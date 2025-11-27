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
 * Functions for the moodle settings page
 *
 * @module     block_exaaichat
 * @copyright  2025 GTN Solutions https://gtn-solutions.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import {getString} from 'core/str';

/**
 * init function, which is called when the instance form is displayed
 */
export function init() {
  const $select = $('select[name$="block_exaaichat_api_type"]');
  let $form = $select.closest('form');
  const oldValue = $select.val();

  $select.on('change', async function () {
    if (!confirm(await getString('moodle_settings:api_type:change', 'block_exaaichat'))) {
      // Revert to old value
      $select.val(oldValue);
    } else {
      // submit the form (click the submit button, so all moodle form handlers are called)
      let submitbutton = $form.find('button[type="submit"]');
      if (submitbutton.length) {
        // moodle formular
        submitbutton[0].click();
      }
    }
  });

  // placeholder für system message
  $form = $form || $('form#adminsettings');
  const $addPlaceholderBtn = $form.find("#config_add_placeholder_button");
  const $placeholderDropdown = $form.find("#config_placeholder_dropdown");
  // Info: using the name of the field, because the element id iscreated dynamically by moodle
  const $messageTextarea = $form.find('textarea[name$="block_exaaichat_sourceoftruth"]');

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
