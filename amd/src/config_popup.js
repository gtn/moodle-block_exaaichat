/* eslint-disable */

export const init = () => {
  console.log('JS init config_popup called'); // Add logging for debug
  debugger;
  document.addEventListener('DOMContentLoaded', () => {
    // TODO: this only works once the config is opened
    debugger;
    const addActivityButton = document.getElementsByName('config_add_activity_button')[0]; // TODO: this works, but id would be better, but id is created dynamically
    const activityDropdown = document.getElementsByName('config_activity_dropdown')[0];
    const userMessageTextarea = document.getElementsByName('config_user_message')[0];

    if (addActivityButton && activityDropdown && userMessageTextarea) {
      addActivityButton.addEventListener('click', () => {
        debugger;
        const selectedActivity = activityDropdown.value;
        const placeholder = `Result of Assignment ${selectedActivity} is: {grade:${selectedActivity}}`;
        userMessageTextarea.value += (userMessageTextarea.value ? '\n' : '') + placeholder;
      });
    }

  });
};
