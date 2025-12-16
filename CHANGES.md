### v3.2 (2025121600) ###
* New setting: allow AI Placement on frontpage
- Rename Setting: allow guests

### v3.2 (2025121000) ###
* More detailed error message when curlsecurityblockedhosts or curlsecurityallowedhosts block the request
* add more info to the debug log to troubleshoot issues

### v3.2 (2025120700) ###
* Check if url was blocked by curlsecurityblockedhosts setting
* Better error handling in javascript
* localhost endpoints don't require api key

### v3.2 (2025120300) ###
* Fixes for ollama on localhost

### v3.2 (2025120200) ###
* Fixes for ollama in Moodle AI Providers and on localhost
* Fix some moodle41 issues
* Reports are on by default
* Fix incorrect default setting

### v3.2 (2025112800) ###
* Integration of Ollama and Gemini AI providers
* Source of truth placeholders are also available in the admin settings
* Admin Settings now allows to define a different model.
* Update OpenAI Models list

### v3.2 (2025112700) ###
* Improve ai placement integration
* fix block edit url

### v3.2 (2025112500) ###
* Improve error handling
* Fix: default apikey only allowed when using default endpoint

### v3.2 (2025112100) ###
* New Block Setting: Retention Period (days)
  How long (in days) to keep the logs. After this period, logs will be automatically deleted.
* Moodle AI Provider integration
* Users can now select different AI providers in the chat dialog
* Chat history is now saved in the Browser (localstorage), so after page reload the chat history is still available
* Modernize chat styling and input styling

### v3.1 (2025111200) ###
* Allow inserting grades and grade ranges in the sourceoftruth field
* Allow configuring different Endpoints in Moodle Admin Settings and Block Instance Settings
  This allows using different AI providers (with openai compatible APIs) for different courses
* Allow configuring different AI models in Moodle Admin Settings and Block Instance Settings
  This allows using different models for different courses
* Improve the Placeholder selector UI (placeholders are now grouped by category and grade categories)
* Improve error handling when communicating with the AI provider and show more detailed error messages to the user
* Add german translation

### v3.0.1 (2025060400) ###
* initial public release of exaaichat block
