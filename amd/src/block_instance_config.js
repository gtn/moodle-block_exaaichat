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
 * Functions for the block configuration popup
 *
 * @module     block_exaaichat
 * @copyright  2025 GTN Solutions https://gtn-solutions.com
 * @copyright  based on work by Limekiller https://github.com/Limekiller/moodle-block_openai_chat
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import {getString} from 'core/str';

/**
 * init function, which is called when the instance form is displayed
 */
export function init() {
  const $form = $('input[type="hidden"][value="exaaichat"]').closest('form');

  // api type select
  const $select = $form.find('select[name="config_api_type"]');
  const oldValue = $select.val();

  $select.on('change', async function () {
    if (!confirm(await getString('block_instance:config:api_type:change', 'block_exaaichat'))) {
      // Revert to old value
      $select.val(oldValue);
    } else {
      // submit the form (click the submit button, so all moodle form handlers are called)
      let submitbutton = $form.find('input[name="submitbutton"]');
      if (submitbutton.length) {
        // moodle formular
        submitbutton[0].click();
      } else {
        // im popup
        $form.closest('.modal-content').find(':button[data-action="save"]')[0].click();
      }
    }
  });

  // placeholder für system message
  const $addPlaceholderBtn = $form.find("#config_add_placeholder_button");
  const $placeholderDropdown = $form.find("#config_placeholder_dropdown");
  // Info: using the name of the field, because the element id iscreated dynamically by moodle
  const $messageTextarea = $form.find('textarea[name="config_sourceoftruth"]');

  if ($addPlaceholderBtn.length) {
    // enable button (was disabled in form config by default)
    $addPlaceholderBtn.prop('disabled', false);
    $addPlaceholderBtn.click(async () => {
      $messageTextarea.val($messageTextarea.val() + ($messageTextarea.val() ? "\n" : "") + $placeholderDropdown.val());

      // scroll to bottom of the textarea
      $messageTextarea[0].scrollTo({
        top: $messageTextarea[0].scrollHeight,
        behavior: 'smooth'
      });
    });
  }
}
