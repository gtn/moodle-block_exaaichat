/**
 * init function, which is called when the instance form is displayed
 */
export function init() {
  // Info: using the name of the field, because the element id is created dynamically by moodle
  let addActivityButton = document.getElementsByName("config_add_activity_button")[0];
  let activityDropdown = document.getElementsByName("config_activity_dropdown")[0];
  let userMessageTextarea = document.getElementsByName("config_user_message")[0];

  if (!addActivityButton || !activityDropdown || !userMessageTextarea) {
    return;
  }

  // enable button (was disabled in form config by default)
  addActivityButton.disabled = false;
  addActivityButton.addEventListener('click', () => {
    let selectedActivity = activityDropdown.value;

    let selectContent = activityDropdown.options[activityDropdown.selectedIndex].text;
    // TODO: übersetzen
    let placeholder = `Result of ${selectContent} is: {grade:${selectedActivity}}`;
    userMessageTextarea.value += (userMessageTextarea.value ? "\n" : "") + placeholder;

    // scroll to bottom of the textarea
    userMessageTextarea.scrollTo({
      top: userMessageTextarea.scrollHeight,
      behavior: 'smooth'
    });
  });
}
