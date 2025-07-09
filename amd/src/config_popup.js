/* eslint-disable */

/*
TODO: this should only be called when the config popup is opened, not on the page load. For now, this is loaded in edit_form.php
export const init = () => {
  console.log('JS init config_popup called'); // Add logging for debug
  // document.addEventListener('DOMContentLoaded', () => {
    debugger;
    (function () { // this is needed to avoid polluting the global scope and getting an error message: Uncaught SyntaxError: Failed to execute appendChild on Node: Identifier addActivityButton has already been declared
      debugger;
      let addActivityButton = document.getElementsByName("config_add_activity_button")[0]; // TODO: this works, but id would be better, but id is created dynamically
      let activityDropdown = document.getElementsByName("config_activity_dropdown")[0];
      let userMessageTextarea = document.getElementsByName("config_user_message")[0];
      console.log('JS init config_popup elements found:', { addActivityButton, activityDropdown, userMessageTextarea }); // Add logging for debug

      if (addActivityButton && activityDropdown && userMessageTextarea) {
        addActivityButton.addEventListener("click", () => {
          console.log('JS init config_popup click registered'); // Add logging for debug
          let selectedActivity = activityDropdown.value;
          // get the name of the selected activity
          if (!selectedActivity) {
            alert("Please select an activity first.");
            return;
          }
          let selectContent = activityDropdown.options[activityDropdown.selectedIndex].text;
          let activityName = selectContent.split(":")[1].trim(); // Get the name after the first colon
          let placeholder = `Result of ${selectContent} is: {grade:${activityName}}`;
          userMessageTextarea.value += (userMessageTextarea.value ? "\n" : "") + placeholder;
        });
      }
    })();
  // });
};
*/
