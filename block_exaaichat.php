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
use block_exaaichat\output;

defined('MOODLE_INTERNAL') || die();

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

    public function applicable_formats() {
        return [
            'all' => true,
            'my' => (bool)get_config('block_exaaichat', 'allow_on_dashboard'),
        ];
    }

    public function specialization() {
        if (!empty($this->config->title)) {
            $this->title = $this->config->title;
        }
    }

    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = output::render_chat_interface($this->instance->id, $this->config ?? (object)[], true, false);

        return $this->content;
    }

    public function instance_config_save($data, $nolongerused = false) {
        // Normalize the repeated provider fields into a clean providers list. Skip empty rows
        // (no api_type and no model) and drop the raw repeat arrays so they don't pollute the config.
        if (is_array($data->provider_api_type ?? false)) {
            $providers = [];
            foreach ($data->provider_api_type as $i => $api_type) {
                $model = trim($data->provider_model[$i] ?? '');
                if (!$api_type && !$model) {
                    continue;
                }
                $providers[] = (object)[
                    'label' => trim($data->provider_label[$i] ?? ''),
                    'api_type' => $api_type,
                    'apikey' => trim($data->provider_apikey[$i] ?? ''),
                    'model' => $model,
                    'endpoint' => trim($data->provider_endpoint[$i] ?? ''),
                    'instructions' => trim($data->provider_instructions[$i] ?? ''),
                ];
            }
            $data->providers = array_values($providers);
            unset($data->provider_label, $data->provider_api_type, $data->provider_apikey, $data->provider_model, $data->provider_endpoint, $data->provider_instructions);
        }

        if (isset($data->documents) && get_config('block_exaaichat', 'enablefileupload')) {
            file_save_draft_area_files($data->documents, $this->context->id, 'block_exaaichat', 'documents', 0, [
                'subdirs' => 0,
                'maxfiles' => 50,
            ]);

            try {
                $data->managed_vector_store_id = \block_exaaichat\vector_store_sync::sync($this->context, $this->config ?? (object)[]);
            } catch (\Throwable $e) {
                \block_exaaichat\logger::debug('vector store sync failed:', $e->getMessage());
                \core\notification::error(get_string('documents:syncerror', 'block_exaaichat') . ' ' . $e->getMessage());
                // Keep the previously stored id so cleanup on block delete still works.
                $data->managed_vector_store_id = $this->config->managed_vector_store_id ?? '';
            }
        } elseif (($this->config->managed_vector_store_id ?? '') !== '') {
            // Documents field not present (other api type or feature disabled): preserve the existing
            // store id so cleanup on block delete still works.
            $data->managed_vector_store_id = $this->config->managed_vector_store_id;
        }

        parent::instance_config_save($data, $nolongerused);
    }

    public function instance_delete() {
        // Always remove the managed vector store + files from OpenAI, even when the documents feature
        // is currently disabled, so we never orphan a paid vector store.
        try {
            \block_exaaichat\vector_store_sync::cleanup($this->config ?? (object)[]);
        } catch (\Throwable $e) {
            \block_exaaichat\logger::debug('vector store cleanup failed:', $e->getMessage());
        }

        get_file_storage()->delete_area_files($this->context->id, 'block_exaaichat');

        return true;
    }
}
