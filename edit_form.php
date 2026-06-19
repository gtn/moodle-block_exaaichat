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

use block_exaaichat\completion\completion_base;
use block_exaaichat\helper;
use block_exaaichat\locallib;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/blocks/exaaichat/lib.php');

class block_exaaichat_edit_form extends block_edit_form {
    private function get_placeholders_gradebook(): array {
        $gradedata = helper::get_student_grades_for_course_flattened();
        $placeholders = [];

        foreach ($gradedata as $key => $gradeitem) {
            if ($key == array_key_last($gradedata)) {
                // letzter Eintrag ist die Kursgesamtbewertung, diese wird weiter unten separat behandelt
                continue;
            }

            $fullname = $gradeitem->mod_name ? "{$gradeitem->mod_name} \"{$gradeitem->name}\"" : $gradeitem->name;

            // schaut besser aus:
            // level 0 no spacer
            // level 1 spacer = 3
            // level 2 spacer = 8
            $spacer = $gradeitem->level > 1 ? ltrim(str_repeat('&nbsp;', 3 + ($gradeitem->level - 2) * 5) . ' &bull; ') : '';

            if (!$gradeitem->has_grade) {
                // Skip grade items without a grade (e.g., category headers).
                $placeholders[] = [
                    'placeholder' => $gradeitem->name,
                    'label' => $gradeitem->name,
                    'disabled' => true,
                    'spacer' => $spacer,
                ];
                continue;
            }

            // Grade value placeholder.
            $placeholders[] = [
                'placeholder' => get_string('placeholders:grade:placeholder', 'block_exaaichat', ['name' => $fullname, 'placeholder' => "{grade:{$gradeitem->name}}"]),
                'label' => get_string('placeholders:grade:name', 'block_exaaichat', $fullname),
                'spacer' => $spacer,
            ];
            $placeholders[] = [
                'placeholder' => get_string('placeholders:range:placeholder', 'block_exaaichat', ['name' => $fullname, 'placeholder' => "{range:{$gradeitem->name}}"]),
                'label' => get_string('placeholders:range:name', 'block_exaaichat', $fullname),
                'spacer' => $spacer,
            ];
        }

        $placeholders += locallib::get_placeholders_gradebook_additional();

        return $placeholders;
    }

    protected function specific_definition($mform) {
        global $OUTPUT;

        // this does not work if the form is displayed on the config page
        // $block_id = $this->_ajaxformdata["blockid"];
        // solution:
        $block_id = $this->_get_block()->instance->id;

        $api_type = '';
        if (get_config('block_exaaichat', 'allowinstancesettings')) {
            // allow switching to different api
            $api_type = $this->_get_block()->config->api_type ?? ''; // is null on new block
        }
        if (!$api_type) {
            $api_type = \block_exaaichat\locallib::get_api_type();
        }
        $completion = completion_base::create_from_type($api_type);

        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('blocktitle', 'block_exaaichat'));
        $mform->setDefault('config_title', get_string('exaaichat', 'block_exaaichat'));
        $mform->setType('config_title', PARAM_TEXT);

        $mform->addElement('advcheckbox', 'config_showlabels', get_string('showlabels', 'block_exaaichat'));
        $mform->setDefault('config_showlabels', 1);

        if (get_config('block_exaaichat', 'allowinstancesettings')) {
            $mform->addElement('select', 'config_api_type', get_string('moodle_settings:api_type', 'block_exaaichat'), [
                '' => \block_exaaichat\locallib::get_api_type()
                    ? get_string('default', 'block_exaaichat', get_string('type_' . $api_type, 'block_exaaichat'))
                    : get_string('type_choose', 'block_exaaichat'),
            ] + \block_exaaichat\locallib::get_api_types());
            $mform->setDefault('config_api_type', '');
            $mform->setType('config_api_type', PARAM_TEXT);
        }

        // azure ist noch nicht für die Placeholder-Logik angepasst
        $mform->addElement('textarea', 'config_instructions', get_string('config_instructions', 'block_exaaichat'));
        $mform->setDefault('config_instructions', '');
        $mform->setType('config_instructions', PARAM_TEXT);
        $mform->addHelpButton('config_instructions', 'config_instructions', 'block_exaaichat');

        if (get_config('block_exaaichat', 'allowinstancesettings')) {
            $mform->addElement('text', 'config_assistantname', get_string('assistantname', 'block_exaaichat'));
            $mform->setDefault('config_assistantname', '');
            $mform->setType('config_assistantname', PARAM_TEXT);
            $mform->addHelpButton('config_assistantname', 'config_assistantname', 'block_exaaichat');
        }

        $models = [];
        if (locallib::get_default_model() && locallib::get_api_type() == $api_type) {
            $models += ['' => get_string('default', 'block_exaaichat', locallib::get_default_model())];
        }
        $models += $completion?->get_models() ?? [];
        $models += ['other' => get_string('block_instance:config:model:choose-other', 'block_exaaichat')];

        // Primary provider: key / model / endpoint, placed right below the assistant name. The
        // "Advanced" parameter group is rendered further down, below the additional providers.
        if (get_config('block_exaaichat', 'allowinstancesettings')) {
            if ($api_type === 'assistant') {
                $mform->addElement('select', 'config_assistant', get_string('assistant', 'block_exaaichat'), block_exaaichat_fetch_assistants_array($block_id));
                $mform->setDefault('config_assistant', get_config('block_exaaichat', 'assistant'));
                $mform->setType('config_assistant', PARAM_TEXT);
                $mform->addHelpButton('config_assistant', 'config_assistant', 'block_exaaichat');

                $mform->addElement('advcheckbox', 'config_persistconvo', get_string('persistconvo', 'block_exaaichat'));
                $mform->addHelpButton('config_persistconvo', 'config_persistconvo', 'block_exaaichat');
                $mform->setDefault('config_persistconvo', 1);
            } else {
                $mform->addElement('select', 'config_model', get_string('model', 'block_exaaichat'), $models);
                $mform->setDefault('config_model', '');
                $mform->setType('config_model', PARAM_TEXT);
                $mform->addHelpButton('config_model', 'config_model', 'block_exaaichat');

                $mform->addElement('text', 'config_model_other', get_string('block_instance:config:model_other', 'block_exaaichat'));
                $mform->setDefault('config_model_other', '');
                $mform->setType('config_model_other', PARAM_TEXT);
                $mform->addHelpButton('config_model_other', 'block_instance:config:model_other', 'block_exaaichat');
                $mform->hideIf('config_model_other', 'config_model', 'neq', 'other');
            }

            $mform->addElement('text', 'config_apikey', get_string('apikey', 'block_exaaichat'));
            $mform->setDefault('config_apikey', '');
            $mform->setType('config_apikey', PARAM_TEXT);
            $mform->addHelpButton('config_apikey', 'config_apikey', 'block_exaaichat');

            if ($api_type !== 'assistant') {
                $mform->addElement('text', 'config_endpoint', get_string('block_instance:config:endpoint', 'block_exaaichat'));
                $mform->setDefault('config_endpoint', '');
                $mform->setType('config_endpoint', PARAM_URL);
            }
        }

        // Knowledge base for the AI (source of truth) + its placeholder dropdown, placed just above
        // the documents upload.
        $el = $mform->addElement('textarea', 'config_sourceoftruth', get_string('sourceoftruth', 'block_exaaichat'));
        $el->updateAttributes(['rows' => 10]);
        $mform->setDefault('config_sourceoftruth', '');
        $mform->setType('config_sourceoftruth', PARAM_TEXT);
        $mform->addHelpButton('config_sourceoftruth', 'config_sourceoftruth', 'block_exaaichat');

        // Dropdown menu for placeholders
        $mform->addElement('static', 'user_message_options', '', $OUTPUT->render_from_template('block_exaaichat/config_source_of_truth', [
            'placeholders' => locallib::get_placeholders(),
            'placeholders_gradebook' => $this->get_placeholders_gradebook(),
        ]));

        // File upload: synced to a managed OpenAI vector store (file_search). Shown whenever the admin
        // enabled the documents feature; the note flags that it only works with the OpenAI Responses API.
        if (get_config('block_exaaichat', 'enablefileupload')) {
            $mform->addElement('static', 'config_documents_note', get_string('documents', 'block_exaaichat'), get_string('documents:responsesonly', 'block_exaaichat'));
            $mform->addElement('filemanager', 'config_documents', '', null, [
                'subdirs' => 0,
                'maxfiles' => 50,
                'accepted_types' => ['.pdf', '.txt', '.md', '.docx', '.pptx', '.html', '.json', '.csv'],
            ]);
            $mform->addHelpButton('config_documents', 'documents', 'block_exaaichat');
        }

        // Additional providers: a teacher-configured list of full providers (each with its own
        // api_type / apikey / model / endpoint / instruction) the students can pick from at runtime,
        // on top of the primary provider configured above.
        if (get_config('block_exaaichat', 'allowinstancesettings')) {
            $mform->addElement('header', 'config_providers_header', get_string('additionalproviders', 'block_exaaichat'));
            $mform->setExpanded('config_providers_header', true);

            $provider_api_types = ['' => get_string('type_choose', 'block_exaaichat')] + locallib::get_api_types();

            // {no} is replaced by repeat_elements with the 1-based row number, giving each provider a
            // numbered divider that visually separates the rows.
            $repeatarray = [
                $mform->createElement('static', 'config_provider_divider', '<strong>' . get_string('additionalproviders:provider', 'block_exaaichat', '{no}') . '</strong>', '<hr class="mt-0">'),
                $mform->createElement('text', 'config_provider_label', get_string('additionalproviders:label', 'block_exaaichat')),
                $mform->createElement('select', 'config_provider_api_type', get_string('moodle_settings:api_type', 'block_exaaichat'), $provider_api_types),
                $mform->createElement('text', 'config_provider_model', get_string('model', 'block_exaaichat')),
                $mform->createElement('text', 'config_provider_apikey', get_string('apikey', 'block_exaaichat')),
                $mform->createElement('text', 'config_provider_endpoint', get_string('block_instance:config:endpoint', 'block_exaaichat')),
                $mform->createElement('textarea', 'config_provider_instructions', get_string('additionalproviders:instructions', 'block_exaaichat')),
                $mform->createElement('submit', 'config_provider_delete', get_string('delete'), [], false),
            ];

            $repeatoptions = [
                'config_provider_label' => ['type' => PARAM_TEXT],
                'config_provider_api_type' => ['type' => PARAM_TEXT],
                'config_provider_apikey' => ['type' => PARAM_TEXT],
                'config_provider_model' => ['type' => PARAM_TEXT],
                'config_provider_endpoint' => ['type' => PARAM_URL],
                'config_provider_instructions' => ['type' => PARAM_TEXT],
            ];

            $existing_providers = $this->_get_block()->config?->providers ?? [];
            $this->repeat_elements($repeatarray, count($existing_providers), $repeatoptions, 'config_provider_repeats',
                'config_provider_add', 1, get_string('additionalproviders:add', 'block_exaaichat'), true, 'config_provider_delete');
        }

        // Advanced parameters, moved below the additional providers. Assistant has no advanced params.
        if (get_config('block_exaaichat', 'allowinstancesettings') && $api_type !== 'assistant') {
            $mform->addElement('header', 'config_adv_header', get_string('advanced', 'block_exaaichat'));
            $mform->setExpanded('config_adv_header', true);

            $mform->addElement('text', 'config_temperature', get_string('temperature', 'block_exaaichat'));
            $mform->setDefault('config_temperature', 0.5);
            $mform->setType('config_temperature', PARAM_FLOAT);
            $mform->addHelpButton('config_temperature', 'config_temperature', 'block_exaaichat');

            if ($api_type === 'responses') {
                $mform->addElement('text', 'config_topp', get_string('topp', 'block_exaaichat'));
                $mform->setDefault('config_topp', 1);
                $mform->setType('config_topp', PARAM_FLOAT);
                $mform->addHelpButton('config_topp', 'config_topp', 'block_exaaichat');

                $mform->addElement('text', 'config_vector_store_ids', get_string('vectorstoreids', 'block_exaaichat'));
                $mform->setDefault('config_vector_store_ids', '');
                $mform->setType('config_vector_store_ids', PARAM_TEXT);
            } else {
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

        // weil das Formular per Ajax geladen wird, geht es nur über diesen Hack, dass das JS initialisiert wird
        $mform->addElement('html', "
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

    block_exaaichat_require(['block_exaaichat/block_instance_config'], function (m) {
        m.init();
    });
</script>
        ");
    }

    public function set_data($defaults) {
        // Load the existing uploaded documents into the filemanager draft area.
        if (get_config('block_exaaichat', 'enablefileupload')) {
            $context = \context_block::instance($this->_get_block()->instance->id);
            $draftid = file_get_submitted_draft_itemid('config_documents');
            file_prepare_draft_area($draftid, $context->id, 'block_exaaichat', 'documents', 0, [
                'subdirs' => 0,
                'maxfiles' => 50,
            ]);
            $defaults->config_documents = $draftid;
        }

        // Unpack the saved providers list into the repeated form fields so they are shown again.
        // Read from the block config directly: parent::set_data() only copies config into config_*
        // fields later (in prepare_defaults), so $defaults does not hold it yet here.
        $saved_providers = $this->_get_block()->config?->providers ?? [];
        if (get_config('block_exaaichat', 'allowinstancesettings') && $saved_providers) {
            foreach ($saved_providers as $i => $provider) {
                $defaults->config_provider_label[$i] = $provider->label ?? '';
                $defaults->config_provider_api_type[$i] = $provider->api_type ?? '';
                $defaults->config_provider_apikey[$i] = $provider->apikey ?? '';
                $defaults->config_provider_model[$i] = $provider->model ?? '';
                $defaults->config_provider_endpoint[$i] = $provider->endpoint ?? '';
                $defaults->config_provider_instructions[$i] = $provider->instructions ?? '';
            }
        }

        parent::set_data($defaults);
    }

    /**
     * Get the block instance
     * @return block_exaaichat
     */
    private function _get_block(): \block_exaaichat {
        if (method_exists($this, 'get_block')) {
            // moodle 4.5 onwards
            return $this->get_block();
        } else {
            // moodle 4.1
            return $this->block;
        }
    }
}
