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

defined('MOODLE_INTERNAL') || die;

class locallib {
    /**
     * Fetch the current API type from the database, defaulting to "chat"
     * @return String: the API type (chat|azure|assistant)
     */
    public static function get_api_type() {
        $stored_type = get_config('block_exaaichat', 'type');
        if ($stored_type) {
            return $stored_type;
        }

        return 'chat';
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

    public static function get_default_models(): array {
        return [
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
}
