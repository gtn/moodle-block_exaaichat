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
use block_exaaichat\locallib;
use block_exaaichat\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Block class
 *
 * @package    block_exaaichat
 * @copyright  2025 GTN Solutions https://gtn-solutions.com
 * @copyright  based on work by Limekiller https://github.com/Limekiller/moodle-block_openai_chat
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_exaaichat extends block_base {
    public function init() {
        $this->title = get_string('exaaichat', 'block_exaaichat');
    }

    public function has_config() {
        return true;
    }

    public function applicable_formats() {
        return [
            'all' => true,
            'my' => (bool)get_config('block_exaaichat', 'allow_on_dashboard'),
        ];
    }

    public function specialization() {
        if (!empty($this->config->title)) {
            $this->title = $this->config->title;
        }
    }

    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = output::render_chat_interface($this->instance->id, $this->config ?? (object)[], true, false);

        return $this->content;
    }
}
