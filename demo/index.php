<?php

use block_exaaichat\completion\responses;
use block_exaaichat\logger;

require __DIR__ . '/../../../config.php';

require_login();
// require_admin();

$chat = new responses((object)[
    // 'model' => 'gpt-4.1-mini',
], '');

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
    function user_message($user_message) {
        global $chat;
        $chat->message($user_message);
    }

    user_message('How much is 2 + 4?');
    user_message('what was the last calculation?');
    exit;

    // user_message('Explain the course signature');
    user_message('Who am I?');
    // user_message('Gib mir alle meine Kurse');
    // user_message('was macht die api get_enrolled_users?');
    // user_message('What are my grades in the current course?');
    // user_message('Gib mir alle Teilnehmer vom Kurs mit der id 81');
    // user_message('Gib mir alle Teilnehmer vom Kurs Teacher02');
    // user_message('Gib mir alle Teilnehmer vom Kurs Kurs 1');
    // user_message('wie heiße ich?');

} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
