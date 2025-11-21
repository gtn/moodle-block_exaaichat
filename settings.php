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
 * Plugin settings
 *
 * @package    block_exaaichat
 * @copyright  2025 GTN Solutions https://gtn-solutions.com
 * @copyright  based on work by Limekiller https://github.com/Limekiller/moodle-block_openai_chat
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    if (!defined('BEHAT_SITE_RUNNING')) {
        $ADMIN->add('reports', new admin_externalpage(
            'exaaichat_report',
            get_string('exaaichat_logs', 'block_exaaichat'),
            new moodle_url("$CFG->wwwroot/blocks/exaaichat/report.php", ['courseid' => 1]),
            'moodle/site:config'
        ));
    }

    if ($ADMIN->fulltree) {

        require_once($CFG->dirroot . '/blocks/exaaichat/lib.php');

        $type = \block_exaaichat\locallib::get_api_type();
        $assistant_array = [];
        if ($type === 'assistant') {
            $assistant_array = block_exaaichat_fetch_assistants_array();
        }

        global $PAGE;
        $PAGE->requires->js_call_amd('block_exaaichat/settings', 'init');

        $settings->add(new admin_setting_configselect(
            'block_exaaichat/type',
            get_string('type', 'block_exaaichat'),
            get_string('type:desc', 'block_exaaichat'),
            'chat',
            [
                'chat' => get_string('type_chat', 'block_exaaichat'),
                'assistant' => get_string('type_assistant', 'block_exaaichat'),
                'responses' => get_string('type_responses', 'block_exaaichat'),
                'azure' => get_string('type_azure', 'block_exaaichat'),
            ]
        ));

        $settings->add(new admin_setting_configtext(
            'block_exaaichat/apikey',
            get_string('apikey', 'block_exaaichat'),
            get_string('apikey:desc', 'block_exaaichat'),
            '',
            PARAM_TEXT
        ));

        $settings->add(new admin_setting_configcheckbox(
            'block_exaaichat/restrictusage',
            get_string('restrictusage', 'block_exaaichat'),
            get_string('restrictusage:desc', 'block_exaaichat'),
            1
        ));

        $settings->add(new admin_setting_configtext(
            'block_exaaichat/assistantname',
            get_string('assistantname', 'block_exaaichat'),
            get_string('assistantname:desc', 'block_exaaichat'),
            get_string('defaultassistantname', 'block_exaaichat'),
            PARAM_TEXT
        ));

        $settings->add(new admin_setting_configtext(
            'block_exaaichat/username',
            get_string('username', 'block_exaaichat'),
            get_string('username:desc', 'block_exaaichat'),
            get_string('defaultusername', 'block_exaaichat'),
            PARAM_TEXT
        ));

        $settings->add(new admin_setting_configcheckbox(
            'block_exaaichat/logging',
            get_string('logging', 'block_exaaichat'),
            get_string('logging:desc', 'block_exaaichat'),
            0
        ));

        $settings->add(new admin_setting_configtext(
            'block_exaaichat/logging_retention_period',
            get_string('logging_retention_period', 'block_exaaichat'),
            get_string('logging_retention_period:desc', 'block_exaaichat'),
            0,
            PARAM_INT
        ));
        $settings->hide_if('block_exaaichat/logging_retention_period', 'block_exaaichat/logging', 'notchecked');

        $settings->add(new admin_setting_configcheckbox(
            'block_exaaichat/allowinstancesettings',
            get_string('allowinstancesettings', 'block_exaaichat'),
            get_string('allowinstancesettings:desc', 'block_exaaichat'),
            0
        ));

        $settings->add(new admin_setting_configcheckbox(
            'block_exaaichat/persistconvo',
            get_string('persistconvo', 'block_exaaichat'),
            get_string('persistconvo:desc', 'block_exaaichat'),
            1
        ));

        $settings->add(new admin_setting_configcheckbox(
            'block_exaaichat/allowproviderselection',
            get_string('allowproviderselection', 'block_exaaichat'),
            get_string('allowproviderselection:desc', 'block_exaaichat'),
            1
        ));

        $settings->add(new admin_setting_configcheckbox(
            'block_exaaichat/allow_access_to_current_page',
            get_string('allow_access_to_page_content', 'block_exaaichat'),
            get_string('allow_access_to_page_content:desc', 'block_exaaichat'),
            0
        ));

        $settings->add(new admin_setting_configcheckbox(
            'block_exaaichat/debug_file_logging',
            get_string('debugfilelogging', 'block_exaaichat'),
            get_string('debugfilelogging:desc', 'block_exaaichat'),
            0
        ));

        // Assistant settings //

        if ($type === 'assistant') {
            $settings->add(new admin_setting_heading(
                'block_exaaichat/assistantheading',
                get_string('assistantheading', 'block_exaaichat'),
                get_string('assistantheading:desc', 'block_exaaichat')
            ));

            if (count($assistant_array)) {
                $settings->add(new admin_setting_configselect(
                    'block_exaaichat/assistant',
                    get_string('assistant', 'block_exaaichat'),
                    get_string('assistant:desc', 'block_exaaichat'),
                    count($assistant_array) ? reset($assistant_array) : null,
                    $assistant_array
                ));
            } else {
                $settings->add(new admin_setting_description(
                    'block_exaaichat/noassistants',
                    get_string('assistant', 'block_exaaichat'),
                    get_string('noassistants', 'block_exaaichat'),
                ));
            }
        } elseif ($type === 'azure') {
            $settings->add(new admin_setting_heading(
                'block_exaaichat/azureheading',
                get_string('azureheading', 'block_exaaichat'),
                get_string('azureheading:desc', 'block_exaaichat')
            ));

            $settings->add(new admin_setting_configtext(
                'block_exaaichat/resourcename',
                get_string('resourcename', 'block_exaaichat'),
                get_string('resourcename:desc', 'block_exaaichat'),
                "",
                PARAM_TEXT
            ));

            $settings->add(new admin_setting_configtext(
                'block_exaaichat/deploymentid',
                get_string('deploymentid', 'block_exaaichat'),
                get_string('deploymentid:desc', 'block_exaaichat'),
                "",
                PARAM_TEXT
            ));

            $settings->add(new admin_setting_configtext(
                'block_exaaichat/apiversion',
                get_string('apiversion', 'block_exaaichat'),
                get_string('apiversion:desc', 'block_exaaichat'),
                "2023-09-01-preview",
                PARAM_TEXT
            ));
        }


        // Chat settings
        $settings->add(new admin_setting_heading(
            'block_exaaichat/chatheading',
            get_string('chatheading', 'block_exaaichat'),
            get_string('chatheading:desc', 'block_exaaichat')
        ));

        $settings->add(new admin_setting_configtextarea(
            'block_exaaichat/prompt',
            get_string('prompt', 'block_exaaichat'),
            get_string('prompt:desc', 'block_exaaichat'),
            get_string('defaultprompt', 'block_exaaichat'),
            PARAM_TEXT
        ));

        $settings->add(new admin_setting_configtextarea(
            'block_exaaichat/sourceoftruth',
            get_string('sourceoftruth', 'block_exaaichat'),
            get_string('sourceoftruth:desc', 'block_exaaichat'),
            '',
            PARAM_TEXT
        ));

        if ($type === 'responses') {
            $settings->add(new admin_setting_configtext(
                'block_exaaichat/additional_message',
                get_string('additionalmessage', 'block_exaaichat'),
                get_string('additionalmessage:desc', 'block_exaaichat'),
                '',
                PARAM_TEXT
            ));
        }


        // Advanced Settings

        $settings->add(new admin_setting_heading(
            'block_exaaichat/advanced',
            get_string('advanced', 'block_exaaichat'),
            get_string('advanced:desc', 'block_exaaichat')
        ));
        if ($type === 'assistant') {

        } else {
            $settings->add(new admin_setting_configtext(
                'block_exaaichat/openai_api_url',
                get_string('openai_api_url', 'block_exaaichat'),
                get_string('openai_api_url:desc', 'block_exaaichat'),
                'https://api.openai.com/v1',
                PARAM_URL
            ));

            $settings->add(new admin_setting_configtextarea(
                'block_exaaichat/models',
                get_string('models', 'block_exaaichat'),
                get_string('models:desc', 'block_exaaichat'),
                ''
            ));

            $settings->add(new admin_setting_configselect(
                'block_exaaichat/model',
                get_string('model', 'block_exaaichat'),
                get_string('model:desc', 'block_exaaichat'),
                'text-davinci-003',
                \block_exaaichat\locallib::get_models()
            ));

            $settings->add(new admin_setting_configtext(
                'block_exaaichat/temperature',
                get_string('temperature', 'block_exaaichat'),
                get_string('temperature:desc', 'block_exaaichat'),
                0.5,
                PARAM_FLOAT
            ));

            $settings->add(new admin_setting_configtext(
                'block_exaaichat/maxlength',
                get_string('maxlength', 'block_exaaichat'),
                get_string('maxlength:desc', 'block_exaaichat'),
                500,
                PARAM_INT
            ));

            $settings->add(new admin_setting_configtext(
                'block_exaaichat/topp',
                get_string('topp', 'block_exaaichat'),
                get_string('topp:desc', 'block_exaaichat'),
                1,
                PARAM_FLOAT
            ));

            $settings->add(new admin_setting_configtext(
                'block_exaaichat/frequency',
                get_string('frequency', 'block_exaaichat'),
                get_string('frequency:desc', 'block_exaaichat'),
                1,
                PARAM_FLOAT
            ));

            $settings->add(new admin_setting_configtext(
                'block_exaaichat/presence',
                get_string('presence', 'block_exaaichat'),
                get_string('presence:desc', 'block_exaaichat'),
                1,
                PARAM_FLOAT
            ));
        }
    }
}
