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
 * Class providing completions for Azure
 *
 * @package    block_exaaichat
 * @copyright  2025 GTN Solutions https://gtn-solutions.com
 * @copyright  based on work by Limekiller https://github.com/Limekiller/moodle-block_openai_chat
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_exaaichat\completion;

defined('MOODLE_INTERNAL') || die;

class azure extends chat {

    private $resourcename;
    private $deploymentid;
    private $apiversion;

    protected function init(object $config) {
        $this->resourcename = $this->get_plugin_setting('resourcename');
        $this->deploymentid = $this->get_plugin_setting('deploymentid');
        $this->apiversion = $this->get_plugin_setting('apiversion');
    }

    protected function get_default_endpoint(): string {
        return "https://" . $this->resourcename . ".openai.azure.com/openai/deployments/" . $this->deploymentid . "/chat/completions?api-version=" . $this->apiversion;
    }

    protected function get_api_headers(): array {
        return [
            'api-key: ' . $this->apikey,
            'Content-Type: application/json',
        ];
    }

    protected function is_gpt5_or_newer(string $model): bool {
        // On Azure the deployment determines the actual model, so also check the deployment id.
        return parent::is_gpt5_or_newer($model) || parent::is_gpt5_or_newer((string)$this->deploymentid);
    }
}
