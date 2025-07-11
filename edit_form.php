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
 * Per-block settings
 *
 * @package    block_exaaichat
 * @copyright  2025 GTN Solutions https://gtn-solutions.com
 * @copyright  based on work by Limekiller https://github.com/Limekiller/moodle-block_openai_chat
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/blocks/exaaichat/lib.php');

class block_exaaichat_edit_form extends block_edit_form {

    /**
     * Get the activies in the course (those that can get grades)
     * @param int $courseid
     * @return array
     */
    private function get_placeholders(int $courseid): array {
        global $DB;

        // Get information about course modules and existing module types.
        // based on course/view.php
        $modinfo = get_fast_modinfo($courseid);
        $mods = $modinfo->get_cms();

        // we only need the id and the name of the modules to return
        // TODO: get_content_items_for_user_in_course something like that to only get the activities, not resources? archetype=1 = resource, archetype=0  = activity

        $placeholders = [
            get_string('placeholders:user.fullname:placeholder', 'block_exaaichat', '{user.fullname}') => get_string('placeholders:user.fullname:name', 'block_exaaichat'),
            get_string('placeholders:userdate:placeholder', 'block_exaaichat', '{userdate}') => get_string('placeholders:userdate:name', 'block_exaaichat'),
        ];

        foreach ($mods as $mod) {
            // plugin_supports MOD_ARCHETYPE_RESOURCE
            // $archetype = plugin_supports('mod', $mod->modname, FEATURE_MOD_ARCHETYPE, MOD_ARCHETYPE_OTHER); // to check if it is a resource. If it is a resource, you cannot grade it ==> skip it
            // if ($archetype === MOD_ARCHETYPE_RESOURCE) {
            //     continue; // skip resources
            // }

            // if (method_exists($mod, 'get_instance_record')) {
            //     only available since moodle 5.0
                // $instance = $mod->get_instance_record(); // gets the record of table "modname" with id "instance"
            // } else {
                $instance = $DB->get_record($mod->modname, ['id' => $mod->instance], '*', MUST_EXIST); // gets the record of table "modname" with id "instance"
            // }
            $modulename = $mod->modname;

            switch ($modulename) {
                case 'assign':
                case 'quiz':
                case 'lesson':
                case 'workshop':
                case 'choice':
                    // TODO: sollte einfach !empty($instance->grade) sein?
                    $hasgrading = !empty($instance->grade);
                    break;

                case 'forum':
                    $hasgrading = isset($instance->grade_forum) && (int)$instance->grade_forum !== 0;
                    break;

                case 'lti':
                    $hasgrading = isset($instance->grade_max) && (int)$instance->grade_max > 0;
                    break;

                case 'scorm':
                    $hasgrading = isset($instance->grademethod) && (int)$instance->grademethod !== 0;
                    break;

                // Add additional modules as needed here

                default:
                    // fallback for other modules with a 'grade' field
                    $hasgrading = isset($instance->grade) && (int)$instance->grade !== 0;
                    break;
            }

            if ($hasgrading) {
                // add the name and the type
                $name = get_string('modulename', $mod->modname) . ' "' . $mod->name . '"';

                $placeholders[get_string('placeholders:grade:placeholder', 'block_exaaichat', $name) . ": {grade:{$mod->name}}"] = get_string('placeholders:grade:name', 'block_exaaichat', $name);
            }
        }

        return $placeholders;
    }

    protected function specific_definition($mform) {
        global $COURSE, $PAGE;

        // this does not work if the form is displayed on the config page
        // $block_id = $this->_ajaxformdata["blockid"];
        // solution:
        $block_id = $this->get_block()->instance->id;

        $type = block_exaaichat_get_type_to_display();

        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('blocktitle', 'block_exaaichat'));
        $mform->setDefault('config_title', 'Exabis AI Chat');
        $mform->setType('config_title', PARAM_TEXT);

        $mform->addElement('advcheckbox', 'config_showlabels', get_string('showlabels', 'block_exaaichat'));
        $mform->setDefault('config_showlabels', 1);

        if ($type === 'assistant') {
            // Assistant settings

            if (get_config('block_exaaichat', 'allowinstancesettings') === "1") {
                $mform->addElement('select', 'config_assistant', get_string('assistant', 'block_exaaichat'), block_exaaichat_fetch_assistants_array($block_id));
                $mform->setDefault('config_assistant', get_config('block_exaaichat', 'assistant'));
                $mform->setType('config_assistant', PARAM_TEXT);
                $mform->addHelpButton('config_assistant', 'config_assistant', 'block_exaaichat');

                $mform->addElement('advcheckbox', 'config_persistconvo', get_string('persistconvo', 'block_exaaichat'));
                $mform->addHelpButton('config_persistconvo', 'config_persistconvo', 'block_exaaichat');
                $mform->setDefault('config_persistconvo', 1);

                $mform->addElement('textarea', 'config_instructions', get_string('config_instructions', 'block_exaaichat'));
                $mform->setDefault('config_instructions', '');
                $mform->setType('config_instructions', PARAM_TEXT);
                $mform->addHelpButton('config_instructions', 'config_instructions', 'block_exaaichat');

                $mform->addElement('text', 'config_username', get_string('username', 'block_exaaichat'));
                $mform->setDefault('config_username', '');
                $mform->setType('config_username', PARAM_TEXT);
                $mform->addHelpButton('config_username', 'config_username', 'block_exaaichat');

                $mform->addElement('text', 'config_assistantname', get_string('assistantname', 'block_exaaichat'));
                $mform->setDefault('config_assistantname', '');
                $mform->setType('config_assistantname', PARAM_TEXT);
                $mform->addHelpButton('config_assistantname', 'config_assistantname', 'block_exaaichat');

                $mform->addElement('header', 'config_adv_header', get_string('advanced', 'block_exaaichat'));

                $mform->addElement('text', 'config_apikey', get_string('apikey', 'block_exaaichat'));
                $mform->setDefault('config_apikey', '');
                $mform->setType('config_apikey', PARAM_TEXT);
                $mform->addHelpButton('config_apikey', 'config_apikey', 'block_exaaichat');
            }

        } elseif ($type === 'responses') {
            if (get_config('block_exaaichat', 'allowinstancesettings') === "1") {
                $mform->addElement('textarea', 'config_instructions', get_string('config_instructions', 'block_exaaichat'));
                $mform->setDefault('config_instructions', '');
                $mform->setType('config_instructions', PARAM_TEXT);
                $mform->addHelpButton('config_instructions', 'config_instructions', 'block_exaaichat');

                $mform->addElement('text', 'config_username', get_string('username', 'block_exaaichat'));
                $mform->setDefault('config_username', '');
                $mform->setType('config_username', PARAM_TEXT);
                $mform->addHelpButton('config_username', 'config_username', 'block_exaaichat');

                $mform->addElement('text', 'config_assistantname', get_string('assistantname', 'block_exaaichat'));
                $mform->setDefault('config_assistantname', '');
                $mform->setType('config_assistantname', PARAM_TEXT);
                $mform->addHelpButton('config_assistantname', 'config_assistantname', 'block_exaaichat');

                $mform->addElement('header', 'config_adv_header', get_string('advanced', 'block_exaaichat'));

                $mform->addElement('text', 'config_apikey', get_string('apikey', 'block_exaaichat'));
                $mform->setDefault('config_apikey', '');
                $mform->setType('config_apikey', PARAM_TEXT);
                $mform->addHelpButton('config_apikey', 'config_apikey', 'block_exaaichat');

                $mform->addElement('select', 'config_model', get_string('model', 'block_exaaichat'), block_exaaichat_get_models()['models']);
                $mform->setDefault('config_model', get_config('block_exaaichat', 'model'));
                $mform->setType('config_model', PARAM_TEXT);
                $mform->addHelpButton('config_model', 'config_model', 'block_exaaichat');

                $mform->addElement('text', 'config_temperature', get_string('temperature', 'block_exaaichat'));
                $mform->setDefault('config_temperature', 0.5);
                $mform->setType('config_temperature', PARAM_FLOAT);
                $mform->addHelpButton('config_temperature', 'config_temperature', 'block_exaaichat');

                $mform->addElement('text', 'config_topp', get_string('topp', 'block_exaaichat'));
                $mform->setDefault('config_topp', 1);
                $mform->setType('config_topp', PARAM_FLOAT);
                $mform->addHelpButton('config_topp', 'config_topp', 'block_exaaichat');

                $mform->addElement('text', 'config_vector_store_ids', 'vector_store_ids');
                $mform->setDefault('config_vector_store_ids', '');
                $mform->setType('config_vector_store_ids', PARAM_TEXT);
            }
        } else {
            // Chat settings

            $el = $mform->addElement('textarea', 'config_sourceoftruth', get_string('sourceoftruth', 'block_exaaichat'));
            $el->updateAttributes(['rows' => 10]);
            $mform->setDefault('config_sourceoftruth', '');
            $mform->setType('config_sourceoftruth', PARAM_TEXT);
            $mform->addHelpButton('config_sourceoftruth', 'config_sourceoftruth', 'block_exaaichat');

            // Dropdown menu for activities
            $placeholders = $this->get_placeholders($COURSE->id);

            ob_start();
            ?>
            <div style="margin - top: -10px">
                <div>
                    <?= get_string('addplaceholders:title', 'block_exaaichat') ?>:
                </div>
                <select id="config_placeholder_dropdown" class="form-control" style="display: inline-block; width: auto;">
                    <?php foreach ($placeholders as $key => $value): ?>
                        <option value="<?= s($key) ?>"><?= s($value) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" id="config_add_placeholder_button" class="btn btn-secondary" disabled>
                    <?= get_string('addplaceholders:button', 'block_exaaichat') ?>
                </button>
            </div>
            <script>
                function block_exaaichat_require(modules, callback) {
                    if (typeof require !== 'undefined') {
                        // require the init script for the config popup
                        require(modules, callback);
                    } else {
                        document.addEventListener('DOMContentLoaded', function () {
                            // require if the form is displayed on the config page
                            // then require is available after the DOM is loaded
                            require(modules, callback);
                        });
                    }
                }

                block_exaaichat_require(['block_exaaichat/config_popup'], function (m) {
                    m.init();
                });
            </script>
            <?php
            $html = ob_get_clean();
            $mform->addElement('static', 'user_message_options', '', $html);

            if (get_config('block_exaaichat', 'allowinstancesettings') === "1") {
                $mform->addElement('textarea', 'config_prompt', get_string('prompt', 'block_exaaichat'));
                $mform->setDefault('config_prompt', '');
                $mform->setType('config_prompt', PARAM_TEXT);
                $mform->addHelpButton('config_prompt', 'config_prompt', 'block_exaaichat');


                $mform->addElement('text', 'config_username', get_string('username', 'block_exaaichat'));
                $mform->setDefault('config_username', '');
                $mform->setType('config_username', PARAM_TEXT);
                $mform->addHelpButton('config_username', 'config_username', 'block_exaaichat');

                $mform->addElement('text', 'config_assistantname', get_string('assistantname', 'block_exaaichat'));
                $mform->setDefault('config_assistantname', '');
                $mform->setType('config_assistantname', PARAM_TEXT);
                $mform->addHelpButton('config_assistantname', 'config_assistantname', 'block_exaaichat');

                $mform->addElement('header', 'config_adv_header', get_string('advanced', 'block_exaaichat'));

                $mform->addElement('text', 'config_apikey', get_string('apikey', 'block_exaaichat'));
                $mform->setDefault('config_apikey', '');
                $mform->setType('config_apikey', PARAM_TEXT);
                $mform->addHelpButton('config_apikey', 'config_apikey', 'block_exaaichat');

                $mform->addElement('select', 'config_model', get_string('model', 'block_exaaichat'), block_exaaichat_get_models()['models']);
                $mform->setDefault('config_model', get_config('block_exaaichat', 'model'));
                $mform->setType('config_model', PARAM_TEXT);
                $mform->addHelpButton('config_model', 'config_model', 'block_exaaichat');

                $mform->addElement('text', 'config_temperature', get_string('temperature', 'block_exaaichat'));
                $mform->setDefault('config_temperature', 0.5);
                $mform->setType('config_temperature', PARAM_FLOAT);
                $mform->addHelpButton('config_temperature', 'config_temperature', 'block_exaaichat');

                $mform->addElement('text', 'config_maxlength', get_string('maxlength', 'block_exaaichat'));
                $mform->setDefault('config_maxlength', 500);
                $mform->setType('config_maxlength', PARAM_INT);
                $mform->addHelpButton('config_maxlength', 'config_maxlength', 'block_exaaichat');

                $mform->addElement('text', 'config_topp', get_string('topp', 'block_exaaichat'));
                $mform->setDefault('config_topp', 1);
                $mform->setType('config_topp', PARAM_FLOAT);
                $mform->addHelpButton('config_topp', 'config_topp', 'block_exaaichat');

                $mform->addElement('text', 'config_frequency', get_string('frequency', 'block_exaaichat'));
                $mform->setDefault('config_frequency', 1);
                $mform->setType('config_frequency', PARAM_FLOAT);
                $mform->addHelpButton('config_frequency', 'config_frequency', 'block_exaaichat');

                $mform->addElement('text', 'config_presence', get_string('presence', 'block_exaaichat'));
                $mform->setDefault('config_presence', 1);
                $mform->setType('config_presence', PARAM_FLOAT);
                $mform->addHelpButton('config_presence', 'config_presence', 'block_exaaichat');
            }
        }
    }
}
