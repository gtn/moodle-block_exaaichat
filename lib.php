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

use block_exaaichat\locallib;

defined('MOODLE_INTERNAL') || die();

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

    $response = $curl->get(\block_exaaichat\locallib::get_openai_api_url() . "/assistants?order=desc");
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
 * If setting is enabled, log the user's message and the AI response
 * @param string usermessage: The text sent from the user
 * @param string airesponse: The text returned by the AI
 */
function block_exaaichat_log_message($usermessage, $airesponse, $context) {
    global $USER, $DB;

    locallib::clean_log();

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
