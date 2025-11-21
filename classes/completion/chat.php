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

use block_exaaichat\logger;

defined('MOODLE_INTERNAL') || die;

class chat extends \block_exaaichat\completion\completion_base {
    /**
     * Given everything we know after constructing the parent, create a completion by constructing the prompt and making the api call
     * @return array The API response from OpenAI
     */
    public function create_completion(): array {
        $history_json = [];
        $history_json[] = ["role" => "system", "content" => $this->get_instructions() . "\n\n" . $this->get_sourceoftruth()];
        if (trim($this->page_content)) {
            $history_json[] = ["role" => "user", "content" => get_string('page_content_ai_message', 'block_exaaichat') . "\n" . $this->page_content];
            // needed for conova APIs
            // Error: Conversation roles must alternate user/assistant/user/assistant/...
            $history_json[] = ["role" => "assistant", "content" => 'ok'];
        }
        $history_json = array_merge($history_json, $this->format_history());
        $history_json[] = ["role" => "user", "content" => $this->message];

        $response_data = $this->make_api_call($history_json);
        return $response_data;
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

        $curlbody = [
            "model" => $this->model,
            "messages" => $history,
            "temperature" => (float)$this->temperature,
            "max_tokens" => (int)$this->maxlength,
            "top_p" => (float)$this->topp,
            "frequency_penalty" => (float)$this->frequency,
            "presence_penalty" => (float)$this->presence,
            "stop" => $this->username . ":",
        ];

        $curl = new \curl();
        $curl->setopt(array(
            'CURLOPT_HTTPHEADER' => array(
                'Authorization: Bearer ' . $this->apikey,
                'Content-Type: application/json',
            ),
        ));

        $endpoint = $this->endpoint ?: \block_exaaichat\locallib::get_openai_api_url() . "/chat/completions";

        logger::debug_grouped('chat.user:' . $USER->id, $endpoint, $curlbody);

        $response = $curl->post($endpoint, json_encode($curlbody));
        $response = json_decode($response);

        logger::debug_grouped('chat.user:' . $USER->id, 'response', $response);

        if ($response->error ?? false) {
            return [
                "error" => $response->error->message,
            ];
        } else {
            $message = $response->choices[0]->message->content;
        }

        return [
            "id" => property_exists($response, 'id') ? $response->id : 'error',
            "message" => $message,
        ];
    }
}
