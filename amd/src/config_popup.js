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
