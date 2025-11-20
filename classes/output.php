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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_exaaichat;

defined('MOODLE_INTERNAL') || die;

class output {
    public static function render_default_chat_interface(): object {
        global $COURSE;

        $instance_id = 'course-' . $COURSE->id;

        $content = static::render_chat_interface($instance_id, (object)[], true);
        $content->instance_id = $instance_id;

        return $content;

        /*
        require_once $CFG->dirroot . '/blocks/exaaichat/block_exaaichat.php';
        $block_instance_course = new \block_exaaichat();
        $block_instance_course->page = $PAGE;
        $block_instance_course->config = (object)[];
        $block_instance_course->instance = (object)['id' => $instance_id];

        $content = $block_instance_course->get_content(true);
        $content->instance_id = $instance_id;
        return $content;
        */
    }

    public static function render_chat_interface(string $instance_id, object $config, bool $as_aiplacement_content): object {
        global $COURSE, $OUTPUT, $PAGE;

        $context = \context_course::instance($COURSE->id);

        // Send data to front end
        $persistconvo = get_config('block_exaaichat', 'persistconvo');
        if (!empty($config)) {
            $persistconvo = (property_exists($config, 'persistconvo') && get_config('block_exaaichat', 'allowinstancesettings')) ? $config->persistconvo : $persistconvo;
        }

        $api_type = '';
        if (get_config('block_exaaichat', 'allowinstancesettings')) {
            // allow switching to different api
            $api_type = $config->api_type ?? '';
        }
        if (!$api_type) {
            $api_type = \block_exaaichat\locallib::get_api_type();
        }

        $api_key =
            (get_config('block_exaaichat', 'allowinstancesettings') ? $config->apikey ?? '' : '')
                ?: get_config('block_exaaichat', 'apikey');

        // First, fetch the global settings for these (and the defaults if not set)
        $assistantname = get_config('block_exaaichat', 'assistantname') ?: get_string('defaultassistantname', 'block_exaaichat');
        $username = get_config('block_exaaichat', 'username') ?: get_string('defaultusername', 'block_exaaichat');

        // Then, override with local settings if available
        if (!empty($config)) {
            $assistantname = (property_exists($config, 'assistantname') && $config->assistantname) ? $config->assistantname : $assistantname;
            $username = (property_exists($config, 'username') && $config->username) ? $config->username : $username;
        }
        $assistantname = format_string($assistantname, true, ['context' => $context]);
        $username = format_string($username, true, ['context' => $context]);

        $content = (object)[];
        $content->text = '';

        if (get_config('block_exaaichat', 'allowproviderselection')) {
            if (get_config('block_exaaichat', 'allowinstancesettings')) {
                $config = $config;
                // falls model "other" gewählt wurde, dann den Wert aus dem Eingabefeld model_other verwenden
                if (($config->model ?? '') === 'other') {
                    $config->model = $config->model_other ?? '';
                }
            }
            $model = $config->model ?? '' ?: get_config('block_exaaichat', 'model') ?: 'chat';

            $ai_providers = [];
            if ($api_key) {
                $ai_providers[] = ['id' => '', 'label' => $model];
            }
            $ai_providers = [
                ...$ai_providers,
                ...array_map(fn($ai_provider) => ['id' => $ai_provider->id, 'label' => $ai_provider->name . ($ai_provider->model && $ai_provider->model != $ai_provider->name ? ' (' . $ai_provider->model . ')' : '')], locallib::get_moodle_ai_providers()),
            ];

            if (!$ai_providers) {
                $content->text .= get_string('apikeymissing', 'block_exaaichat');
                return $content;
            }
        } else {
            $ai_providers = [];
            if (!$api_key) {
                $content->text .= get_string('apikeymissing', 'block_exaaichat');
                return $content;
            }
        }

        $PAGE->requires->js_call_amd('block_exaaichat/lib', 'init', [[
            'blockId' => $instance_id, // can be string or int
            'api_type' => $api_type,
            'persistConvo' => (bool)$persistconvo,
            'assistantName' => $assistantname,
            'userName' => $username,
            'showlabels' => (bool)($config->showlabels ?? true),
        ]]);

        $contextdata = [
            'logging_enabled' => get_config('block_exaaichat', 'logging'),
            'show_top_buttons' => !$PAGE->user_is_editing() || $as_aiplacement_content,
            'settings_url' => $as_aiplacement_content ?
                // auf die editmode.php verlinken und dann zum block edit weiterleiten
                // weil block editieren nur mit aktivem editmode geht!
                (new \moodle_url('/editmode.php', ['setmode' => 1, 'context' => \context_system::instance()->id, 'sesskey' => sesskey(),
                    'pageurl' => (new \moodle_url('/course/view.php', ['id' => $COURSE->id, 'bui_editid' => $instance_id]))->out(false),
                ]))->out(false) : null,
            'pix_popout' => '/blocks/exaaichat/pix/arrow-up-right-from-square.svg',
            'pix_arrow_right' => '/blocks/exaaichat/pix/arrow-right.svg',
            'pix_refresh' => '/blocks/exaaichat/pix/refresh.svg',
            'show_ai_provider_select' => count($ai_providers) > 1,
            'ai_providers' => $ai_providers,
        ];

        $content->text .= $OUTPUT->render_from_template('block_exaaichat/chat_component', $contextdata);

        if ($PAGE->requires->get_jsrev() == -1 && $_SERVER['HTTP_HOST'] == 'localhost') {
            // for debugging prevent moodle to clear localstorage on each page load
            $content->text .= "<script> localStorage.clear = function() { console.log('localStorage.clear() disabled by block_exaaichat for debugging')}; </script>";
        }

        return $content;
    }
}
