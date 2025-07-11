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
 * @package    block_exaaichat
 * @copyright  2025 GTN Solutions https://gtn-solutions.com
 * @copyright  based on work by Limekiller https://github.com/Limekiller/moodle-block_openai_chat
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require __DIR__ . '/../../config.php';

require_admin();

$lines = optional_param('lines', 2000, PARAM_INT);
$search_thread = optional_param('thread', '', PARAM_TEXT);

$logfile = $CFG->dataroot . '/log/block_exaaichat.log';

echo '<pre>';

if ($search_thread) {
    $file = new SplFileObject($logfile, 'r');
    while (!$file->eof()) {
        $line = $file->fgets();

        if (preg_match('!^(\[[^\]]+\]\[)(thread_[^\]]+|new)!', $line, $matches)) {
            $thread = $matches[2];
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
