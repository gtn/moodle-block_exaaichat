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
 * Terms of Service dialog functionality
 *
 * @module     block_exaaichat/tos
 * @copyright  2025 GTN Solutions https://gtn-solutions.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import {get_string} from 'core/str';
import {call as ajaxCall} from 'core/ajax';

/**
 * Initializes the TOS dialog
 */
export async function init() {
  const wrapper = document.querySelector('.exaaichat_tos_wrapper');
  const openBtn = wrapper.querySelector('.exaaichat_tos_open_btn');
  const tosContent = wrapper.querySelector('.exaaichat_tos_content').innerHTML;

  const title = await get_string('terms_of_service:title', 'block_exaaichat');
  const acceptLabel = await get_string('terms_of_service:accept', 'block_exaaichat');

  openBtn.addEventListener('click', async function () {
    const modal = await ModalFactory.create({
      type: ModalFactory.types.SAVE_CANCEL,
      title: title,
      body: tosContent,
      buttons: {
        save: acceptLabel,
      },
      large: true,
    });

    // Add icon to save button
    modal.getRoot().find('[data-action="save"]').prepend('<i class="fa fa-check"></i> ');

    modal.getRoot().on(ModalEvents.save, function () {
      modal.getRoot().find('[data-action="save"]').prop('disabled', true);

      ajaxCall([{
        methodname: 'block_exaaichat_accept_tos',
        args: {},
      }])[0]
        .then(data => {
          if (data.success) {
            window.location.reload();
          }
        })
        .catch(() => {
          modal.getRoot().find('[data-action="save"]').prop('disabled', false);
        });

      return false;
    });

    modal.show();
  });
}
