<?php

use block_exaaichat\api\responses;
use block_exaaichat\logger;

require __DIR__ . '/../../../config.php';

require_login();
// require_admin();

$api_key = '';
$chat = new responses('', $api_key);

/*
echo json_encode(array_map(function($function_definition) {
    unset($function_definition['callback']);
    return $function_definition;
}, callback_helper::get_functions()), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
/* */

$courseid = required_param('courseid', PARAM_INT);
require_login($courseid);
$PAGE->set_context(\context_course::instance($courseid));

echo '<pre>';
logger::enable_debug_output();

try {
    function userMessage($userMessage) {
        global $chat;
        $chat->message($userMessage);
    }

    userMessage('How much is 2 + 4?');
    userMessage('what was the last calculation?');
    exit;

    // userMessage('Explain the course signature');
    userMessage('Who am I?');
    // userMessage('Gib mir alle meine Kurse');
    // userMessage('was macht die api get_enrolled_users?');
    // userMessage('What are my grades in the current course?');
    // userMessage('Gib mir alle Teilnehmer vom Kurs mit der id 81');
    // userMessage('Gib mir alle Teilnehmer vom Kurs Teacher02');
    // userMessage('Gib mir alle Teilnehmer vom Kurs Kurs 1');
    // userMessage('wie heiße ich?');

} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
