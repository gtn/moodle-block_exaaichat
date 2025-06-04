<?php

require __DIR__ . '/../../config.php';

require_admin();

$lines = optional_param('lines', 2000, PARAM_INT);
$search_thread = optional_param('thread', '', PARAM_TEXT);

$logfile = $CFG->dataroot . '/log/exaaichat.log';

echo '<pre>';

if ($search_thread) {
    $file = new SplFileObject($logfile, 'r');
    while (!$file->eof()) {
        $line = $file->fgets();

        if (preg_match('!^(\[[^\]]+\]\[)(thread_[^\]]+|new)!', $line, $matches)) {
            $thread = $matches[2];
            // } elseif ($line[0] == '[') {
            //     // something else
            //     continue;
        }

        if ($thread != $search_thread) {
            continue;
        }

        $line = htmlspecialchars($line, ENT_QUOTES | ENT_HTML5);

        echo $line;
    }
} else {
    $file = new SplFileObject($logfile, 'r');
    $file->seek(PHP_INT_MAX); // Go to the end of the file
    $lastLine = $file->key(); // Get the total number of lines

    $startLine = max(0, $lastLine - $lines); // Calculate the start line
    $file->seek($startLine); // Move to the start line

    $output = [];
    $thread = '';
    while (!$file->eof()) {
        $line = $file->fgets();

        $line = htmlspecialchars($line, ENT_QUOTES | ENT_HTML5);

        $line = preg_replace('!^(\[[^\]]+\]\[)(thread_[^\]]+)!', '$1<a href="log.php?thread=$2">$2</a>', $line);

        echo $line;
    }
}
