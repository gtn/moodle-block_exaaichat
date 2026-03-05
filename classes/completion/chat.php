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
 * Class providing completions for chat models (3.5 and up)
 *
 * @package    block_exaaichat
 * @copyright  2025 GTN Solutions https://gtn-solutions.com
 * @copyright  based on work by Limekiller https://github.com/Limekiller/moodle-block_openai_chat
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_exaaichat\completion;

use block_exaaichat\callback_helper;
use block_exaaichat\logger;

defined('MOODLE_INTERNAL') || die;

class chat extends completion_base {
    /**
     * Given everything we know after constructing the parent, create a completion by constructing the prompt and making the api call
     * @return array The API response from OpenAI
     */
    public function create_completion(): array {
        $history = [];
        $history[] = ["role" => "system", "content" => $this->get_instructions() . "\n\n" . $this->get_sourceoftruth()];
        if (trim($this->page_content)) {
            $history[] = ["role" => "user", "content" => get_string('page_content_ai_message', 'block_exaaichat') . "\n" . $this->page_content];
            // needed for conova APIs
            // Error: Conversation roles must alternate user/assistant/user/assistant/...
            $history[] = ["role" => "assistant", "content" => 'ok'];
        }
        $history = array_merge($history, $this->format_history());
        $history[] = ["role" => "user", "content" => $this->message];

        // Tool calling loop: keep calling until we get a final text response.
        while (true) {
            $response_data = $this->make_api_call($history);

            if (isset($response_data['error'])) {
                return $response_data;
            }

            if (!isset($response_data['tool_calls'])) {
                return $response_data;
            }

            // Append the assistant message with tool calls to history.
            $history[] = $response_data['assistant_message'];

            // Process each tool call and append results.
            foreach ($response_data['tool_calls'] as $tool_call) {
                $result = callback_helper::call_tool((object)[
                    'name' => $tool_call->function->name,
                    'arguments' => $tool_call->function->arguments,
                ]);

                $history[] = [
                    'role' => 'tool',
                    'tool_call_id' => $tool_call->id,
                    'content' => $result,
                ];
            }
        }
    }

    /**
     * Format the history JSON into a string that we can pass in the prompt
     * @return string: The string representing the chat history to add to the prompt
     */
    protected function format_history(): array {
        $history = [];
        foreach ($this->history as $message) {
            if ($message['type'] == 'error') {
                // don't send errors in chat history to ai
                continue;
            }

            // TODO: maybe check that type is only user and asisstant
            // type system is not allowed here?
            $history[] = ["role" => $message['type'], "content" => $message["message"]];
        }

        return $history;
    }

    /**
     * Make the actual API call to OpenAI
     * @return array The response from OpenAI
     */
    private function make_api_call($history): array {
        global $USER;

        $endpoint = $this->endpoint ?: $this->get_default_endpoint();
        $model = $this->model;

        if (!$model && preg_match('!^https://generativelanguage.googleapis.com/v1beta/models/([^/:]+)!', $endpoint, $matches)) {
            $model = $matches[1];
        }

        if (str_starts_with($endpoint, 'https://generativelanguage.googleapis.com/v1beta/')) {
            // Google Gemini Endpoint
            $endpoint = 'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions';
        }

        $data = [
            "model" => $model,
            "messages" => $history,
            "temperature" => (float)$this->temperature,
            "max_tokens" => (int)$this->maxlength,
            "top_p" => (float)$this->topp,
            "frequency_penalty" => (float)$this->frequency,
            "presence_penalty" => (float)$this->presence,
            "stop" => $this->username . ":",
        ];

        if (static::model_supports_tool_calling($model)) {
            $data['tools'] = array_values(array_map(function($function_definition) {
                unset($function_definition['callback']);

                return [
                    'type' => 'function',
                    'function' => $function_definition,
                ];
            }, callback_helper::get_functions()));
        }

        if (preg_match('!^gpt-[5-9]!i', $model)) {
            // gpt-5+ uses max_completion_tokens instead of max_tokens
            $data['max_completion_tokens'] = (int)$this->maxlength;
            unset($data['max_tokens']);
            unset($data['stop']);
            unset($data['temperature']);
            unset($data['frequency_penalty']);
            unset($data['presence_penalty']);
        }

        if (str_starts_with($endpoint, 'https://generativelanguage.googleapis.com/v1beta/openai/')) {
            // not supported by gemini
            unset($data['frequency_penalty']);
            unset($data['presence_penalty']);
        }

        $curl = new \curl();
        $curl->setopt(array(
            'CURLOPT_HTTPHEADER' => array(
                'Authorization: Bearer ' . $this->apikey,
                'Content-Type: application/json',
            ),
        ));

        logger::debug_grouped('chat.user:' . $USER->id, $endpoint, $data);

        if ($ret = $this->curl_pre_check($endpoint)) {
            logger::debug_grouped('chat.user:' . $USER->id, 'curl_pre_check error', $ret);
            return $ret;
        }

        $rawResponse = $curl->post($endpoint, json_encode($data));

        // another solution: check $rawResponse after the request, which maybe contains the error message
        // if ($rawResponse == $curl->get_security()->get_blocked_url_string()) {
        //     // url was blocked error
        // }

        $response = json_decode($rawResponse);

        if (!$response) {
            logger::debug_grouped('chat.user:' . $USER->id, 'response error', [
                'curl_error' => $curl->error,
                'curl_errno' => $curl->get_errno(),
                'curl_info' => $curl->get_info(),
            ]);
            logger::debug_grouped('chat.user:' . $USER->id, 'rawResponse:', $rawResponse);

            return [
                'id' => 'error',
                "error" => strip_tags($rawResponse),
            ];
        }

        logger::debug_grouped('chat.user:' . $USER->id, 'response', $response);

        if (is_object($response)) {
            if ($response->error ?? false) {
                return [
                    "error" => is_string($response->error) ? /* ollama */ $response->error : $response->error->message,
                ];
            }

            $choice = $response->choices[0];

            // Handle tool calls.
            if ($choice->message->tool_calls ?? null) {
                return [
                    "id" => property_exists($response, 'id') ? $response->id : 'error',
                    "tool_calls" => $choice->message->tool_calls,
                    "assistant_message" => json_decode(json_encode($choice->message), true),
                ];
            }

            return [
                "id" => property_exists($response, 'id') ? $response->id : 'error',
                "message" => $choice->message->content,
            ];
        }

        return [
            "id" => 'error',
            "message" =>
            // gemini error format (array with one error object)
                $response[0]->error->message ?? 'Unknown error',
        ];
    }

    protected function get_default_endpoint(): string {
        return \block_exaaichat\locallib::get_openai_api_url() . "/chat/completions";
    }
}
