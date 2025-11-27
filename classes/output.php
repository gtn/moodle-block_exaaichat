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

    public static function render_aiplacement_content(): string {
        global $COURSE, $DB, $PAGE, $OUTPUT;

        $regions = $PAGE->blocks->get_regions();

        $block_instance_current_page = null;
        foreach ($regions as $region) {
            $instances = $PAGE->blocks->get_blocks_for_region($region);

            foreach ($instances as $block_instance_test) {
                if ($block_instance_test instanceof \block_exaaichat) {
                    $block_instance_current_page = $block_instance_test;
                    break 2;
                }
            }
        }

        if ($block_instance_current_page) {
            $visible = $DB->get_field('block_positions', 'visible', [
                'blockinstanceid' => $block_instance_current_page->instance->id,
                'contextid' => $block_instance_current_page->context->get_parent_context()->id,
                // 'region' => $block_instance_current_page->instance->region,
            ]);

            if ($visible === false) {
                // === false => couldn't find visibility record, so block is visible
                return '';
            } elseif ($visible) {
                // block is visible on this page, so do not show chat
                return '';
            }
        }

        $block_record_course = $DB->get_record('block_instances', [
            'blockname' => 'exaaichat',
            'parentcontextid' => \context_course::instance($COURSE->id)->id,
        ]);

        if ($block_record_course) {
            /* @var \block_exaaichat $block_instance_course */
            $block_instance_course = block_instance($block_record_course->blockname, $block_record_course);

            $content = output::render_chat_interface($block_instance_course->instance->id, $block_instance_course->config ?? (object)[], true, true);
            $instance_id = $block_instance_course->instance->id;
        } else {
            $instance_id = 'course-' . $COURSE->id;
            $content = static::render_chat_interface($instance_id, (object)[], false, true);
        }

        return $OUTPUT->render_from_template('block_exaaichat/aiplacement_content', [
            'blockinstanceid' => $instance_id,
            'content' => $content->text,
        ]);
    }

    /**
     * @param string $instance_id the instanceid of this block, either int (for a real block, or course-{id} for a course level instance without the actual block in the course)
     * @param object $config
     * @param bool $is_block_instance (is a block instance, false = no block in the course)
     * @param bool $as_aiplacement_content (is the block content, or the content of the aiplacement)
     * @return object
     * @throws \coding_exception
     * @throws \core\exception\moodle_exception
     * @throws \dml_exception
     */
    public static function render_chat_interface(string $instance_id, object $config, bool $is_block_instance, bool $as_aiplacement_content): object {
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

        $apikey =
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
            if ($apikey) {
                $ai_providers[] = ['id' => '', 'label' => $model];
            }
            $moodle_ai_proviers = locallib::get_moodle_ai_providers();
            $ai_providers = [
                ...$ai_providers,
                ...array_map(fn($ai_provider) => ['id' => $ai_provider->id, 'label' => $ai_provider->name . ($ai_provider->model && $ai_provider->model != $ai_provider->name ? ' (' . $ai_provider->model . ')' : '')], $moodle_ai_proviers),
            ];

            if (!$ai_providers) {
                $content->text .= get_string('apikeymissing', 'block_exaaichat');
                return $content;
            }
        } else {
            $ai_providers = [];
            if (!$apikey) {
                $content->text .= get_string('apikeymissing', 'block_exaaichat');
                return $content;
            }
        }

        $PAGE->requires->js_call_amd('block_exaaichat/lib', 'init', [[
            'blockId' => (string)$instance_id, // can be string or int
            'api_type' => (string)$api_type,
            'persistConvo' => (bool)$persistconvo,
            'assistantName' => $assistantname,
            'userName' => $username,
            'showlabels' => (bool)($config->showlabels ?? true),
            'allow_access_to_current_page' => (bool)get_config('block_exaaichat', 'allow_access_to_current_page'),
        ]]);

        $canEdit = has_capability('moodle/course:update', \context_course::instance($COURSE->id));

        $contextdata = [
            'logging_enabled' => get_config('block_exaaichat', 'logging'),
            'show_top_buttons' => $as_aiplacement_content || !$PAGE->user_is_editing(), // always show top buttons in aiplacement, otherwise (in the block) only when not editing
            'settings_url' => $is_block_instance && $as_aiplacement_content && $canEdit ?
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
