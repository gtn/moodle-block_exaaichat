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
 * @copyright  2024 Bryce Yoder <me@bryceyoder.com>
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

        require_once($CFG->dirroot .'/blocks/exaaichat/lib.php');

        $type = block_exaaichat_get_type_to_display();
        $assistant_array = [];
        if ($type === 'assistant') {
            $assistant_array = block_exaaichat_fetch_assistants_array();
        }

        global $PAGE;
        $PAGE->requires->js_call_amd('block_exaaichat/settings', 'init');

        $settings->add(new admin_setting_configtext(
            'block_exaaichat/apikey',
            get_string('apikey', 'block_exaaichat'),
            get_string('apikeydesc', 'block_exaaichat'),
            '',
            PARAM_TEXT
        ));

        $settings->add(new admin_setting_configselect(
            'block_exaaichat/type',
            get_string('type', 'block_exaaichat'),
            get_string('typedesc', 'block_exaaichat'),
            'chat',
            ['chat' => 'chat', 'assistant' => 'assistant', 'azure' => 'azure', 'responses' => 'responses']
        ));

        $settings->add(new admin_setting_configcheckbox(
            'block_exaaichat/restrictusage',
            get_string('restrictusage', 'block_exaaichat'),
            get_string('restrictusagedesc', 'block_exaaichat'),
            1
        ));

        $settings->add(new admin_setting_configtext(
            'block_exaaichat/assistantname',
            get_string('assistantname', 'block_exaaichat'),
            get_string('assistantnamedesc', 'block_exaaichat'),
            'Assistant',
            PARAM_TEXT
        ));

        $settings->add(new admin_setting_configtext(
            'block_exaaichat/username',
            get_string('username', 'block_exaaichat'),
            get_string('usernamedesc', 'block_exaaichat'),
            'User',
            PARAM_TEXT
        ));

        $settings->add(new admin_setting_configcheckbox(
            'block_exaaichat/logging',
            get_string('logging', 'block_exaaichat'),
            get_string('loggingdesc', 'block_exaaichat'),
            0
        ));

        $settings->add(new admin_setting_configcheckbox(
            'block_exaaichat/debug_file_logging',
            'Enable debug logging',
            'All api calls (User messages, AI responses and function calls) will be logged to moodledata/log/exaaichat.log',
            0
        ));

        // Assistant settings //

        if ($type === 'assistant') {
            $settings->add(new admin_setting_heading(
                'block_exaaichat/assistantheading',
                get_string('assistantheading', 'block_exaaichat'),
                get_string('assistantheadingdesc', 'block_exaaichat')
            ));

            if (count($assistant_array)) {
                $settings->add(new admin_setting_configselect(
                    'block_exaaichat/assistant',
                    get_string('assistant', 'block_exaaichat'),
                    get_string('assistantdesc', 'block_exaaichat'),
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

            $settings->add(new admin_setting_configcheckbox(
                'block_exaaichat/persistconvo',
                get_string('persistconvo', 'block_exaaichat'),
                get_string('persistconvodesc', 'block_exaaichat'),
                1
            ));

        } else {

            // Chat settings //

            if ($type === 'azure') {
                $settings->add(new admin_setting_heading(
                    'block_exaaichat/azureheading',
                    get_string('azureheading', 'block_exaaichat'),
                    get_string('azureheadingdesc', 'block_exaaichat')
                ));

                $settings->add(new admin_setting_configtext(
                    'block_exaaichat/resourcename',
                    get_string('resourcename', 'block_exaaichat'),
                    get_string('resourcenamedesc', 'block_exaaichat'),
                    "",
                    PARAM_TEXT
                ));

                $settings->add(new admin_setting_configtext(
                    'block_exaaichat/deploymentid',
                    get_string('deploymentid', 'block_exaaichat'),
                    get_string('deploymentiddesc', 'block_exaaichat'),
                    "",
                    PARAM_TEXT
                ));

                $settings->add(new admin_setting_configtext(
                    'block_exaaichat/apiversion',
                    get_string('apiversion', 'block_exaaichat'),
                    get_string('apiversiondesc', 'block_exaaichat'),
                    "2023-09-01-preview",
                    PARAM_TEXT
                ));
            }

            $settings->add(new admin_setting_heading(
                'block_exaaichat/chatheading',
                get_string('chatheading', 'block_exaaichat'),
                get_string('chatheadingdesc', 'block_exaaichat')
            ));

            $settings->add(new admin_setting_configtextarea(
                'block_exaaichat/prompt',
                get_string('prompt', 'block_exaaichat'),
                get_string('promptdesc', 'block_exaaichat'),
                "Below is a conversation between a user and a support assistant for a Moodle site, where users go for online learning.",
                PARAM_TEXT
            ));

            $settings->add(new admin_setting_configtextarea(
                'block_exaaichat/sourceoftruth',
                get_string('sourceoftruth', 'block_exaaichat'),
                get_string('sourceoftruthdesc', 'block_exaaichat'),
                '',
                PARAM_TEXT
            ));
        }


        // Advanced Settings //

        $settings->add(new admin_setting_heading(
            'block_exaaichat/advanced',
            get_string('advanced', 'block_exaaichat'),
            get_string('advanceddesc', 'block_exaaichat')
        ));

        $settings->add(new admin_setting_configcheckbox(
            'block_exaaichat/allowinstancesettings',
            get_string('allowinstancesettings', 'block_exaaichat'),
            get_string('allowinstancesettingsdesc', 'block_exaaichat'),
            0
        ));

        if ($type === 'responses') {
            $settings->add(new admin_setting_configtext(
                'block_exaaichat/additional_message',
                'Additional text for every message',
                '',
                '',
                PARAM_TEXT
            ));
        }

        if ($type === 'assistant') {

        } else {
            $settings->add(new admin_setting_configselect(
                'block_exaaichat/model',
                get_string('model', 'block_exaaichat'),
                get_string('modeldesc', 'block_exaaichat'),
                'text-davinci-003',
                block_exaaichat_get_models()['models']
            ));

            $settings->add(new admin_setting_configtext(
                'block_exaaichat/temperature',
                get_string('temperature', 'block_exaaichat'),
                get_string('temperaturedesc', 'block_exaaichat'),
                0.5,
                PARAM_FLOAT
            ));

            $settings->add(new admin_setting_configtext(
                'block_exaaichat/maxlength',
                get_string('maxlength', 'block_exaaichat'),
                get_string('maxlengthdesc', 'block_exaaichat'),
                500,
                PARAM_INT
            ));

            $settings->add(new admin_setting_configtext(
                'block_exaaichat/topp',
                get_string('topp', 'block_exaaichat'),
                get_string('toppdesc', 'block_exaaichat'),
                1,
                PARAM_FLOAT
            ));

            $settings->add(new admin_setting_configtext(
                'block_exaaichat/frequency',
                get_string('frequency', 'block_exaaichat'),
                get_string('frequencydesc', 'block_exaaichat'),
                1,
                PARAM_FLOAT
            ));

            $settings->add(new admin_setting_configtext(
                'block_exaaichat/presence',
                get_string('presence', 'block_exaaichat'),
                get_string('presencedesc', 'block_exaaichat'),
                1,
                PARAM_FLOAT
            ));
        }
    }
}
