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
 * @copyright  based on work by Limekiller https://github.com/Limekiller/moodle-block_openai_chat
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_exaaichat;

use core_ai\aiactions\generate_text;

defined('MOODLE_INTERNAL') || die;

class locallib {
    /**
     * Fetch the current API type from the database, defaulting to "chat"
     * @return String: the API type (chat|azure|assistant)
     */
    public static function get_api_type() {
        $stored_type = get_config('block_exaaichat', 'api_type');
        if ($stored_type) {
            return $stored_type;
        }

        return 'chat';
    }

    public static function get_default_model(): string {
        $model = get_config('block_exaaichat', 'model');
        if ($model == 'other') {
            $model = get_config('block_exaaichat', 'model_other');
        }
        if (!$model) {
            $model = 'chat';
        }

        return $model;
    }

    public static function get_openai_api_url(): string {
        return get_config('block_exaaichat', 'openai_api_url') ?: 'https://api.openai.com/v1';
    }

    /**
     * Return a list of available models
     * @return array The list of model info
     */
    public static function get_models(): array {
        $configmodels = trim(get_config('block_exaaichat', 'models'));
        if (!$configmodels) {
            return static::get_default_models();
        }

        $configmodels = array_filter(array_map('trim', explode("\n", $configmodels)));
        return array_combine($configmodels, $configmodels);
    }

    public static function clean_log(): void {
        global $DB;

        if (!$days = get_config('block_exaaichat', 'logging_retention_period')) {
            return;
        }

        $DB->execute('DELETE FROM {block_exaaichat_log} WHERE timecreated < ?', [time() - ($days * 24 * 60 * 60)]);
    }

    public static function get_moodle_ai_providers(): array {
        global $CFG;

        $ais = [];

        if (version_compare($CFG->release, '5.0', '>=')) {
            // TODO: works with moodle 5.1, but also with 5.0?
            $providers = \core\di::get(\core_ai\manager::class)->get_sorted_providers();

            foreach ($providers as $provider) {
                if (!$provider->enabled) {
                    continue;
                }

                $actionconfig = $provider->actionconfig['core_ai\aiactions\generate_text'] ?? null;
                if (!$actionconfig || !$actionconfig['enabled']) {
                    continue;
                }

                $ais[] = (object)[
                    'id' => $provider->id,
                    'name' => $provider->name,
                    'apikey' => ($provider->config['apikey'] ?? '') ?: ($provider->config['password'] /* for ollama */ ?? ''),
                    'api_type' => \aiprovider_ollama\provider::class ? 'ollama' :
                        (\aiprovider_deepseek\provider::class ? 'deepseek' : 'chat'),
                    // globalratelimit
                    // userratelimit
                    'model' => $actionconfig['settings']['model'] ?? '',
                    'endpoint' => $actionconfig['settings']['endpoint'] ?? '',
                    'modelsettings' => $actionconfig['modelsettings'][$actionconfig['model'] ?? ''] ?? [],
                ];
            }

            return $ais;
        } elseif (version_compare($CFG->release, '4.5', '>=')) {
            $providers = current(\core_ai\manager::get_providers_for_actions([generate_text::class], true));
            $action = new generate_text(1, 1, '');
            foreach ($providers as $provider) {
                /* @var \core_ai\provider $provider */
                $classname = 'process_' . $action->get_basename();
                $classpath = substr($provider::class, 0, strpos($provider::class, '\\') + 1);
                $processclass = $classpath . $classname;
                $processor = new $processclass($provider, $action);

                $call_private_method = function($instance, $methodName) {
                    $method = new \ReflectionMethod($instance, $methodName);
                    $method->setAccessible(true); // allow access
                    return $method->invoke($instance);
                };

                $get_private_property = function($instance, $propertyName) {
                    $property = new \ReflectionProperty($instance, $propertyName);
                    $property->setAccessible(true); // allow access
                    return $property->getValue($instance);
                };

                $model = $call_private_method($processor, 'get_model');
                $endpoint = $call_private_method($processor, 'get_endpoint');

                if (!$model && preg_match('!^https://generativelanguage.googleapis.com/v1beta/models/([^/:]+)!', $endpoint, $matches)) {
                    // For Gemini model is inside the url
                    $model = $matches[1];
                }

                $ais[] = (object)[
                    'id' => 'provider-' . $provider->get_name(),
                    'name' => $model ?: ucfirst(str_replace('aiprovider_', '', $provider->get_name())),
                    'apikey' => $get_private_property($provider, 'apikey'),
                    'api_type' => 'chat',
                    // globalratelimit
                    // userratelimit
                    'model' => $model,
                    'endpoint' => (string)$endpoint,
                    'modelsettings' => [],
                ];
            }
        }

        return $ais;
    }

    public static function get_placeholders(): array {
        $placeholders = [];
        $placeholders[] = [
            'placeholder' => get_string('placeholders:user.fullname:placeholder', 'block_exaaichat', '{user.fullname}'),
            'label' => get_string('placeholders:user.fullname:name', 'block_exaaichat'),
        ];
        $placeholders[] = [
            'placeholder' => get_string('placeholders:userdate:placeholder', 'block_exaaichat', '{userdate}'),
            'label' => get_string('placeholders:userdate:name', 'block_exaaichat'),
        ];

        return $placeholders;
    }

    public static function get_placeholders_gradebook_additional(): array {
        $placeholders = [];

        // New syntax examples for course total.
        $placeholders[] = [
            'placeholder' => get_string('placeholders:grade:coursetotal:placeholder', 'block_exaaichat', '{grade:coursetotal}'),
            'label' => get_string('placeholders:grade:coursetotal:name', 'block_exaaichat'),
        ];
        $placeholders[] = [
            'placeholder' => get_string('placeholders:range:coursetotal:placeholder', 'block_exaaichat', '{range:coursetotal}'),
            'label' => get_string('placeholders:range:coursetotal:name', 'block_exaaichat'),
        ];

        return $placeholders;
    }
}
