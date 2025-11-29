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
 * Functions for the chat interface
 *
 * @module     block_exaaichat
 * @copyright  2025 GTN Solutions https://gtn-solutions.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {get_string, get_strings, getString as moodleGetString, getStrings as moodleGetStrings} from 'core/str';

/**
 * Convert a jQuery-deferred result into a real ES6 Promise.
 * @param {Object} deferred The jQuery-deferred object
 */
function toNativePromise(deferred) {
  return new Promise((resolve, reject) => {
    deferred.then(resolve).fail(reject);
  });
}

/**
 * Promise-friendly single string loader.
 * @param {Array} request Array of string requests
 */
export async function getStrings(request) {
  if (moodleGetStrings) {
    // moodle 4.3 and newer
    return moodleGetStrings(request);
  } else {
    // moodle 4.2 and older
    return toNativePromise(get_strings(request));
  }
}

/**
 * Promise-friendly single string loader.
 * @param {string} key The language string key
 * @param {string} [component='core'] The language string component
 * @param {object|string} [param] The param for variable expansion in the string.
 * @param {string} [lang] The users language - if not passed it is deduced.
 * @return {Promise<string>} A native Promise containing the translated string
 */
export async function getString(key, component, param, lang) {
  if (moodleGetString) {
    // moodle 4.3 and newer
    return moodleGetString(key, component, param, lang);
  } else {
    // moodle 4.2 and older
    return toNativePromise(get_string(key, component, param, lang));
  }
}
