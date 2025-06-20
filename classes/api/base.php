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

namespace block_exaaichat\api;

use block_exaaichat\logger;

defined('MOODLE_INTERNAL') || die;

require_once __DIR__ . '/../../vendor/autoload.php';

class base {
    function __construct(protected string $threadId = '') {

    }

    protected function debug(...$args) {
        logger::debug_grouped($this->threadId ?: 'new', ...$args);
    }

    protected function throw(...$args) {
        $this->debug($args);
        throw new \moodle_exception(json_encode($args));
    }
}
