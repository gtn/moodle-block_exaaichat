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
 * API endpoint for retrieving GPT completion
 *
 * @package    block_exaaichat
 * @copyright  2025 GTN Solutions https://gtn-solutions.com
 * @copyright  based on work by Limekiller https://github.com/Limekiller/moodle-block_openai_chat
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_exaaichat\locallib;

require_once('../../../config.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/blocks/exaaichat/lib.php');

global $DB, $PAGE;

if (get_config('block_exaaichat', 'restrictusage') !== "0") {
    require_login();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $CFG->wwwroot");
    die();
}

require_sesskey();

$body = json_decode(file_get_contents('php://input'), true);
$message = clean_param($body['message'], PARAM_NOTAGS);
$history = clean_param_array($body['history'], PARAM_NOTAGS, true);
$block_id = clean_param($body['blockId'], PARAM_TEXT); // block_id can be string or int
$thread_id = clean_param($body['threadId'], PARAM_NOTAGS);
$provider_id = clean_param($body['providerId'] ?? null, PARAM_NOTAGS);
$page_content = clean_param($body['pageContent'] ?? null, PARAM_NOTAGS);

if (preg_match('!^course-(.*)$!', $block_id, $matches)) {
    $courseid = $matches[1];

    $config = (object)[];
    $context = context_course::instance($courseid);
} else {
    // So that we're not leaking info to the client like API key, the block makes an API request including its ID
    // Then we can look up that specific block to pull out its config data
    $instance_record = $DB->get_record('block_instances', ['blockname' => 'exaaichat', 'id' => $block_id], '*');
    $instance = block_instance('exaaichat', $instance_record);
    if (!$instance) {
        throw new \moodle_exception('invalidblockinstance', 'error');
    }

    $config = clone $instance->config;
    $context = context::instance_by_id($instance_record->parentcontextid);
}

// $context could be a course context, could also be any subcontext, like a module
require_login($context->get_course_context()->instanceid);

$PAGE->set_context($context);

if (get_config('block_exaaichat', 'allowproviderselection') && $provider_id) {
    $providers = locallib::get_moodle_ai_providers();
    $provider = current(array_filter($providers, fn($provider) => $provider->id == $provider_id));

    if (!$provider) {
        throw new \moodle_exception('invalidprovider', 'block_exaaichat');
    }

    $extra_config = (object)[];
    $extra_config->api_type = $provider->api_type;
    $extra_config->apikey = $provider->apikey;
    $extra_config->model = $provider->model;
    $extra_config->endpoint = $provider->endpoint;
    // TODO: modelsettings

    $config = (object)array_merge((array)$config, (array)$extra_config);
    $config->is_moodle_ai_provider = true;
    $config->settings_to_keep = array_keys((array)$extra_config);
}

$completion = \block_exaaichat\completion\completion_base::create_from_config($config, $message, $thread_id, $history, $page_content);

try {
    $response = $completion->create_completion();
    if ($response['error'] ?? false) {
        header("Content-Type: application/json");
        echo json_encode($response);
        exit;
    }
} catch (\moodle_exception $e) {
    header("Content-Type: application/json");
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

// Format the markdown of each completion message into HTML.
$response["message"] = format_text($response["message"], FORMAT_MARKDOWN, ['context' => $context]);

// Log the message
block_exaaichat_log_message($message, $response['message'], $context);

$response = json_encode($response);

header("Content-Type: application/json");
echo $response;
