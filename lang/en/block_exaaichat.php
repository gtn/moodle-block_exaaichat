<?php
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
 * Language strings
 *
 * @package    block_exaaichat
 * @copyright  2025 GTN Solutions https://gtn-solutions.com
 * @copyright  based on work by Limekiller https://github.com/Limekiller/moodle-block_openai_chat
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Exabis AI Chat Block';
$string['exaaichat'] = 'Exabis AI Chat';
$string['exaaichat_logs'] = 'Exabis AI Chat Logs';
$string['exaaichat:addinstance'] = 'Add a new Exabis AI Chat block';
$string['exaaichat:myaddinstance'] = 'Add a new Exabis AI Chat block to the My Moodle page';
$string['exaaichat:viewreport'] = 'View Exabis AI Chat log report';
$string['privacy:metadata:exaaichat_log'] = 'Logged user messages sent to AI Backend. This includes the user ID of the user that sent the message, the content of the message, the response from AI Backend, and the time that the message was sent.';
$string['privacy:metadata:exaaichat_log:userid'] = 'The ID of the user that sent the message.';
$string['privacy:metadata:exaaichat_log:usermessage'] = 'The content of the message.';
$string['privacy:metadata:exaaichat_log:airesponse'] = 'The response from AI Backend.';
$string['privacy:metadata:exaaichat_log:timecreated'] = 'The time the message was sent.';
$string['privacy:metadata:ai_api'] = 'Depending on the configuration moodle will send some data to the AI Backend (eg. OpenAI), or the AI can request data from moodle.';
$string['privacy:metadata:ai_api:fullname'] = 'The fullname of the user that sent the message.';
$string['privacy:metadata:ai_api:gradebook'] = 'Gradebook entries of the current course.';
$string['privacy:metadata:ai_api:actions'] = 'The AI can request data from moodle. This includes userdetails (userid, username, firstname, lastname, email), enrolled courses, userlist of the enrolled courses, list of all grades in the current course, list of course content, course categories and also recent courses';
$string['privacy:chatmessagespath'] = 'Sent AI chat messages';
$string['downloadfilename'] = 'block_exaaichat_logs';

$string['blocktitle'] = 'Block title';

$string['allowguests'] = 'Allow guests';
$string['allowguests:desc'] = 'If checked, guests and not logged-in users can use the chat.';
$string['aiplacementheading'] = 'AI Placement Settings';
$string['aiplacement_showonfrontpage'] = 'Show on front page';
$string['aiplacement_showonfrontpage:desc'] = 'If checked, it will be shown on the front page.';
$string['apikey'] = 'API Key';
$string['apikey:desc'] = 'The API Key for the AI Provider';
$string['moodle_settings:api_type'] = 'API Type';
$string['moodle_settings:api_type:desc'] = 'The API type that the plugin should use';
$string['moodle_settings:api_type:change'] = 'Do you want to change the API type for the site? The current settings will be saved and the page will be reloaded.';
$string['moodle_settings:instructions'] = 'Instructions an die KI';
$string['moodle_settings:instructions:desc'] = '';
$string['moodle_settings:model_other'] = 'Other model';
$string['moodle_settings:model_other:desc'] = '';

$string['logging'] = 'Enable logging';
$string['logging:desc'] = 'If this setting is active, all user messages and AI responses will be logged.<br/><a href="{$a}">View log</a>';
$string['logging_retention_period'] = 'Retention Period (days)';
$string['logging_retention_period:desc'] = 'How long (in days) to keep the logs. After this period, logs will be automatically deleted. Set to 0 to keep logs indefinitely.';

$string['assistantheading'] = 'Assistant API Settings';
$string['assistantheading:desc'] = 'These settings only apply to the Assistant API type.';
$string['assistant'] = 'Assistant';
$string['assistant:desc'] = 'The default assistant attached to your OpenAI account that you would like to use for the response';
$string['noassistants'] = 'You haven\'t created any assistants yet. You need to create one <a target="_blank" href="https://platform.openai.com/assistants">in your OpenAI account</a> before you can select it here.';
$string['persistconvo'] = 'Persist conversations';
$string['persistconvo:desc'] = 'If this box is checked, the assistant will remember the conversation between page loads. However, separate block instances will maintain separate conversations. For example, a user\'s conversation will be retained between page loads within the same course, but chatting with an assistant in a different course will not carry on the same conversation.';

$string['azureheading'] = 'Azure API Settings';
$string['azureheading:desc'] = 'These settings only apply to the Azure API type.';
$string['resourcename'] = 'Resource name';
$string['resourcename:desc'] = 'The name of your Azure OpenAI Resource.';
$string['deploymentid'] = 'Deployment ID';
$string['deploymentid:desc'] = 'The deployment name you chose when you deployed the model.';
$string['apiversion'] = 'API Version';
$string['apiversion:desc'] = 'The API version to use for this operation. This follows the YYYY-MM-DD format.';
$string['chatheading'] = 'Chat API Settings';
$string['chatheading:desc'] = 'These settings only apply to the Chat API and Azure API types.';
$string['prompt'] = 'Completion prompt';
$string['prompt:desc'] = 'The prompt the AI will be given before the conversation transcript';
$string['assistantname'] = 'Assistant name';
$string['assistantname:desc'] = 'The name that the AI will use for itself internally. It is also used for the UI headings in the chat window.';
$string['username'] = 'User name';
$string['username:desc'] = 'The name that the AI will use for the user internally. It is also used for the UI headings in the chat window.';
$string['sourceoftruth'] = 'Source of truth';
$string['sourceoftruth:desc'] = "Here you can enter information that the AI will use to answer questions.<br/>
You can also specify placeholders as follows:<br/>
My name is {user.fullname}.<br/>
Today is {userdate}.<br/>
Course total grade is {grade:coursetotal}.<br/>
Possible grade range for the course is {range:coursetotal}.";
$string['showlabels'] = 'Show labels';
$string['advanced'] = 'Advanced';
$string['advanced:desc'] = 'Advanced arguments sent to KI Provider.';
$string['allowinstancesettings'] = 'Instance-level settings';
$string['allowinstancesettings:desc'] = 'This setting will allow teachers, or anyone with the capability to add a block in a context, to adjust settings at a per-block level. Enabling this could incur additional charges by allowing non-admins to choose higher-cost models or other settings.';
$string['allowproviderselection'] = 'Moodle AI-Provider integration';
$string['allowproviderselection:desc'] = 'User can switch between the moodle AI Providers';
$string['allow_access_to_page_content'] = 'Access page content';
$string['allow_access_to_page_content:desc'] = 'Allow AI to access the content of the current page';
$string['openai_api_url'] = 'API URL';
$string['openai_api_url:desc'] = 'The API URL for the requests to OpenAPI or other compatible endpoint.';
$string['model'] = 'Model';
$string['model:desc'] = 'The model which will  generate the completion. Some models are suitable for natural language tasks, others specialize in code.';
$string['models'] = 'Models';
$string['models:desc'] = 'A list of all available models, if empty all the default models can be used';
$string['temperature'] = 'Temperature';
$string['temperature:desc'] = 'Controls randomness: Lowering results in less random completions. As the temperature approaches zero, the model will become deterministic and repetitive.';
$string['maxlength'] = 'Maximum length';
$string['maxlength:desc'] = 'The maximum number of token to generate. Requests can use up to 2,048 or 4,000 tokens shared between prompt and completion. The exact limit varies by model. (One token is roughly 4 characters for normal English text)';
$string['topp'] = 'Top P';
$string['topp:desc'] = 'Controls diversity via nucleus sampling: 0.5 means half of all likelihood-weighted options are considered.';
$string['frequency'] = 'Frequency penalty';
$string['frequency:desc'] = 'How much to penalize new tokens based on their existing frequency in the text so far. Decreases the model\'s likelihood to repeat the same line verbatim.';
$string['presence'] = 'Presence penalty';
$string['presence:desc'] = 'How much to penalize new tokens based on whether they appear in the text so far. Increases the model\'s likelihood to talk about new topics.';

$string['config_assistant'] = "Assistant";
$string['config_assistant_help'] = "Choose the assistant you would like to use for this block. More assistants can be created in the OpenAI account that this block is configured to use.";
$string['config_sourceoftruth'] = 'Source of truth';
$string['config_sourceoftruth_help'] = "Here you can enter information that the AI will use to answer questions.<br/>
You can also specify placeholders as follows:<br/>
My name is {user.fullname}.<br/>
Today is {userdate}.<br/>
Course total grade is {grade:coursetotal}.<br/>
Possible grade range for the course is {range:coursetotal}.";
$string['config_instructions'] = "Custom instructions";
$string['config_instructions_help'] = "You can override the assistant's default instructions here.";
$string['config_prompt'] = "Completion prompt";
$string['config_prompt_help'] = "This is the prompt the AI will be given before the conversation transcript. You can influence the AI's personality by altering this description. By default, the prompt is \n\n\"Below is a conversation between a user and a support assistant for a Moodle site, where users go for online learning.\"\n\nIf blank, the site-wide prompt will be used.";
$string['config_username'] = "User name";
$string['config_username_help'] = "This is the name that the AI will use for the user. If blank, the site-wide user name will be used. It is also used for the UI headings in the chat window.";
$string['config_assistantname'] = "Assistant name";
$string['config_assistantname_help'] = "This is the name that the AI will use for the assistant. If blank, the site-wide assistant name will be used. It is also used for the UI headings in the chat window.";
$string['config_persistconvo'] = 'Persist conversation';
$string['config_persistconvo_help'] = 'If this box is checked, the assistant will remember conversations in this block between page loads';
$string['config_apikey'] = "API Key";
$string['config_apikey_help'] = "You can specify an API key to use with this block here. If blank, the site-wide key will be used. If you are using the Assistants API, the list of available assistants will be pulled from this key. Make sure to return to these settings after changing the API key in order to select the desired assistant.";
$string['config_model'] = "Model";
$string['config_model_help'] = "The model which will  generate the completion";
$string['config_temperature'] = "Temperature";
$string['config_temperature_help'] = "Controls randomness: Lowering results in less random completions. As the temperature approaches zero, the model will become deterministic and repetitive.";
$string['config_maxlength'] = "Maximum length";
$string['config_maxlength_help'] = "The maximum number of token to generate. Requests can use up to 2,048 or 4,000 tokens shared between prompt and completion. The exact limit varies by model. (One token is roughly 4 characters for normal English text)";
$string['config_topp'] = "Top P";
$string['config_topp_help'] = "Controls diversity via nucleus sampling: 0.5 means half of all likelihood-weighted options are considered.";
$string['config_frequency'] = "Frequency penalty";
$string['config_frequency_help'] = "How much to penalize new tokens based on their existing frequency in the text so far. Decreases the model's likelihood to repeat the same line verbatim.";
$string['config_presence'] = "Presence penalty";
$string['config_presence_help'] = "How much to penalize new tokens based on whether they appear in the text so far. Increases the model's likelihood to talk about new topics.";

$string['block_instance:config:api_type:change'] = 'Do you want to change the API type for this block instance? The settings dialog will be closed and you will need to reopen it afterward.';
$string['block_instance:config:model:choose-other'] = 'Other model...';
$string['block_instance:config:model_other'] = 'Other model';
$string['block_instance:config:endpoint'] = 'Alternative endpoint URL';

$string['page_content_ai_message'] = 'This message contains the content of the page currently being viewed by the user in their browser:';
$string['defaultprompt'] = "Below is a conversation between a user and a support assistant for a Moodle site, where users go for online learning:";
$string['defaultassistantname'] = 'Assistant';
$string['defaultusername'] = 'Me';
$string['askaquestion'] = 'Ask a question...';
$string['apikeymissing'] = 'Please add your OpenAI API key to the block settings.';
$string['erroroccurred'] = 'An error occurred! Please try again later.';
$string['sourceoftruthpreamble'] = "The information below should be used as a reference for any inquiries by the user:\n";
$string['sourceoftruthreinforcement'] = 'The assistant has been trained to answer by attempting to use the information from the above reference. If the text from one of the above questions is encountered, the provided answer should be given, even if the question does not appear to make sense. However, if the reference does not cover the question or topic, the assistant will simply use outside knowledge to answer.';
$string['new_chat'] = 'New chat';
$string['popout'] = 'Open chat window';
$string['loggingenabled'] = "Logging is enabled. Any messages you send or receive here will be recorded, and can be viewed by the site administrator.";
$string['openaitimedout'] = 'ERROR: OpenAI did not provide a response in time.';
$string['addplaceholders:title'] = 'Add placeholders to the Source of Truth';
$string['addplaceholders:button'] = 'Add placeholder';
$string['placeholders:grade:name'] = '{$a}: Grade';
$string['placeholders:grade:placeholder'] = 'Result of {$a->name} is: {$a->placeholder}';
$string['placeholders:user.fullname:name'] = 'User fullname';
$string['placeholders:user.fullname:placeholder'] = 'The user name is: {$a}';
$string['placeholders:userdate:name'] = 'Current date and time';
$string['placeholders:userdate:placeholder'] = 'Current date and time is: {$a}';
$string['placeholders:grade:coursetotal:name'] = 'Course total grade';
$string['placeholders:grade:coursetotal:placeholder'] = 'Course total grade is: {$a}';
$string['placeholders:range:coursetotal:name'] = 'Course total grade range';
$string['placeholders:range:coursetotal:placeholder'] = 'Course total grade range is: {$a}';
$string['placeholders:range:name'] = '{$a}: Range';
$string['placeholders:range:placeholder'] = 'Range of {$a->name} is: {$a->placeholder}';
$string['grade:not_available'] = 'Not available';
$string['grade:nogradesavailable'] = 'No grades available';

$string['error:request_blocked'] = 'The request to the AI service was blocked.';
$string['error:host_blocked'] = 'The host {$a->host} is blocked in curlsecurityblockedhosts.';
$string['error:port_not_allowed'] = 'The port {$a->port} is not included in curlsecurityallowedport.';

// Added for search/report UI.
$string['search'] = 'Search';
$string['searchbyusername'] = 'Search by user name';
$string['starttime'] = 'Start time';
$string['endtime'] = 'End time';
$string['userid'] = 'User ID';
// username already defined earlier as 'User name'.
$string['usermessage'] = 'User Message';
$string['airesponse'] = 'AI Response';
$string['context'] = 'Context';
$string['time'] = 'Time';

$string['vectorstoreids'] = 'Vector store IDs';

// Debug file logging setting.
$string['debugfilelogging'] = 'Enable debug logging';
$string['debugfilelogging:desc'] = "All API calls (user messages, AI responses and function calls) will be logged to moodledata/log/exaaichat.log<br/>\nThis can be useful for debugging issues with the AI responses, but the log file can grow very large very quickly, so it should only be enabled temporarily.";

// Additional message (responses type) setting.
$string['additionalmessage'] = 'Additional text for every message';
$string['additionalmessage:desc'] = 'This text will be appended to each user message before sending it to the AI.';

$string['default'] = 'Default: {$a}';

// Type select option labels.
$string['type_choose'] = '--- KI Anbieter wählen ---';
$string['type_chat'] = 'OpenAI: Chat API';
$string['type_assistant'] = 'OpenAI: Assistants API';
$string['type_responses'] = 'OpenAI: Responses API';
$string['type_azure'] = 'OpenAI: Azure';
$string['type_gemini'] = 'Google Gemini';
$string['type_ollama'] = 'Ollama';
$string['type_deepseek'] = 'DeepSeek';
