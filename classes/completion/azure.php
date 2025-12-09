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
 * Class providing completions for Azure
 *
 * @package    block_exaaichat
 * @copyright  2025 GTN Solutions https://gtn-solutions.com
 * @copyright  based on work by Limekiller https://github.com/Limekiller/moodle-block_openai_chat
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_exaaichat\completion;

use block_exaaichat\logger;

defined('MOODLE_INTERNAL') || die;

class azure extends chat {

    private $resourcename;
    private $deploymentid;
    private $apiversion;

    protected function init(object $config) {
        $this->resourcename = $this->get_plugin_setting('resourcename');
        $this->deploymentid = $this->get_plugin_setting('deploymentid');
        $this->apiversion = $this->get_plugin_setting('apiversion');
    }

    /**
     * Given everything we know after constructing the parent, create a completion by constructing the prompt and making the api call
     * @return JSON: The API response from Azure
     */
    public function create_completion(): array {
        $history_json = array_values([
            ["role" => "system", "content" => $this->get_instructions() . "\n\n" . $this->get_sourceoftruth()],
            ...$this->format_history(),
            ["role" => "user", "content" => $this->message],
        ]);

        $response_data = $this->make_api_call($history_json);
        return $response_data;
    }

    /**
     * Make the actual API call to Azure
     * @return JSON: The response from Azure
     */
    private function make_api_call($history) {
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
                'api-key: ' . $this->apikey,
                'Content-Type: application/json',
            ),
        ));

        $endpoint = "https://" . $this->resourcename . ".openai.azure.com/openai/deployments/" . $this->deploymentid . "/chat/completions?api-version=" . $this->apiversion;

        if ($ret = $this->curl_pre_check($endpoint)) {
            logger::debug_grouped('chat.user:' . $USER->id, 'curl_pre_check error', $ret);
            return $ret;
        }

        $response = $curl->post(
            $endpoint,
            json_encode($curlbody)
        );
        $response = json_decode($response);

        if (property_exists($response, 'error')) {
            $message = 'ERROR: ' . $response->error->message;
        } else {
            $message = $response->choices[0]->message->content;
        }

        return [
            "id" => property_exists($response, 'id') ? $response->id : 'error',
            "message" => $message,
        ];
    }
}
