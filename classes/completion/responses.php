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
use block_exaaichat\logger;

defined('MOODLE_INTERNAL') || die;

class responses extends completion_base {
    /**
     * Send a request to the OpenAI Responses API to create a new thread or continue an existing one.
     *
     * @param array $data the raw request data to send to the API.
     * @return object (the response)
     */
    private function call_response_api(array $data): object {
        global $USER;

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

        $endpoint = $this->endpoint ?: \block_exaaichat\locallib::get_openai_api_url() . "/responses";

        $data = [
            'model' => $this->model,
            'temperature' => $this->temperature,
            'top_p' => $this->topp,
            'previous_response_id' => $this->thread_id ?: null,

            // needs to be sent every time
            'instructions' => $this->get_instructions() . "\n\n" . $this->get_sourceoftruth(),
            'tools' => $tools,

            ...$data,
        ];

        if (strtolower($data['model']) == 'gpt-5') {
            // Unsupported parameter: 'temperature' is not supported with this model.
            unset($data['temperature']);
        }

        $curl = new \curl();
        $curl->setopt([
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HTTPHEADER' => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apikey,
            ],
        ]);

        if ($ret = $this->curl_pre_check($endpoint)) {
            logger::debug_grouped('chat.user:' . $USER->id, 'curl_pre_check error', $ret);
            return $ret;
        }

        $responseText = $curl->post($endpoint, json_encode($data));

        $this->debug('raw response:', $responseText);

        if ($curl->get_errno()) {
            $this->throw('curl error: ' . $curl->get_errno());
        }

        $response = json_decode($responseText);
        if (!$response) {
            $this->throw('invalid json response from API', $responseText);
        }

        if ($response->error) {
            $this->throw('error while creating thread', $response->error);
        }

        $this->thread_id = $response->id;

        return $response;
    }

    /**
     * Send a message to the AI and get a response.
     *
     * @param string $message The message to send to the AI.
     * @return object An object containing the response ID, thread ID, and the AI's message.
     * @throws \Exception If an error occurs during the API call.
     */
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
            'thread_id' => $this->thread_id,
            'message' => $result_message,
        ];
    }

    /**y
     * Given everything we know after constructing the parent, create a completion by constructing the prompt and making the api call
     * @return JSON: The API response from OpenAI
     */
    public function create_completion(): array {
        $response = $this->message($this->message);

        return [
            "id" => $response->id,
            "message" => helper::clean_text_response($response->message),
            "thread_id" => $response->thread_id,
        ];
    }
}
