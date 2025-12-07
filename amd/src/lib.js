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
 * @copyright  based on work by Limekiller https://github.com/Limekiller/moodle-block_openai_chat
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* eslint-disable @babel/semi, no-undef */

import {getStrings} from './helper';
import LocalStorage from 'core/localstorage';
// import config from 'core/config';
import $ from 'jquery';

/**
 * Initializes the chat interface
 * @param {object} data
 */
export async function init(data) {
  const {
    blockId,
    api_type,
    persistConvo,
    userName,
    assistantName,
    showlabels,
    allow_access_to_current_page,
  } = data;

  const [questionString, errorString] = await getStrings([{
    key: 'askaquestion', component: 'block_exaaichat'
  }, {
    key: 'erroroccurred', component: 'block_exaaichat'
  }]);

  // OLD: if javascript caching is turned of, then jsrev=-1 and then the moodle localstorage won't work (will not save anything)
  // update: always use own localStorage logic, so the storage events can be used to sync between tabs
  const useMoodleLocalStorage = false; // config.jsrev > 0;

  const $form = $(`.block_exaaichat[data-instance-id='${blockId}']`);
  const $input = $form.find('.exaaichat_input');

  let historyTimestamp = 0;

  /**
   * Add a message to the chat UI
   * @param {string} type Which side of the UI the message should be on. Can be "user" or "assistant"
   * @param {string} message The text of the message to add
   * @param {int} blockId The ID of the block to manipulate
   */
  function addToChatLog(type, message, blockId) {
    let messageContainer = document.querySelector(`.block_exaaichat[data-instance-id='${blockId}'] .exaaichat_log`)
    messageContainer.classList.remove('hidden');

    const messageElem = document.createElement('div')
    messageElem.classList.add('chat_message')
    for (let className of type.split(' ')) {
      messageElem.classList.add(className)
    }

    let label = '';
    if (showlabels) {
      if (type == 'user') {
        label = userName;
      } else if (type == 'assistant') {
        label = assistantName;
      }
    }
    if (label) {
      const labelElement = document.createElement('div');
      labelElement.classList.add('chat_message_label');
      labelElement.innerHTML = label;
      messageElem.append(labelElement);
    }

    const messageText = document.createElement('div')
    messageText.classList.add('chat_message_content')
    messageText.innerHTML = message
    messageElem.append(messageText)

    messageContainer.append(messageElem);
    // if (messageText.offsetWidth) {
    //   messageElem.style.width = (messageText.offsetWidth + 40) + "px"
    // }
    messageContainer.scrollTop = messageContainer.scrollHeight
    messageContainer.closest('.block_exaaichat > div').scrollTop = messageContainer.scrollHeight
  }

  /**
   * Get chat data
   * @returns {object} The chat data object
   */
  function getAllChatsData() {
    let chatData = useMoodleLocalStorage ? LocalStorage.get("block_exaaichat_data") : localStorage.getItem("block_exaaichat_data");
    if (chatData) {
      chatData = JSON.parse(chatData);
    }

    return chatData || {};
  }

  /**
   * set chat data
   * @param {object} data
   */
  function setAllChatsData(data) {
    if (useMoodleLocalStorage) {
      LocalStorage.set("block_exaaichat_data", JSON.stringify(data));
    } else {
      localStorage.setItem("block_exaaichat_data", JSON.stringify(data));
    }
  }

  /**
   * Get chat data for a specific block
   * @returns {object}
   */
  function getBlockChatData() {
    let chatData = getAllChatsData();
    return chatData[blockId] || {};
  }

  /**
   * Set chat data for a specific block
   * @param {object} data
   */
  function setBlockChatData(data) {
    let chatData = getAllChatsData();
    if (chatData[blockId]) {
      data = {...chatData[blockId], ...data};
    }
    chatData[blockId] = data;
    setAllChatsData(chatData);
  }

  /**
   * clear the data for a specific block
   */

  /*
  function clearBlockChatData() {
    let chatData = getAllChatsData();
    delete chatData[blockId];
    setAllChatsData(chatData);
  }
  */

  /**
   * Add messages to the history for this block
   * @param {array} messages
   */
  function addHistory(messages) {
    let history = getBlockChatData().history || [];
    history = [...history, ...messages];
    historyTimestamp = Date.now();
    setBlockChatData({history, historyTimestamp});
  }

  /**
   * Clears the thread ID from local storage and removes the messages from the UI in order to refresh the chat
   */
  function clearHistory() {
    $form.find('.exaaichat_log')
      .html('')
      .addClass('hidden');
  }

  /**
   * Get the text content of the main region.
   * @return {String} The text content.
   */
  function getPageContent() {
    const mainRegion = document.querySelector('[role="main"]');
    if (!mainRegion) {
      return "";
    }

    // Clone the main region so we don't touch the real DOM
    const clone = mainRegion.cloneNode(true);

    // Remove all unwanted blocks
    clone.querySelectorAll('.block_exaaichat, #ai-features').forEach(el => el.remove());

    // Return text without that block
    return clone.innerText || clone.textContent;
  }

  /**
   * Makes an API request to get a completion from GPT-3, and adds it to the chat log
   * @param {string} message The text to get a completion for
   * @param {int} blockId The ID of the block this message is being sent from -- used to override settings if necessary
   * @param {string} api_type "assistant" | "chat" The type of API to use
   */
  const createCompletion = (message, blockId, api_type) => {
    let threadId = null

    // If the type is assistant, attempt to fetch a thread ID
    if (api_type === 'assistant' || api_type === 'responses') {
      let chatData = getBlockChatData();
      threadId = chatData['threadId'] || null
    }

    let history = [];
    if (api_type == 'assistant' || api_type === 'responses') {
      // they are stateful and don't need a history
    } else {
      history = getBlockChatData().history || [];
    }
    let providerId = $form.find('select[name="ai_provider"]').val();

    document.querySelector(`.block_exaaichat[data-instance-id='${blockId}'] .exaaichat_control_bar`).classList.add('disabled')
    document.querySelector(`.block_exaaichat[data-instance-id='${blockId}'] .exaaichat_input`).classList.remove('error')
    document.querySelector(`.block_exaaichat[data-instance-id='${blockId}'] .exaaichat_input`).placeholder = questionString
    document.querySelector(`.block_exaaichat[data-instance-id='${blockId}'] .exaaichat_input`).blur()
    addToChatLog('assistant loading', '...', blockId);

    fetch(`${M.cfg.wwwroot}/blocks/exaaichat/api/completion.php?sesskey=${M.cfg.sesskey}`, {
      method: 'POST', body: JSON.stringify({
        message, history, blockId, providerId, threadId,
        pageContent: allow_access_to_current_page ? getPageContent() : null,
      })
    })
      .then(async (response) => {
        let messageContainer = document.querySelector(`.block_exaaichat[data-instance-id='${blockId}'] .exaaichat_log`)
        messageContainer.removeChild(messageContainer.lastElementChild)
        $form.find('.exaaichat_control_bar').removeClass('disabled');

        if (!response.ok) {
          throw Error(response.statusText);
        } else {
          const responseText = await response.text();

          try {
            return JSON.parse(responseText);
          } catch {
            // Not JSON → try HTML error parsing
            const errorMessage = $(responseText).find('.errormessage').text()
              // debuggingmessage is maybe the current element or a subelement
              || $('<div>' + responseText + '</div>').find('.debuggingmessage').text()
              || 'Unknown return value from server';

            throw new Error(errorMessage);
          }
        }
      })
      .then(data => {
        if (data.error) {
          throw new Error(data.error);
        }

        addToChatLog('assistant', data.message, blockId)
        if (data.thread_id) {
          setBlockChatData({threadId: data.thread_id});
        }

        addHistory([
          {"type": "user", "message": message},
          {"type": "assistant", "message": data.message},
        ]);

        $input.focus();
      })
      .catch(error => {
        logError(error);
        addToChatLog('error', error.message, blockId)
        addHistory([
          {"type": "user", "message": message},
          {"type": "error", "message": error.message},
        ]);

        document.querySelector(`.block_exaaichat[data-instance-id='${blockId}'] .exaaichat_input`).classList.add('error')
        document.querySelector(`.block_exaaichat[data-instance-id='${blockId}'] .exaaichat_input`).placeholder = errorString
        $input.focus();
      })
  }

  /**
   * Reload chat history from local storage
   */
  function reloadChatHistory() {
    let chatData = getBlockChatData();
    historyTimestamp = chatData.historyTimestamp || 0;

    clearHistory();
    let history = chatData.history || [];
    for (let message of history) {
      addToChatLog(message.type, message.message, blockId)
    }
  }

  /**
   * Log an error to the console and potentially to an external service in the future
   * @param {Error} error The error object to log
   */
  function logError(error) {
    /* eslint-disable no-console */
    console.error(error);
  }

  // Initialize local data storage if necessary
  // If a thread ID exists for this block, make an API request to get existing messages
  /*
  if (api_type === 'assistant') {
    let chatData = getBlockChatData();
    if (chatData['threadId'] && persistConvo) {
      fetch(`${M.cfg.wwwroot}/blocks/exaaichat/api/thread.php?thread_id=${chatData['threadId']}`)
        .then(response => response.json())
        .then(data => {
          for (let message of data) {
            addToChatLog(message.role === 'user' ? 'user' : 'assistant', message.message, blockId)
          }
        })
        // Some sort of error in the API call. Probably the thread no longer exists, so lets reset it
        .catch(() => {
          // clearBlockChatData();
        })
      // The block ID doesn't exist in the chat data object, so let's create it
    }
    // We don't even have a chat data object, so we'll create one
  } else {
  */

  if (persistConvo) {
    reloadChatHistory();
  }
  // }

// Prevent sidebar from closing when osk pops up (hack for MDL-77957)
  window.addEventListener('resize', event => {
    event.stopImmediatePropagation();
  }, true);

  $form.find('.exaaichat_input').on('keydown', e => {
    if (e.which === 13 && e.target.value !== "") {
      e.preventDefault();
      addToChatLog('user', e.target.value, blockId)
      createCompletion(e.target.value, blockId, api_type)
      e.target.value = ''
    }
  })
  $form.find('.exaaichat_input_submit_btn').on('click', () => {
    if ($input.val()) {
      addToChatLog('user', $input.val(), blockId);
      createCompletion($input.val(), blockId, api_type);
      $input.val('');
    } else {
      $input.focus();
    }
  })

  $form.find('.exaaichat_input_refresh_btn').on('click', () => {
    setBlockChatData({history: [], historyTimestamp: Date.now()});
    clearHistory();
    $input.focus();
  })

  $form.find('.exaaichat_popout_btn, .exaaichat_btn_close').click(() => {
    if (document.querySelector('.drawer.drawer-right')) {
      document.querySelector('.drawer.drawer-right').style.zIndex = '1041';
    }
    $form.toggleClass('expanded');
  });

  // beim laden den letzten ai_provider setzen
  const ai_provider = getBlockChatData().ai_provider;
  if (ai_provider) {
    $form.find('select[name="ai_provider"]').val(ai_provider);
  }
  $form.find('select[name="ai_provider"]').on('change', function () {
    setBlockChatData({ai_provider: this.value});
  });

  // enable inputs after page loaded
  $form.find('.disabled').removeClass('disabled');

  if (!useMoodleLocalStorage) {
    // handle changes from other windows/tabs
    window.addEventListener("storage", (event) => {
      if (event.key !== "block_exaaichat_data") {
        return;
      }

      const chatData = getBlockChatData();
      $form.find('select[name="ai_provider"]').val(chatData.ai_provider);

      if (historyTimestamp != (chatData.historyTimestamp || 0)) {
        reloadChatHistory();
      }
    });
  }
}
