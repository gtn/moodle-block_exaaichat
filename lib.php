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
 * General plugin functions
 *
 * @package    block_exaaichat
 * @copyright  2025 GTN Solutions https://gtn-solutions.com
 * @copyright  based on work by Limekiller https://github.com/Limekiller/moodle-block_openai_chat
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Fetch the current API type from the database, defaulting to "chat"
 * @return String: the API type (chat|azure|assistant)
 */
function block_exaaichat_get_type_to_display() {
    $stored_type = get_config('block_exaaichat', 'type');
    if ($stored_type) {
        return $stored_type;
    }

    return 'chat';
}

/**
 * Use an API key to fetch a list of assistants from a user's OpenAI account
 * @param int|null $block_id The ID of a block instance. If this is passed, the API can be pulled from the block rather than the site level.
 * @return array The list of assistants
 * @throws coding_exception
 * @throws dml_exception
 */
function block_exaaichat_fetch_assistants_array(int $block_id = null) {
    global $DB;

    if (!$block_id) {
        $apikey = get_config('block_exaaichat', 'apikey');
    } else {
        $instance_record = $DB->get_record('block_instances', ['blockname' => 'exaaichat', 'id' => $block_id], '*');
        $instance = block_instance('exaaichat', $instance_record);
        $apikey = $instance->config->apikey ? $instance->config->apikey : get_config('block_exaaichat', 'apikey');
    }

    if (!$apikey) {
        return [];
    }

    $curl = new \curl();
    $curl->setopt(array(
        'CURLOPT_HTTPHEADER' => array(
            'Authorization: Bearer ' . $apikey,
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v2'
        ),
    ));

    $response = $curl->get("https://api.openai.com/v1/assistants?order=desc");
    $response = json_decode($response);
    $assistant_array = [];
    if ($response && property_exists($response, 'data')) {
        foreach ($response->data as $assistant) {
            $assistant_array[$assistant->id] = $assistant->name;
        }
    }

    return $assistant_array;
}

/**
 * Return a list of available models, and the type of each model.
 * (Type used to be relevant before OpenAI released the Assistant API. Currently it is no longer useful as all models are of type "chat,"
 * but I left it here in case the API is changed significantly in the future)
 * @return Array: The list of model info
 */
function block_exaaichat_get_models() {
    return [
        "models" => [
            'gpt-4.1-mini' => 'gpt-4.1-mini',
            'gpt-4o' => 'gpt-4o',
            'gpt-4o-2024-11-20' => 'gpt-4o-2024-11-20',
            'gpt-4o-2024-08-06' => 'gpt-4o-2024-08-06',
            'gpt-4o-2024-05-13' => 'gpt-4o-2024-05-13',
            'gpt-4o-mini-2024-07-18' => 'gpt-4o-mini-2024-07-18',
            'gpt-4o-mini' => 'gpt-4o-mini',
            'gpt-4-turbo-preview' => 'gpt-4-turbo-preview',
            'gpt-4-turbo-2024-04-09' => 'gpt-4-turbo-2024-04-09',
            'gpt-4-turbo' => 'gpt-4-turbo',
            'gpt-4-32k-0314' => 'gpt-4-32k-0314',
            'gpt-4-1106-preview' => 'gpt-4-1106-preview',
            'gpt-4-0613' => 'gpt-4-0613',
            'gpt-4-0314' => 'gpt-4-0314',
            'gpt-4-0125-preview' => 'gpt-4-0125-preview',
            'gpt-4' => 'gpt-4',
            'gpt-3.5-turbo-16k-0613' => 'gpt-3.5-turbo-16k-0613',
            'gpt-3.5-turbo-16k' => 'gpt-3.5-turbo-16k',
            'gpt-3.5-turbo-1106' => 'gpt-3.5-turbo-1106',
            'gpt-3.5-turbo-0125' => 'gpt-3.5-turbo-0125',
            'gpt-3.5-turbo' => 'gpt-3.5-turbo'
        ],
        "types" => [
            'gpt-4o-2024-11-20'          =>  'chat',
            'gpt-4o-2024-08-06'          =>  'chat',
            'gpt-4o-2024-05-13'          =>  'chat',
            'gpt-4o'                     =>  'chat',
            'gpt-4o-mini-2024-07-18'     =>  'chat',
            'gpt-4o-mini'                =>  'chat',
            'gpt-4-turbo-preview'        =>  'chat',
            'gpt-4-turbo-2024-04-09'     =>  'chat',
            'gpt-4-turbo'                =>  'chat',
            'gpt-4-32k-0314'             =>  'chat',
            'gpt-4-1106-preview'         =>  'chat',
            'gpt-4-0613'                 =>  'chat',
            'gpt-4-0314'                 =>  'chat',
            'gpt-4-0125-preview'         =>  'chat',
            'gpt-4'                      =>  'chat',
            'gpt-3.5-turbo-16k-0613'     =>  'chat',
            'gpt-3.5-turbo-16k'          =>  'chat',
            'gpt-3.5-turbo-1106'         =>  'chat',
            'gpt-3.5-turbo-0125'         =>  'chat',
            'gpt-3.5-turbo'              =>  'chat'
        ]
    ];
}

/**
 * If setting is enabled, log the user's message and the AI response
 * @param string usermessage: The text sent from the user
 * @param string airesponse: The text returned by the AI
 */
function block_exaaichat_log_message($usermessage, $airesponse, $context) {
    global $USER, $DB;

    if (!get_config('block_exaaichat', 'logging')) {
        return;
    }

    $DB->insert_record('block_exaaichat_log', (object) [
        'userid' => $USER->id,
        'usermessage' => $usermessage,
        'airesponse' => $airesponse,
        'contextid' => $context->id,
        'timecreated' => time()
    ]);
}

function block_exaaichat_extend_navigation_course($nav, $course, $context) {
    if ($nav->get('coursereports')) {
        $nav->get('coursereports')->add(
            get_string('exaaichat_logs', 'block_exaaichat'),
            new moodle_url('/blocks/exaaichat/report.php', ['courseid' => $course->id]),
            navigation_node::TYPE_SETTING,
            null
        );
    }
}
