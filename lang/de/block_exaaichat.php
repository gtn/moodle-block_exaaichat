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
 * Deutsche Sprachstrings
 *
 * @package    block_exaaichat
 * @copyright  2025 GTN Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


$string['pluginname'] = 'Exabis KI Chat Block';
$string['exaaichat'] = 'Exabis KI Chat';
$string['exaaichat_logs'] = 'Exabis Chat-Protokolle';
$string['exaaichat:addinstance'] = 'Neuen Exabis KI Chat Block hinzufügen';
$string['exaaichat:myaddinstance'] = 'Exabis KI Chat Block zur „Dashboard“-Seite hinzufügen';
$string['exaaichat:viewreport'] = 'Exabis KI Chat-Protokollbericht anzeigen';
$string['privacy:metadata:exaaichat_log'] = 'Protokollierte vom Benutzer gesendete Nachrichten an den KI-Backend. Dies umfasst die Benutzer-ID des Absenders, den Inhalt der gesendeten Nachricht, die vom KI-Backend zurückgegebene Antwort sowie den Zeitpunkt des Sendens.';
$string['privacy:metadata:exaaichat_log:userid'] = 'Die ID des Benutzers, der die Nachricht gesendet hat.';
$string['privacy:metadata:exaaichat_log:usermessage'] = 'Inhalt der Nachricht.';
$string['privacy:metadata:exaaichat_log:airesponse'] = 'Die Antwort des KI-Backends.';
$string['privacy:metadata:exaaichat_log:timecreated'] = 'Zeitpunkt des Sendens.';
$string['privacy:metadata:ai_api'] = 'Abhängig von der Konfiguration sendet Moodle bestimmte Daten an das KI-Backend (z. B. an OpenAI) oder das KI-Backend kann Daten aus Moodle abfragen.';
$string['privacy:metadata:ai_api:fullname'] = 'Der vollständige Name des Benutzers, der die Nachricht gesendet hat.';
$string['privacy:metadata:ai_api:gradebook'] = 'Bewertungseinträge des aktuellen Kurses.';
$string['privacy:metadata:ai_api:actions'] = 'Die KI kann folgende Daten von Moodle anfordern: Benutzerdetails (userid, username, firstname, lastname, email), eingeschriebene Kurse, Benutzerlisten der eingeschriebenen Kurse, Liste aller Noten im aktuellen Kurs, Kursinhalte, Kurskategorien sowie zuletzt verwendete Kurse.';
$string['privacy:chatmessagespath'] = 'Gesendete KI-Chat-Nachrichten';
$string['downloadfilename'] = 'block_exaaichat_protokolle';

$string['blocktitle'] = 'Blocktitel';

$string['restrictusage'] = 'Verwendung auf angemeldete Nutzer beschränken';
$string['restrictusage:desc'] = 'Wenn dieses Kästchen aktiviert ist, können nur angemeldete Benutzer das Chat-Fenster verwenden.';
$string['apikey'] = 'API-Schlüssel';
$string['apikey:desc'] = 'Der API-Schlüssel des KI Anbieters.';
$string['moodle_settings:api_type'] = 'API-Typ';
$string['moodle_settings:api_type:desc'] = 'Der API-Typ, den dieses Plugin verwenden soll.';
$string['moodle_settings:api_type:change'] = 'Möchten Sie den API-Typ ändern? Es werden die aktuellen Einstellungen gespeichert und die Seite wird neu geladen.';
$string['moodle_settings:instructions'] = 'Anweisungen an die KI';
$string['moodle_settings:instructions:desc'] = '';
$string['moodle_settings:model_other'] = 'Anderes Modell';
$string['moodle_settings:model_other:desc'] = '';
$string['logging'] = 'Protokollierung aktivieren';
$string['logging:desc'] = 'Wenn diese Einstellung aktiviert ist, werden alle Benutzernachrichten und KI-Antworten protokolliert.';
$string['logging_retention_period'] = 'Protokollaufbewahrungszeitraum (in Tagen)';
$string['logging_retention_period:desc'] = 'Die Anzahl der Tage, die Protokolleinträge aufbewahrt werden, bevor sie automatisch gelöscht werden. Setze diesen Wert auf 0, um Protokolle unbegrenzt aufzubewahren.';

$string['assistantheading'] = 'Assistent API Einstellungen';
$string['assistantheading:desc'] = 'Diese Einstellungen gelten nur für den Assistenten-API-Typ.';
$string['assistant'] = 'Assistent';
$string['assistant:desc'] = 'Der Standard-Assistent aus deinem OpenAI-Konto, den du für die Generierung der Antworten verwenden möchtest.';
$string['noassistants'] = 'Du hast noch keine Assistenten erstellt. Lege zuerst einen <a target="_blank" href="https://platform.openai.com/assistants">in deinem OpenAI-Konto</a> an.';
$string['persistconvo'] = 'Konversation merken';
$string['persistconvo:desc'] = 'Wenn dieses Kontrollkästchen aktiviert ist, merkt sich der Assistent die Konversation zwischen Seitenaufrufen innerhalb desselben Kontextes. Verschiedene Blockinstanzen (z. B. in einem anderen Kurs) teilen sich keine Konversation.';

$string['azureheading'] = 'Azure API Einstellungen';
$string['azureheading:desc'] = 'Diese Einstellungen gelten nur für den Azure API-Typ.';
$string['resourcename'] = 'Ressourcenname';
$string['resourcename:desc'] = 'Der Name deiner Azure OpenAI Ressource.';
$string['deploymentid'] = 'Deployment-ID';
$string['deploymentid:desc'] = 'Der Name des Deployments, den du beim Bereitstellen gewählt hast.';
$string['apiversion'] = 'API-Version';
$string['apiversion:desc'] = 'Die zu verwendende API-Version im Format JJJJ-MM-TT.';
$string['chatheading'] = 'Chat API Einstellungen';
$string['chatheading:desc'] = 'Diese Einstellungen gelten nur für die Chat-API und die Azure-API Typen.';
$string['prompt'] = 'Prompt';
$string['prompt:desc'] = 'Der Prompt, der der KI vor dem Gespräch gesendet wird.';
$string['assistantname'] = 'Name des Assistenten';
$string['assistantname:desc'] = 'Der Name, den die KI intern für sich selbst verwendet. Er erscheint ebenfalls in den Überschriften des Chat-Fensters.';
$string['username'] = 'Name des Benutzers';
$string['username:desc'] = 'Der Name, den die KI intern für den Benutzer verwendet. Er erscheint ebenfalls in den Überschriften des Chat-Fensters.';
$string['sourceoftruth'] = 'Wissensbasis für die KI';
$string['sourceoftruth:desc'] = "Hier können Sie Informationen hinterlegen, die von der KI zur Beantwortung Ihrer Anfragen verwendet werden.<br/>
Es sind auch Platzhalter möglich, z.B.:<br/>
Mein Name ist {user.fullname}.<br/>
Heute ist {userdate}.<br/>
Kursgesamtbewertung ist {grade:coursetotal}.<br/>
Möglicher Notenbereich für den Kurs ist {range:coursetotal}.";
$string['showlabels'] = 'Labels anzeigen';
$string['advanced'] = 'Erweitert';
$string['advanced:desc'] = 'Erweiterte Parameter, die an die KI gesendet werden.';
$string['allowinstancesettings'] = 'Instanzbezogene Einstellungen';
$string['allowinstancesettings:desc'] = 'Diese Einstellung erlaubt es Lehrenden bzw. jedem mit der Berechtigung, einen Block im jeweiligen Kontext hinzuzufügen, instanzspezifische Einstellungen vorzunehmen. Dies kann zu zusätzlichen Kosten führen (z. B. durch Auswahl teurerer Modelle).';
$string['allowproviderselection'] = 'Moodle KI-Provider Integration';
$string['allowproviderselection:desc'] = 'Benutzer können eine KI wählen (aus den Moodle KI-Provider)';
$string['allow_access_to_page_content'] = 'Zugriff auf Seiteninhalt';
$string['allow_access_to_page_content:desc'] = 'Die KI auf den Inhalt der aktuell angezeigten Seite zugreifen lassen';
$string['model'] = 'Modell';
$string['model:desc'] = 'Das Modell, das die Generierung der Antwort übernimmt. Einige Modelle sind auf natürliche Sprache spezialisiert, andere auf Code.';
$string['models'] = 'Modelle';
$string['models:desc'] = 'Überschreibung der verfügbaren Modelle für die Auswahl im Kurs';
$string['temperature'] = 'Temperatur';
$string['temperature:desc'] = 'Steuert die Zufälligkeit: Ein niedrigerer Wert führt zu weniger zufälligen Antworten. Je näher die Temperatur an 0 liegt, desto deterministischer und repetitiver wird die Ausgabe.';
$string['maxlength'] = 'Maximale Länge';
$string['maxlength:desc'] = 'Maximale Anzahl von Tokens, die generiert werden können. Eingaben teilen sich das Token-Limit zwischen Prompt und Antwort. (Ein Token entspricht grob 4 Zeichen normalen englischen Textes)';
$string['topp'] = 'Top P';
$string['topp:desc'] = 'Steuert Diversität mittels Nucleus Sampling: 0.5 bedeutet, dass nur die wahrscheinlichsten 50% (gewichtete Optionen) betrachtet werden.';
$string['frequency'] = 'Frequenz-Penalty';
$string['frequency:desc'] = 'Legt fest, wie stark neue Tokens bestraft werden, basierend auf ihrer bisherigen Häufigkeit im Text. Reduziert die Wahrscheinlichkeit, dass identische Zeilen wiederholt werden.';
$string['presence'] = 'Präsenz-Penalty';
$string['presence:desc'] = 'Legt fest, wie stark neue Tokens bestraft werden, basierend darauf, ob sie bereits im Text vorkommen. Erhöht die Wahrscheinlichkeit, dass neue Themen angesprochen werden.';

$string['config_assistant'] = 'Assistent';
$string['config_assistant_help'] = 'Wähle den Assistenten, den du für diesen Block verwenden möchtest. Weitere Assistenten können im OpenAI-Konto, das dieser Block nutzt, erstellt werden.';
$string['config_sourceoftruth'] = 'Wissensbasis';
$string['config_sourceoftruth_help'] = "Hier können Sie Informationen hinterlegen, die von der KI zur Beantwortung Ihrer Anfragen verwendet werden.<br/>
Es sind auch Platzhalter möglich, z.B.:<br/>
Mein Name ist {user.fullname}.<br/>
Heute ist {userdate}.<br/>
Kursgesamtbewertung ist {grade:coursetotal}.<br/>
Möglicher Notenbereich für den Kurs ist {range:coursetotal}.";
$string['config_instructions'] = 'Benutzerdefinierte Anweisungen';
$string['config_instructions_help'] = 'Du kannst hier die Standard-Anweisungen des Assistenten überschreiben.';
$string['config_prompt'] = 'Prompt';
$string['config_prompt_help'] = 'Dies ist der Prompt, der der KI vor dem Gespräch gesendet wird. Du kannst die Persönlichkeit der KI beeinflussen, indem du diese Beschreibung anpasst. Standard ist:\n\n"Below is a conversation between a user and a support assistant for a Moodle site, where users go for online learning."\n\nWenn leer, wird der siteweite Prompt verwendet.';
$string['config_username'] = 'Benutzername';
$string['config_username_help'] = 'Name, den die KI für den Benutzer verwendet. Wenn leer, wird der siteweite Benutzername verwendet. Er erscheint außerdem in den Überschriften des Chat-Fensters.';
$string['config_assistantname'] = 'Assistentenname';
$string['config_assistantname_help'] = 'Name, den die KI für sich selbst verwendet. Wenn leer, wird der siteweite Assistentenname verwendet. Er erscheint außerdem in den Überschriften des Chat-Fensters.';
$string['config_persistconvo'] = 'Konversation merken';
$string['config_persistconvo_help'] = 'Wenn aktiviert, merkt sich der Assistent die Konversation dieser Blockinstanz zwischen Seitenaufrufen.';
$string['config_apikey'] = 'API-Schlüssel';
$string['config_apikey_help'] = 'Optionaler API-Schlüssel nur für diese Blockinstanz. Wenn leer, wird der siteweite Schlüssel verwendet. Bei Nutzung der Assistants API wird die Assistentenliste mit diesem Schlüssel geladen. Nach Änderung des Schlüssels erneut hierher zurückkehren, um den gewünschten Assistenten auszuwählen.';
$string['config_model'] = 'Modell';
$string['config_model_help'] = 'Das Modell, das die Antwort generiert.';
$string['config_temperature'] = 'Temperatur';
$string['config_temperature_help'] = 'Steuert die Zufälligkeit: Niedriger = weniger zufällig. Nähert sich der Wert 0, wird die Ausgabe deterministischer und repetitiver.';
$string['config_maxlength'] = 'Maximale Länge';
$string['config_maxlength_help'] = 'Maximale Anzahl von Tokens für die generierte Antwort (inkl. Prompt). (Ein Token entspricht grob 4 Zeichen normalen englischen Textes). Das Token-Limit variiert je nach Modell.';
$string['config_topp'] = 'Top P';
$string['config_topp_help'] = 'Steuert Diversität mittels Nucleus Sampling. 0.5 bedeutet, dass nur die wahrscheinlichsten 50% (gewichtete Optionen) betrachtet werden.';
$string['config_frequency'] = 'Frequenz-Penalty';
$string['config_frequency_help'] = 'Legt fest, wie stark neue Tokens bestraft werden, basierend auf ihrer bisherigen Häufigkeit im Text. Reduziert die Wahrscheinlichkeit, dass identische Zeilen wiederholt werden.';
$string['config_presence'] = 'Präsenz-Penalty';
$string['config_presence_help'] = 'Legt fest, wie stark neue Tokens bestraft werden, basierend darauf, ob sie bereits im Text vorkommen. Erhöht die Wahrscheinlichkeit, dass neue Themen angesprochen werden.';

$string['block_instance:config:api_type:change'] = 'Möchten Sie den API-Typ für diese Blockinstanz ändern? Der Einstellungsdialog wird dabei geschlossen und muss anschließend erneut geöffnet werden.';
$string['block_instance:config:model:choose-other'] = 'Anderes Modell...';
$string['block_instance:config:model_other'] = 'Anderes Modell';
$string['block_instance:config:endpoint'] = 'Alternative Endpunkt-URL';

$string['page_content_ai_message'] = 'Diese Nachricht enthält den Inhalt der aktuell vom Benutzer im Browser angezeigten Seite:';
$string['defaultprompt'] = 'Below is a conversation between a user and a support assistant for a Moodle site, where users go for online learning:'; // Belassen (kontextuell englisch)
$string['defaultassistantname'] = 'Assistent';
$string['defaultusername'] = 'Ich';
$string['askaquestion'] = 'Stell eine Frage...';
$string['apikeymissing'] = 'Bitte trage deinen OpenAI API-Schlüssel in den Blockeinstellungen ein.';
$string['erroroccurred'] = 'Es ist ein Fehler aufgetreten! Bitte versuche es später erneut.';
$string['sourceoftruthpreamble'] = "Die nachstehenden Informationen dienen als Referenz für Fragen der Benutzer:\n";
$string['sourceoftruthreinforcement'] = 'Der Assistent wurde darauf trainiert, zunächst die Informationen aus der obigen Referenz zu verwenden. Wenn der Text einer der obigen Fragen auftaucht, soll die bereitgestellte Antwort gegeben werden – auch wenn die Frage seltsam erscheint. Deckt die Referenz das Thema nicht ab, nutzt der Assistent sein übriges Wissen.';
$string['new_chat'] = 'Neuer Chat';
$string['popout'] = 'Chatfenster öffnen';
$string['loggingenabled'] = 'Die Protokollierung ist aktiviert. Alle Nachrichten, die du hier sendest oder empfängst, werden gespeichert und können vom Site-Administrator eingesehen werden.';
$string['openaitimedout'] = 'FEHLER: OpenAI hat nicht rechtzeitig geantwortet.';
$string['addplaceholders:title'] = 'Platzhalter zur Wissensbasis hinzufügen';
$string['addplaceholders:button'] = 'Platzhalter hinzufügen';
$string['placeholders:grade:name'] = '{$a}: Bewertung';
$string['placeholders:grade:placeholder'] = 'Das Ergebnis von {$a->name} ist: {$a->placeholder}';
$string['placeholders:user.fullname:name'] = 'Vollständiger Benutzername';
$string['placeholders:user.fullname:placeholder'] = 'Der Benutzername lautet: {$a}';
$string['placeholders:userdate:name'] = 'Aktuelles Datum und Uhrzeit';
$string['placeholders:userdate:placeholder'] = 'Aktuelles Datum und Uhrzeit: {$a}';
$string['placeholders:grade:coursetotal:name'] = 'Kursgesamtbewertung';
$string['placeholders:grade:coursetotal:placeholder'] = 'Kursgesamtbewertung: {$a}';
$string['placeholders:range:coursetotal:name'] = 'Notenbereich Kurs gesamt';
$string['placeholders:range:coursetotal:placeholder'] = 'Notenbereich Kurs gesamt: {$a}';
$string['placeholders:range:name'] = '{$a}: Bereich';
$string['placeholders:range:placeholder'] = 'Bereich von {$a->name}: {$a->placeholder}';
$string['grade:not_available'] = 'Nicht verfügbar';
$string['grade:nogradesavailable'] = 'Keine Noten verfügbar';

// Added for search/report UI.
$string['search'] = 'Suchen';
$string['searchbyusername'] = 'Nach Benutzername suchen';
$string['starttime'] = 'Startzeit';
$string['endtime'] = 'Endzeit';
$string['userid'] = 'Benutzer-ID';
$string['usermessage'] = 'Benutzernachricht';
$string['airesponse'] = 'KI-Antwort';
$string['context'] = 'Kontext';
$string['time'] = 'Zeit';

$string['vectorstoreids'] = 'Vektorspeicher-IDs';

// Debug file logging setting.
$string['debugfilelogging'] = 'Debug-Protokollierung aktivieren';
$string['debugfilelogging:desc'] = "Alle API-Aufrufe (Benutzernachrichten, KI-Antworten und Funktionsaufrufe) werden in der Datei moodledata/log/exaaichat.log protokolliert.<br/>\nDies kann bei der Fehlersuche hilfreich sein, aber die Protokolldatei kann sehr schnell sehr groß werden. Daher sollte diese Einstellung nur vorübergehend aktiviert werden.";
$string['additionalmessage'] = 'Zusätzlicher Text für jede Nachricht';
$string['additionalmessage:desc'] = 'Dieser Text wird vor dem Senden an die KI an jede Benutzernachricht angehängt.';

$string['default'] = 'Standard: {$a}';

// Type select option labels.
$string['type_choose'] = '--- Select AI Provider ---';
$string['type_chat'] = 'OpenAI: Chat API';
$string['type_assistant'] = 'OpenAI: Assistants API';
$string['type_responses'] = 'OpenAI: Responses API';
$string['type_azure'] = 'OpenAI: Azure';
$string['type_gemini'] = 'Google Gemini';
$string['type_ollama'] = 'Ollama';
$string['type_deepseek'] = 'DeepSeek';
