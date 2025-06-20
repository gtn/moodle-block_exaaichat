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
 * @link       https://github.com/Limekiller/moodle-block_openai_chat Based on block openai_chat by Limekiller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_exaaichat\api;

use block_exaaichat\callback_helper;

defined('MOODLE_INTERNAL') || die;

class responses extends base {
    function __construct(
        protected string $threadId = '',
        private string $apikey = '',
        private string $assistant_id = '',
        private $instructions = '',
        private string $model = 'gpt-4o-mini',
        private ?float $temperature = null,
        private ?float $top_p = null,
        private array $vector_store_ids = [],
    ) {
        if (!$apikey) {
            $this->apikey = get_config('block_exaaichat', 'apikey');
        }

        parent::__construct($threadId);
    }

    private function call_response_api(array $data) {
        $tools = array_values(array_map(function($function_definition) {
            unset($function_definition['callback']);

            return [
                'type' => 'function',
                ...$function_definition,
            ];
        }, callback_helper::get_functions()));

        if ($this->vector_store_ids) {
            $tools = array_merge($tools, [
                [
                    "type" => "file_search",
                    "vector_store_ids" => $this->vector_store_ids,
                    "max_num_results" => 20, // TODO: was macht das?
                ],
            ]);
        }

        $url = 'https://api.openai.com/v1/responses'; // Note: This is not a valid OpenAI endpoint. Likely you meant /v1/chat/completions or /v1/completions.

        $data = [
            'model' => $this->model,
            'temperature' => $this->temperature,
            'top_p' => $this->top_p,
            'previous_response_id' => $this->threadId ?: null,

            // needs to be sent every time
            'instructions' => $this->instructions,
            'tools' => $tools,

            ...$data,
        ];

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apikey,
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        $this->debug('raw response:', $response);

        if (curl_errno($ch)) {
            throw new \moodle_exception('curl error: ' . curl_error($ch));
        }

        curl_close($ch);

        $response = json_decode($response);

        if ($response->error) {
            $this->throw('error while creating thread', $response->error);
        }

        $this->threadId = $response->id;

        return $response;
    }

    public function message(string $message) {
        try {
            if ($additional_message = trim(get_config('block_exaaichat', 'additional_message'))) {
                $message .= "\n" . $additional_message;
            }

            $response = $this->call_response_api([
                'input' => $message,
            ]);
        } catch (\Exception $e) {
            $this->debug("User: {$message}");
            throw $e;
        }

        $this->debug("User: {$message}");

        $result_message = '';
        while (true) {
            $output_function_call = null;
            $output_message = null;
            foreach ($response->output as $output) {
                if ($output->type == 'function_call') {
                    if ($output_function_call) {
                        $this->debug('function call already set');
                    }
                    $output_function_call = $output;
                } elseif ($output->type == 'message') {
                    if ($output_message) {
                        $this->debug('message already set');
                    }
                    $output_message = $output;
                } elseif ($output->type == 'file_search_call') {
                    // info about a file search
                } else {
                    $this->debug('wrong output type', $output);
                }
            }

            if ($output_function_call) {
                // call again

                // https://platform.openai.com/docs/guides/function-calling?api-mode=responses
                // input_messages.append(tool_call)  # append model's function call message
                // input_messages.append({                               # append result message
                //     "type": "function_call_output",
                //     "call_id": tool_call.call_id,
                //     "output": str(result)
                // })
                // var_dump($output);

                $response = $this->call_response_api([
                    'input' => [
                        [
                            "type" => "function_call_output",
                            "call_id" => $output_function_call->call_id,
                            "output" => callback_helper::call_tool($output_function_call),
                        ],
                    ],
                ]);

                // process next message
                continue;
            }

            if (!$output_message) {
                $this->throw('didn\'t find a message output:', $response);
            }

            if ($output_message->status != 'completed') {
                $this->throw('output message not completed?!?:', $output_message);
            }

            $result_message = $output_message->content[0]->text;

            // got a final response
            break;
        }


        $this->debug("AI:\n====================================================================\n" .
            $result_message .
            "\n====================================================================\n");


        return (object)[
            'id' => $response->id,
            'threadId' => $this->threadId,
            'message' => $result_message,
        ];
    }

    // public function messageSimple(string $message): string {
    //     $response = $this->message($message);
    //     return helper::clean_text_response($response->content[0]->text->value);
    // }
}
