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
 * Base completion object class
 *
 * @package    block_exaaichat
 * @copyright  2025 GTN Solutions https://gtn-solutions.com
 * @copyright  based on work by Limekiller https://github.com/Limekiller/moodle-block_openai_chat
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_exaaichat\completion;

use block_exaaichat\helper;
use block_exaaichat\locallib;
use block_exaaichat\logger;

defined('MOODLE_INTERNAL') || die;

abstract class completion_base {

    protected string $apikey = '';

    protected string $assistantname = '';
    protected string $username = '';

    // protected string $prompt = '';
    protected string $sourceoftruth;
    protected string $model;
    protected float $temperature;
    protected $maxlength;
    protected float $topp;
    protected $frequency;
    protected $presence;

    protected string $assistant;
    protected string $instructions = '';
    protected string $endpoint = '';
    protected array $vector_store_ids = [];

    /**
     * Initialize all the class properties that we'll need regardless of model
     * @param object $config the block config
     * @param string $message The most recent message sent by the user
     * @param string $thread_id conversion thread_id
     * @param array $history An array of objects containing the history of the conversation (eg. for stateless Chat API)
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function __construct(object $config, protected string $message, protected string $thread_id = '', protected array $history = [], protected string $page_content = '') {
        $config = clone $config;

        if (!get_config('block_exaaichat', 'allowinstancesettings')) {
            $global_configs_to_use = [
                'api_type',
                'apikey',

                // 'instructions',
                // 'sourceoftruth',

                // 'persistconvo',
                'assistantname',
                'model',
                'endpoint',
                'temperature',
                'topp',
                'vector_store_ids',
                // 'prompt',
                'maxlength',
                'frequency',
                'presence',
            ];

            foreach ($global_configs_to_use as $cfgname) {
                if (!in_array($cfgname, $config->settings_to_keep ?? [])) {
                    unset($config->{$cfgname});
                }
            }
        }

        $default_api_type = locallib::get_api_type();
        $current_api_type = preg_replace('!^.*\\\\!', '', static::class);
        $this->apikey = $config->apikey ?? '';
        if (($config->is_moodle_ai_provider ?? false) || ($config->endpoint ?? '') || ($default_api_type != $current_api_type)) {
            // custom endpoint, don't use default apikey!
        } else {
            $this->apikey = $this->apikey ?: $this->get_plugin_setting('apikey', '');
        }

        $this->instructions = trim($config->instructions ?? '') ?: $this->get_plugin_setting('instructions', '');
        $this->sourceoftruth = trim($config->sourceoftruth ?? '') ?: $this->get_plugin_setting('sourceoftruth', '');

        // $this->persistconvo = $config->persistconvo ?? $this->get_plugin_setting( 'persistconvo', 0);
        $this->username = $config->username ?? '' ?: $this->get_plugin_setting('username', get_string('defaultusername', 'block_exaaichat'));
        $this->assistantname = $config->assistantname ?? '' ?: $this->get_plugin_setting('assistantname', get_string('defaultassistantname', 'block_exaaichat'));;

        // falls model "other" gewählt wurde, dann den Wert aus dem Eingabefeld model_other verwenden
        if (($config->model ?? '') === 'other') {
            $config->model = $config->model_other ?? '';
        }
        $this->model = $config->model ?? '' ?: locallib::get_default_model();

        $this->endpoint = $config->endpoint ?? '';
        $this->temperature = $config->temperature ?? $this->get_plugin_setting('temperature', 0.5);;
        $this->topp = $config->topp ?? $this->get_plugin_setting('topp', 1);

        $vector_store_ids = trim($config->vector_store_ids ?? '');
        if ($vector_store_ids) {
            $this->vector_store_ids = preg_split('![\s,]+!', $vector_store_ids);
        }

        // $this->prompt = $config->prompt ?? $this->get_plugin_setting('prompt', get_string('defaultprompt', 'block_exaaichat'));

        $this->maxlength = $config->maxlength ?? $this->get_plugin_setting('maxlength', 500);
        $this->frequency = $config->frequency ?? $this->get_plugin_setting('frequency', 1);
        $this->presence = $config->presence ?? $this->get_plugin_setting('presence', 1);

        $this->init($config);
    }

    protected function init(object $config) {
        // can be overridden by child classes
    }

    public static function create_from_config(object $config, string $message, string $thread_id = '', array $history = [], string $page_content = ''): static {
        if (get_config('block_exaaichat', 'allowinstancesettings')) {
            $api_type = $config->api_type ?? '';
        } else {
            $api_type = '';
        }
        $api_type = $api_type ?: locallib::get_api_type();
        $engine_class = "\block_exaaichat\completion\\{$api_type}";

        return new $engine_class($config, $message, $thread_id, $history, $page_content);
    }

    public static function create_from_type(string $api_type): ?static {
        $engine_class = "\block_exaaichat\completion\\{$api_type}";

        if (!class_exists($engine_class)) {
            return null;
        }

        return new $engine_class((object)[], '');
    }

    public abstract function create_completion(): array;

    /**
     * Attempt to get the saved value for a setting; if this isn't set, return a passed default instead
     * @param string $settingname The name of the setting to fetch
     * @param mixed $default_value default_value: The default value to return if the setting isn't already set
     * @return mixed The saved or default value
     */
    protected function get_plugin_setting(string $settingname, mixed $default_value = null): mixed {
        $setting = get_config('block_exaaichat', $settingname);
        if (!$setting && $setting != 0) {
            $setting = $default_value;
        }
        return $setting;
    }

    protected function get_sourceoftruth(): string {
        // TOOD: die logik hier überdenken
        /*
        $sourceoftruth = get_config('block_exaaichat', 'sourceoftruth');

        if ($sourceoftruth || $this->sourceoftruth) {
            $sourceoftruth =
                get_string('sourceoftruthpreamble', 'block_exaaichat')
                . $sourceoftruth . "\n\n"
                . $this->sourceoftruth . "\n\n";
        } else {
            $sourceoftruth = '';
        }

        $sourceoftruth = trim($sourceoftruth); //  . ' ' . $this->prompt);
        */
        $sourceoftruth = helper::generate_placeholders($this->sourceoftruth);
        if ($sourceoftruth) {
            $sourceoftruth = get_string('sourceoftruthpreamble', 'block_exaaichat') . $sourceoftruth;
        }

        return $sourceoftruth;
    }

    protected function get_instructions(): string {
        return $this->instructions;
    }

    /**
     *
     * add debug output to the logger
     * @param mixed ...$args
     * @return void
     */
    protected function debug(...$args): void {
        logger::debug_grouped($this->thread_id ?: 'new', ...$args);
    }

    /**
     * add error output to the logger and throw an exception
     * @param mixed ...$args
     * @throws \moodle_exception
     */
    protected function throw(...$args): void {
        $this->debug($args);
        throw new \moodle_exception(json_encode($args));
    }

    public function get_models(): array {
        return [
            'gpt-5' => 'gpt-5',
            'gpt-5-mini' => 'gpt-5-mini',
            'gpt-5-nano' => 'gpt-5-nano',
            'gpt-4.1-mini' => 'gpt-4.1-mini',
            'gpt-4o' => 'gpt-4o',
            'gpt-4o-2024-11-20' => 'gpt-4o-2024-11-20',
            'gpt-4o-2024-08-06' => 'gpt-4o-2024-08-06',
            'gpt-4o-2024-05-13' => 'gpt-4o-2024-05-13',
            'gpt-4o-mini-2024-07-18' => 'gpt-4o-mini-2024-07-18',
            'gpt-4o-mini' => 'gpt-4o-mini',
            'gpt-4-turbo-preview' => 'gpt-4-turbo-preview',
            'gpt-4-turbo-2024-04-09' => 'gpt-4-turbo-2024-04-09',
            'gpt-4-turbo' => 'gpt-4-turbo',
            'gpt-4-32k-0314' => 'gpt-4-32k-0314',
            'gpt-4-1106-preview' => 'gpt-4-1106-preview',
            'gpt-4-0613' => 'gpt-4-0613',
            'gpt-4-0314' => 'gpt-4-0314',
            'gpt-4-0125-preview' => 'gpt-4-0125-preview',
            'gpt-4' => 'gpt-4',
            'gpt-3.5-turbo-16k-0613' => 'gpt-3.5-turbo-16k-0613',
            'gpt-3.5-turbo-16k' => 'gpt-3.5-turbo-16k',
            'gpt-3.5-turbo-1106' => 'gpt-3.5-turbo-1106',
            'gpt-3.5-turbo-0125' => 'gpt-3.5-turbo-0125',
            'gpt-3.5-turbo' => 'gpt-3.5-turbo',
        ];
    }

    protected function curl_pre_check(string $endpoint): ?array {
        $curl = new \curl();
        $security = $curl->get_security();

        // check if request will be blocked before the actual post request
        if ($security->url_is_blocked($endpoint)) {
            // Parse URL to get host and port for detailed error message.
            $url = new \moodle_url($endpoint);
            $host = $url->get_host();
            $port = $url->get_port();
            $scheme = $url->get_scheme();
            if (!$port) {
                $port = ($scheme === 'https') ? 443 : 80;
            }

            // Build detailed error message by checking each setting using reflection.
            $errors = [get_string('error:request_blocked', 'block_exaaichat')];

            // Use reflection to call protected methods.
            $reflection = new \ReflectionClass($security);

            // Check if host is blocked.
            $hostMethod = $reflection->getMethod('host_is_blocked');
            $hostMethod->setAccessible(true);
            if ($hostMethod->invoke($security, $host)) {
                $errors[] = get_string('error:host_blocked', 'block_exaaichat', (object)['host' => $host]);
            }

            // Check if port is blocked.
            $portMethod = $reflection->getMethod('port_is_blocked');
            $portMethod->setAccessible(true);
            if ($portMethod->invoke($security, $port)) {
                $errors[] = get_string('error:port_not_allowed', 'block_exaaichat', (object)['port' => $port]);
            }

            return [
                'id' => 'error',
                "error" => implode(' ', $errors),
            ];
        }

        return null;
    }
}
