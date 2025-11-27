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
 * Class providing completions for assistant API
 *
 * @package    block_exaaichat
 * @copyright  2025 GTN Solutions https://gtn-solutions.com
 * @copyright  based on work by Limekiller https://github.com/Limekiller/moodle-block_openai_chat
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_exaaichat\completion;

use block_exaaichat\callback_helper;
use block_exaaichat\helper;
use OpenAI;
use OpenAI\Client;
use OpenAI\Responses\StreamResponse;
use OpenAI\Responses\Threads\Runs\ThreadRunResponseRequiredActionFunctionToolCall;

defined('MOODLE_INTERNAL') || die;

require_once __DIR__ . '/../../vendor/autoload.php';

class assistant extends completion_base {
    private Client $client;
    private StreamResponse $stream;

    protected string $assistant_id;

    public function __construct(object $config, protected string $message, protected string $thread_id = '', protected array $history = []) {
        parent::__construct($config, $message, $thread_id, $history);

        $this->assistant_id = $config->assistant ?? '' ?: $this->get_plugin_setting('assistant', '');
        $this->client = OpenAI::client($this->apikey);
    }

    /**
     * Given everything we know after constructing the parent, create a completion by constructing the prompt and making the api call
     * @return JSON: The API response from OpenAI
     */
    public function create_completion(): array {
        $response = $this->message($this->message);

        return [
            "id" => $response->id,
            "message" => helper::clean_text_response($response->content[0]->text->value),
            "thread_id" => $response->threadId,
        ];
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

        $this->stream = $this->client->threads()->createAndRunStreamed(
            parameters: [
                'assistant_id' => $this->assistant_id,
                'instructions' => $this->get_instructions() . "\n\n" . $this->get_sourceoftruth(),
                'thread' => $message ? [
                    'messages' => [
                        ['role' => 'user', 'content' => $message],
                    ],
                ] : (object)[],
                'tools' => $tools,
            ]
        );

        return $this->get_response();
    }

    /**
     * Send a message to the assistant and get a response.
     *
     * @param string $message The message to send to the assistant.
     * @return \OpenAI\Responses\Threads\Messages\ThreadMessageResponse
     */
    public function message(string $message): \OpenAI\Responses\Threads\Messages\ThreadMessageResponse {
        $this->debug("User: {$message}");

        if (!$this->thread_id) {
            $response = $this->create_thread($message);
        } else {
            /* @var $run OpenAI\Responses\Threads\Runs\ThreadRunResponse */
            $this->client->threads()->messages()->create($this->thread_id, [
                'role' => 'user',
                'content' => $message,
            ]);
            $this->stream = $this->client->threads()->runs()->createStreamed(
                threadId: $this->thread_id,
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
     * Get the response from the stream.
     *
     * @return \OpenAI\Responses\Threads\Messages\ThreadMessageResponse
     */
    private function get_response(): \OpenAI\Responses\Threads\Messages\ThreadMessageResponse {
        // $chatResponse = '';
        $completedResponse = null;

        $completed = false;
        $responseInfo = false;

        while (!$completed) {
            foreach ($this->stream as $responseInfo) {
                // $responseInfo->event // 'thread.run.created' | 'thread.run.in_progress' | .....
                // $responseInfo->response // ThreadResponse | ThreadRunResponse | ThreadRunStepResponse | ThreadRunStepDeltaResponse | ThreadMessageResponse | ThreadMessageDeltaResponse

                if ($responseInfo->event != 'thread.message.delta' && $responseInfo->event != 'thread.run.step.delta') {
                    // $this->debug($responseInfo);
                    $this->debug('event:', $responseInfo->event);
                    // $this->debug($responseInfo->response);
                }

                $response = $responseInfo->response;

                if ($responseInfo->event == 'thread.message.completed') {
                    /* @var $response OpenAI\Responses\Threads\Messages\ThreadMessageResponse */
                    // if ($chatResponse) {
                    //     $chatResponse .= "\n";
                    // }
                    // // var_dump($response);
                    // $chatResponse .= $response->content[0]->text->value;

                    if ($completedResponse) {
                        die('already completed, completed again?!?');
                    }
                    $completedResponse = $response;
                }

                if ($responseInfo->event == 'thread.run.requires_action') {
                    /* @var $response OpenAI\Responses\Threads\Runs\ThreadRunResponse */
                    if ($response->requiredAction) {
                        $tool_outputs = [];

                        foreach ($response->requiredAction->submitToolOutputs->toolCalls as $toolCall) {
                            /* @var $toolCall ThreadRunResponseRequiredActionFunctionToolCall */
                            $function = $toolCall->function;

                            $output = callback_helper::call_tool($function);

                            $tool_outputs[] = [
                                'tool_call_id' => $toolCall->id,
                                'output' => $output,
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
                    $this->thread_id = $response->threadId;

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
     * Send a message to the assistant and return the cleaned response.
     *
     * @param string $message The message to send to the assistant.
     * @return string The cleaned response from the assistant.
     */
    public function message_simple(string $message): string {
        $response = $this->message($message);
        return helper::clean_text_response($response->content[0]->text->value);
    }
}
