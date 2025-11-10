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
 * Class providing completions for assistant API
 *
 * @package    block_exaaichat
 * @copyright  2025 GTN Solutions https://gtn-solutions.com
 * @copyright  based on work by Limekiller https://github.com/Limekiller/moodle-block_openai_chat
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_exaaichat\completion;

use block_exaaichat\helper;

defined('MOODLE_INTERNAL') || die;

class responses extends \block_exaaichat\completion {

    private $thread_id;
    private \block_exaaichat\api\responses $chat;

    public function __construct($model, $message, $history, $block_settings, $thread_id) {
        parent::__construct($model, $message, $history, $block_settings);

        $vector_store_ids = trim($block_settings['vector_store_ids'] ?? '');
        if ($vector_store_ids) {
            $vector_store_ids = preg_split('![\s,]+!', $vector_store_ids);
        } else {
            $vector_store_ids = [];
        }

        $this->chat = new \block_exaaichat\api\responses($thread_id, $this->apikey, $this->assistant ?: '', $this->instructions . "\n\n" . $this->get_sourceoftruth(),
            model: $this->model, temperature: $this->temperature, top_p: $this->topp,
            vector_store_ids: $vector_store_ids);

        $this->thread_id = $thread_id;
    }

    /**
     * Given everything we know after constructing the parent, create a completion by constructing the prompt and making the api call
     * @return JSON: The API response from OpenAI
     */
    public function create_completion($context) {
        $response = $this->chat->message($this->message);

        return [
            "id" => $response->id,
            "message" => helper::clean_text_response($response->message),
            "thread_id" => $response->threadId,
        ];
    }
}
