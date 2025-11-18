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
$block_id = clean_param($body['blockId'], PARAM_INT, true);
$thread_id = clean_param($body['threadId'], PARAM_NOTAGS, true);


// So that we're not leaking info to the client like API key, the block makes an API request including its ID
// Then we can look up that specific block to pull out its config data
$instance_record = $DB->get_record('block_instances', ['blockname' => 'exaaichat', 'id' => $block_id], '*');
$instance = block_instance('exaaichat', $instance_record);
if (!$instance) {
    throw new \moodle_exception('invalidblockinstance', 'error');
}

$context = context::instance_by_id($instance_record->parentcontextid);

if ($context instanceof \context_course) {
    require_login($context->instanceid);
} else {
    throw new \moodle_exception('block is not a course block, TODO: pass courseid as parameter');
}

$PAGE->set_context($context);

$completion = \block_exaaichat\completion\completion_base::create_from_config($instance->config, $message, $thread_id, $history);
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
