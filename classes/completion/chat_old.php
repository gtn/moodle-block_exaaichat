<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    block_exaaichat
 * @copyright  2025 GTN Solutions https://gtn-solutions.com
 * @copyright  based on work by Limekiller https://github.com/Limekiller/moodle-block_openai_chat
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_exaaichat\completion;

use block_exaaichat\api\base;
use block_exaaichat\callback_helper;
use block_exaaichat\helper;
use OpenAI;
use OpenAI\Client;
use OpenAI\Responses\StreamResponse;
use OpenAI\Responses\Threads\Runs\ThreadRunResponseRequiredActionFunctionToolCall;

defined('MOODLE_INTERNAL') || die;

/**
 * OpenAI chat API
 */
class chat_old extends base {
    private Client $client;
    private StreamResponse $stream;

    function __construct(protected string $threadId = '', private string $apikey = '', private string $assistant_id = '', private $instructions = '') {
        if (!$apikey) {
            $apikey = get_config('block_exaaichat', 'apikey');
        }

        $this->assistant_id = $this->assistant_id ?: get_config('block_exaaichat', 'assistant');

        $this->client = OpenAI::client($apikey);

        parent::__construct($threadId);
    }

    /**
     * Create a new thread with the given message
     *
     * @param string $message The initial message to start the thread with.
     * @return \OpenAI\Responses\Threads\Messages\ThreadMessageResponse
     */
    public function create_thread(string $message = '') {
        $tools = array_values(array_map(function($function_definition) {
            unset($function_definition['callback']);

            return [
                'type' => 'function',
                'function' => $function_definition,
            ];
        }, callback_helper::get_functions()));

        $this->stream = $this->client->chat()->createStreamed(
            parameters: [
                // 'assistant_id' => $this->assistant_id,
                'model' => 'gpt-4o-mini',
                // 'instructions' => $this->instructions,
                'messages' => [
                    ['role' => 'user', 'content' => $message],
                ],
                // 'prompt' => $message,
                'tools' => $tools,
            ]
        );

        return $this->get_response();
    }

    /**
     * Send a message to the chat thread
     *
     * @param string $message The message to send.
     * @return \OpenAI\Responses\Threads\Messages\ThreadMessageResponse
     */
    public function message(string $message): \OpenAI\Responses\Threads\Messages\ThreadMessageResponse {
        $this->debug("User: {$message}");

        if (!$this->threadId) {
            $response = $this->create_thread($message);
        } else {
            /* @var $run OpenAI\Responses\Threads\Runs\ThreadRunResponse */
            $this->client->threads()->messages()->create($this->threadId, [
                'role' => 'user',
                'content' => $message,
            ]);
            $this->stream = $this->client->threads()->runs()->createStreamed(
                threadId: $this->threadId,
                parameters: [
                    'assistant_id' => $this->assistant_id,
                ],
            );

            $response = $this->get_response();
        }

        $this->debug("AI:\n====================================================================\n" .
            $response->content[0]->text->value .
            "\n====================================================================\n");

        return $response;
    }

    /**
     * Get the response from the stream
     * @return OpenAI\Responses\Threads\Messages\ThreadMessageResponse
     * @throws \Exception
     */
    private function get_response(): \OpenAI\Responses\Threads\Messages\ThreadMessageResponse {
        // $chatResponse = '';
        $completedResponse = null;

        $completed = false;
        $responseInfo = false;

        while (!$completed) {
            foreach ($this->stream as $responseInfo) {
                $eventType = $responseInfo->event ?? $responseInfo->object;

                // $eventType // 'thread.run.created' | 'thread.run.in_progress' | .....
                // $responseInfo->response // ThreadResponse | ThreadRunResponse | ThreadRunStepResponse | ThreadRunStepDeltaResponse | ThreadMessageResponse | ThreadMessageDeltaResponse

                if ($responseInfo->event != 'thread.message.delta' && $responseInfo->event != 'thread.run.step.delta') {
                    $this->debug('eventType:', $eventType);
                    $this->debug($responseInfo);
                }

                $response = $responseInfo->response;

                if ($eventType == 'thread.message.completed') {
                    /* @var $response OpenAI\Responses\Threads\Messages\ThreadMessageResponse */
                    // if ($chatResponse) {
                    //     $chatResponse .= "\n";
                    // }
                    // // var_dump($response);
                    // $chatResponse .= $response->content[0]->text->value;

                    if ($completedResponse) {
                        $this->debug('already completed, completed again?!?');
                        // die('already completed, completed again?!?');
                    }
                    $completedResponse = $response;
                }

                if ($eventType == 'thread.run.requires_action') {
                    /* @var $response OpenAI\Responses\Threads\Runs\ThreadRunResponse */
                    if ($response->requiredAction) {
                        $tool_outputs = [];

                        foreach ($response->requiredAction->submitToolOutputs->toolCalls as $toolCall) {
                            /* @var $toolCall ThreadRunResponseRequiredActionFunctionToolCall */
                            $function = $toolCall->function;
                            $this->debug('api calling function: ' . $function->name);

                            $functions = callback_helper::get_functions();
                            $functionDefinition = current(array_filter($functions, fn($f) => $f['name'] == $function->name));
                            if ($functionDefinition) {
                                $arguments = $function->arguments ? json_decode($function->arguments) : [];
                                if (is_object($arguments)) {
                                    $arguments = (array)$arguments;
                                }

                                $this->debug('arguments:', $arguments);

                                $finalArguments = [];
                                foreach ($functionDefinition['parameters']['properties'] as $paramName => $paramDetails) {
                                    if (isset($arguments[$paramName])) {
                                        $finalArguments[] = $arguments[$paramName];
                                    } elseif (in_array($paramName, $functionDefinition['parameters']['required'])) {
                                        throw new \Exception("Missing required parameter: $paramName");
                                    }
                                }

                                $callback = $functionDefinition['callback'];

                                try {
                                    if (is_array($callback)) {
                                        $output = $callback[0]::{$callback[1]}(...$finalArguments);
                                    } else {
                                        $output = $callback(...$finalArguments);
                                    }
                                } catch (\Exception $e) {
                                    $output = 'Fehler: ' . $e->getMessage();
                                }
                            } else {
                                $output = 'This API is not available';
                            }
                            $this->debug("output:", $output);

                            $tool_outputs[] = [
                                'tool_call_id' => $toolCall->id,
                                'output' => is_scalar($output) ? $output : json_encode($output),
                            ];
                        }

                        $this->stream = $this->client->threads()->runs()->submitToolOutputsStreamed(
                            threadId: $response->threadId,
                            runId: $response->id,
                            parameters: [
                                'tool_outputs' => $tool_outputs,
                            ]
                        );
                    }
                }

                if ($response instanceof OpenAI\Responses\Threads\Runs\ThreadRunResponse) {
                    $this->threadId = $response->threadId;

                    $completed = ($response->status == 'completed'
                        || $response->failedAt
                        // TODO: gibts die property wirklich (code von chatGPT übernommen)
                        || property_exists($response, 'error'));
                }
            }
        }

        if (!$completedResponse) {
            $this->debug('response was not completed, last response:', $responseInfo);
        }

        return $completedResponse;
        // return $chatResponse;
    }

    /**
     * Send a message to the chat and return the cleaned response.
     *
     * @param string $message The message to send to the assistant.
     * @return string The cleaned response from the assistant.
     */
    public function message_simple(string $message): string {
        $response = $this->message($message);
        return helper::clean_text_response($response->content[0]->text->value);
    }
}
