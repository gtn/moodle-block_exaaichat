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
 * Class providing completions for chat models (3.5 and up)
 *
 * @package    block_exaaichat
 * @copyright  2025 GTN Solutions https://gtn-solutions.com
 * @copyright  based on work by Limekiller https://github.com/Limekiller/moodle-block_openai_chat
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_exaaichat\completion;

defined('MOODLE_INTERNAL') || die;

class ollama extends chat {
    protected function init(object $config) {
        // adjust endpoint to openai compatible one
        $this->endpoint = preg_replace('!/api/chat/?$!', '/v1/chat/completions', $this->endpoint);

        if (preg_match('!^https?://localhost(:[0-9]+)?/?$!', $this->endpoint)) {
            // localhost ollama with no api path, use openai compatible path
            $this->endpoint = rtrim($this->endpoint, '/') . '/v1/chat/completions';
        }
    }

    public function get_models(): array {
        // liste von https://ollama.com/api/tags
        $models = [
            "cogito-2.1:671b",
            "cogito-2.1:671b",
            "glm-4.6",
            "kimi-k2:1t",
            "kimi-k2-thinking",
            "qwen3-coder:480b",
            "deepseek-v3.1:671b",
            "gpt-oss:120b",
            "gpt-oss:20b",
            "qwen3-vl:235b-instruct",
            "qwen3-vl:235b",
            "minimax-m2",
            "gemini-3-pro-preview",
        ];

        return array_combine($models, $models);
    }

    protected function get_default_endpoint(): string {
        return 'https://ollama.com/v1/chat/completions';
    }
}
