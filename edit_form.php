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

require_once($CFG->dirroot . '/blocks/exaaichat/lib.php');

class block_exaaichat_edit_form extends block_edit_form {

    /**
     * @param $courseid
     * @return void
     * Get the activies in the course
     */
    private function fetch_course_activities($courseid) {
        // Get information about course modules and existing module types.
        // based on course/view.php
        $modinfo = get_fast_modinfo($courseid);
        $modnames = get_module_types_names();
        $modnamesplural = get_module_types_names(true);
        $modnamesused = $modinfo->get_used_module_names();
        $mods = $modinfo->get_cms();
        return $mods;
    }

    protected function specific_definition($mform) {
        global $COURSE, $PAGE;
        $block_id = $this->_ajaxformdata["blockid"];
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
            // Chat settings //

            $mform->addElement('textarea', 'config_sourceoftruth', get_string('sourceoftruth', 'block_exaaichat'));
            $mform->setDefault('config_sourceoftruth', '');
            $mform->setType('config_sourceoftruth', PARAM_TEXT);
            $mform->addHelpButton('config_sourceoftruth', 'config_sourceoftruth', 'block_exaaichat');

            if (get_config('block_exaaichat', 'allowinstancesettings') === "1") {
                $mform->addElement('textarea', 'config_prompt', get_string('prompt', 'block_exaaichat'));
                $mform->setDefault('config_prompt', '');
                $mform->setType('config_prompt', PARAM_TEXT);
                $mform->addHelpButton('config_prompt', 'config_prompt', 'block_exaaichat');


                // Dropdown menu for activities
                $activities = $this->fetch_course_activities($COURSE->id); // TODO: self:: vs $this-> ?
                $mform->addElement('select', 'config_activity_dropdown', get_string('selectactivity', 'block_openai_chat'), ["asdf", "qwer", "test"]);
                $mform->setDefault('config_activity_dropdown', '');
                $mform->addHelpButton('config_activity_dropdown', 'config_activity_dropdown', 'block_openai_chat');

                // Button to add activity to user message
                $mform->addElement('button', 'config_add_activity_button', get_string('addactivity', 'block_openai_chat'));

                // JavaScript for button functionality
                $PAGE->requires->js_call_amd('block_openai_chat/config_popup', 'init'); // TODO: is this the right way to load the js? --> Does NOT work for now
                $PAGE->requires->js_call_amd('block_openai_chat/settings', 'init');
                // TODO: maybe there is a better way, but for now, add the script like this:
                // Inject inline JavaScript directly after the button.
                $script = '<script>
                    console.log("Adding custom JavaScript for button functionality.");
                    debugger;
                    const addActivityButton = document.getElementsByName("config_add_activity_button")[0]; // TODO: this works, but id would be better, but id is created dynamically
                    const activityDropdown = document.getElementsByName("config_activity_dropdown")[0];
                    const userMessageTextarea = document.getElementsByName("config_user_message")[0];

                    if (addActivityButton && activityDropdown && userMessageTextarea) {
                      addActivityButton.addEventListener("click", () => {
                        debugger;
                        const selectedActivity = activityDropdown.value;
                        const placeholder = `Result of coursemodule ${selectedActivity} is: {grade:${selectedActivity}}`;
                        userMessageTextarea.value += (userMessageTextarea.value ? "\n" : "") + placeholder;
                      });
                    }
                </script>';
                $mform->addElement('html', $script);

                $mform->addElement('textarea', 'config_user_message', 'user_message');
                $mform->setDefault('config_user_message', '');
                $mform->setType('config_user_message', PARAM_TEXT);
                $mform->addHelpButton('config_user_message', 'config_user_message', 'block_exaaichat');

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
