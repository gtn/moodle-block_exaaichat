// This file is part of Moodle - http://moodle.org/ //
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
 * Javascript events for the `core_block` subsystem.
 *
 * @module     core_block/events
 * @copyright  2021 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      4.0
 *
 * @example <caption>Example of listening to a block event.</caption>
 * import {eventTypes as blockEventTypes} from 'core_block/events';
 *
 * document.addEventListener(blockEventTypes.blockContentUpdated, e => {
 *     window.console.log(e.target); // The HTMLElement relating to the block whose content was updated.
 *     window.console.log(e.detail.instanceId); // The instanceId of the block that was updated.
 * });
 */




/**
 * init function, which is called when the instance form is displayed
 */
export function init() {
  const addActivityButton = document.getElementById("config_add_placeholder_button");
  const activityDropdown = document.getElementById("config_placeholder_dropdown");
  // Info: using the name of the field, because the element id iscreated dynamically by moodle
  const messageTextarea = document.getElementsByName("config_sourceoftruth")[0];

  if (!addActivityButton || !activityDropdown || !messageTextarea) {
    return;
  }

  // enable button (was disabled in form config by default)
  addActivityButton.disabled = false;
  addActivityButton.addEventListener('click', async () => {
    messageTextarea.value += (messageTextarea.value ? "\n" : "") + activityDropdown.value;

    // scroll to bottom of the textarea
    messageTextarea.scrollTo({
      top: messageTextarea.scrollHeight,
      behavior: 'smooth'
    });
  });
}
