/**
 * init function, which is called when the instance form is displayed
 */
export function init() {
  const addActivityButton = document.getElementById("config_add_activity_button");
  const activityDropdown = document.getElementById("config_activity_dropdown");
  // Info: using the name of the field, because the element id iscreated dynamically by moodle
  const userMessageTextarea = document.getElementsByName("config_user_message")[0];

  if (!addActivityButton || !activityDropdown || !userMessageTextarea) {
    return;
  }

  // enable button (was disabled in form config by default)
  addActivityButton.disabled = false;
  addActivityButton.addEventListener('click', () => {
    const selectedActivity = activityDropdown.value;

    const selectContent = activityDropdown.options[activityDropdown.selectedIndex].text;
    // TODO: übersetzen
    const placeholder = `Result of ${selectContent} is: {grade:${selectedActivity}}`;
    userMessageTextarea.value += (userMessageTextarea.value ? "\n" : "") + placeholder;

    // scroll to bottom of the textarea
    userMessageTextarea.scrollTo({
      top: userMessageTextarea.scrollHeight,
      behavior: 'smooth'
    });
  });
}
