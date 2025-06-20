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
 * @link       https://github.com/Limekiller/moodle-block_openai_chat Based on block openai_chat by Limekiller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_exaaichat;

class logger {
    private static bool $debug_output = false;
    private static ?bool $debug_file_logging = null;

    public static function debugGrouped(string $group, ...$args) {
        global $CFG;

        if (static::$debug_file_logging === null) {
            static::$debug_file_logging = (bool)get_config('block_exaaichat', 'debug_file_logging');
        }

        $args = array_map(function($arg) {
            if ($arg === null) {
                return 'NULL';
            } elseif (is_scalar($arg)) {
                return $arg;
            } else {
                return json_encode($arg, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        }, $args);
        $message = join(' ', $args);

        $message = '[' . date('Y-m-d H:i:s') . ']' .
            ($group ? '[' . $group . ']' : '') .
            ' ' . $message . "\n";

        if (static::$debug_output) {
            echo $message;
        }

        if (static::$debug_file_logging) {
            $dir = $CFG->dataroot . '/log';
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $logfile = $dir . '/exaaichat.log';
            file_put_contents($logfile, $message, FILE_APPEND);
        }
    }

    public static function debug(...$args) {
        static::debugGrouped('', $args);
    }

    public static function enable_debug_output() {
        static::$debug_output = true;
    }
}
