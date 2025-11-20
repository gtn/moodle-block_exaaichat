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

    function applicable_formats() {
        return array('all' => true);
    }

    public function specialization() {
        if (!empty($this->config->title)) {
            $this->title = $this->config->title;
        }
    }

    public function get_content(bool $as_aiplacement_content = false) {
        global $COURSE, $OUTPUT, $PAGE;

        if ($this->content !== null) {
            return $this->content;
        }

        // Send data to front end
        $persistconvo = get_config('block_exaaichat', 'persistconvo');
        if (!empty($this->config)) {
            $persistconvo = (property_exists($this->config, 'persistconvo') && get_config('block_exaaichat', 'allowinstancesettings')) ? $this->config->persistconvo : $persistconvo;
        }

        $api_type = '';
        if (get_config('block_exaaichat', 'allowinstancesettings')) {
            // allow switching to different api
            $api_type = $this->config->api_type;
        }
        if (!$api_type) {
            $api_type = \block_exaaichat\locallib::get_api_type();
        }

        // First, fetch the global settings for these (and the defaults if not set)
        $assistantname = get_config('block_exaaichat', 'assistantname') ?: get_string('defaultassistantname', 'block_exaaichat');
        $username = get_config('block_exaaichat', 'username') ?: get_string('defaultusername', 'block_exaaichat');

        // Then, override with local settings if available
        if (!empty($this->config)) {
            $assistantname = (property_exists($this->config, 'assistantname') && $this->config->assistantname) ? $this->config->assistantname : $assistantname;
            $username = (property_exists($this->config, 'username') && $this->config->username) ? $this->config->username : $username;
        }
        $assistantname = format_string($assistantname, true, ['context' => $this->context]);
        $username = format_string($username, true, ['context' => $this->context]);

        $this->page->requires->js_call_amd('block_exaaichat/lib', 'init', [[
            'blockId' => (int)$this->instance->id,
            'api_type' => $api_type,
            'persistConvo' => (bool)$persistconvo,
            'assistantName' => $assistantname,
            'userName' => $username,
            'showlabels' => (bool)$this->config->showlabels,
        ]]);

        $this->content = new stdClass;
        $this->content->text = '';

        if (get_config('block_exaaichat', 'allowproviderselection')) {
            if (get_config('block_exaaichat', 'allowinstancesettings')) {
                $config = $this->config;
                // falls model "other" gewählt wurde, dann den Wert aus dem Eingabefeld model_other verwenden
                if (($config->model ?? '') === 'other') {
                    $config->model = $config->model_other ?? '';
                }
            }
            $model = $config->model ?? '' ?: get_config('block_exaaichat', 'model') ?: 'chat';

            $ai_providers = [
                ['id' => '', 'label' => $model],
                ...array_map(fn($ai_provider) => ['id' => $ai_provider->id, 'label' => $ai_provider->name . ($ai_provider->model ? ' (' . $ai_provider->model . ')' : '')], locallib::get_moodle_ai_providers()),
            ];
        } else {
            $ai_providers = [];
        }

        if (
            empty(get_config('block_exaaichat', 'apikey')) &&
            (!get_config('block_exaaichat', 'allowinstancesettings') || empty($this->config->apikey))
        ) {
            $this->content->text .= get_string('apikeymissing', 'block_exaaichat');
        } else {
            $contextdata = [
                'logging_enabled' => get_config('block_exaaichat', 'logging'),
                'show_top_buttons' => !$PAGE->user_is_editing() || $as_aiplacement_content,
                'settings_url' => $as_aiplacement_content ?
                    // auf die editmode.php verlinken und dann zum block edit weiterleiten
                    // weil block editieren nur mit aktivem editmode geht!
                    (new \moodle_url('/editmode.php', ['setmode' => 1, 'context' => \context_system::instance()->id, 'sesskey' => sesskey(),
                        'pageurl' => (new \moodle_url('/course/view.php', ['id' => $COURSE->id, 'bui_editid' => $this->instance->id]))->out(false),
                    ]))->out(false) : null,
                'pix_popout' => '/blocks/exaaichat/pix/arrow-up-right-from-square.svg',
                'pix_arrow_right' => '/blocks/exaaichat/pix/arrow-right.svg',
                'pix_refresh' => '/blocks/exaaichat/pix/refresh.svg',
                'show_ai_provider_select' => count($ai_providers) > 1,
                'ai_providers' => $ai_providers,
            ];

            $this->content->text .= $OUTPUT->render_from_template('block_exaaichat/chat_component', $contextdata);
        }

        if ($PAGE->requires->get_jsrev() == -1 && $_SERVER['HTTP_HOST'] == 'localhost') {
            // for debugging prevent moodle to clear localstorage on each page load
            $this->content->text .= "<script> localStorage.clear = function() { console.log('localStorage.clear() disabled by block_exaaichat for debugging')}; </script>";
        }

        return $this->content;
    }
}
