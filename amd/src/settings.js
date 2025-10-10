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
 * Functions for the block settings page
 *
 * @module     block_exaaichat
 * @copyright  2025 GTN Solutions https://gtn-solutions.com
 * @copyright  based on work by Limekiller https://github.com/Limekiller/moodle-block_openai_chat
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export const init = () => {
  document.querySelector('#id_s_block_exaaichat_type')?.addEventListener('change', () => {
    // If the API Type is changed, programmatically hit save so the page automatically reloads with the new options
    document.querySelector('.settingsform').classList.add('block_exaaichat');
    document.querySelector('.settingsform').classList.add('disabled');
    document.querySelector('.settingsform button[type="submit"]').click();
  });
};
