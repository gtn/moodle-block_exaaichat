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
$string['privacy:metadata:exaaichat_log'] = 'Protokollierte vom Benutzer and das KI-Backend gesendete Nachrichten. Enthält Benutzer-ID, Nachrichtentext, KI-Antwort und Zeitstempel.';
$string['privacy:metadata:exaaichat_log:userid'] = 'Die ID des Benutzers, der die Nachricht gesendet hat.';
$string['privacy:metadata:exaaichat_log:usermessage'] = 'Inhalt der Nachricht.';
$string['privacy:metadata:exaaichat_log:airesponse'] = 'Die Antwort des KI-Backends.';
$string['privacy:metadata:exaaichat_log:timecreated'] = 'Zeitpunkt des Sendens.';
$string['privacy:metadata:ai_api'] = 'Abhängig von der Konfiguration sendet Moodle bestimmte Daten an das KI-Backend (z. B. OpenAI) oder die KI kann Daten bei Moodle abrufen.';
$string['privacy:metadata:ai_api:fullname'] = 'Der vollständige Name des Benutzers, der die Nachricht gesendet hat.';
$string['privacy:metadata:ai_api:gradebook'] = 'Bewertungseinträge des aktuellen Kurses.';
$string['privacy:metadata:ai_api:actions'] = 'Die KI kann folgende Daten anfordern: Benutzerdetails (userid, username, firstname, lastname, email), eingeschriebene Kurse, Benutzerlisten der Kurse, alle Noten im aktuellen Kurs, Kursinhalte, Kurskategorien und zuletzt verwendete Kurse.';
$string['privacy:chatmessagespath'] = 'Gesendete KI-Chat-Nachrichten';
$string['downloadfilename'] = 'block_exaaichat_protokolle';

$string['blocktitle'] = 'Blocktitel';

$string['restrictusage'] = 'Verwendung auf angemeldete Nutzer beschränken';
$string['restrictusagedesc'] = 'Wenn aktiviert, können nur angemeldete Benutzer den Chat benutzen.';
$string['apikey'] = 'API-Schlüssel';
$string['apikeydesc'] = 'Der API-Schlüssel für dein OpenAI- oder Azure-Konto.';
$string['type'] = 'API-Typ';
$string['typedesc'] = 'Der API-Typ, den das Plugin verwenden soll.';
$string['logging'] = 'Protokollierung aktivieren';
$string['loggingdesc'] = 'Wenn aktiv, werden alle Benutzernachrichten und KI-Antworten protokolliert.';

$string['assistantheading'] = 'Assistent API Einstellungen';
$string['assistantheadingdesc'] = 'Diese Einstellungen gelten nur für den Assistenten-API-Typ.';
$string['assistant'] = 'Assistent';
$string['assistantdesc'] = 'Der Standardassistent aus deinem OpenAI-Konto, der für Antworten verwendet wird.';
$string['noassistants'] = 'Du hast noch keine Assistenten erstellt. Lege zuerst einen <a target="_blank" href="https://platform.openai.com/assistants">in deinem OpenAI-Konto</a> an.';
$string['persistconvo'] = 'Konversation merken';
$string['persistconvodesc'] = 'Wenn aktiviert, merkt sich der Assistent die Unterhaltung über Seitenaufrufe hinweg im gleichen Kontext (nicht zwischen verschiedenen Kursen).';

$string['azureheading'] = 'Azure API Einstellungen';
$string['azureheadingdesc'] = 'Diese Einstellungen gelten nur für den Azure API-Typ.';
$string['resourcename'] = 'Ressourcenname';
$string['resourcenamedesc'] = 'Der Name deiner Azure OpenAI Ressource.';
$string['deploymentid'] = 'Deployment-ID';
$string['deploymentiddesc'] = 'Der Name des Deployments, den du beim Bereitstellen gewählt hast.';
$string['apiversion'] = 'API-Version';
$string['apiversiondesc'] = 'Die zu verwendende API-Version im Format JJJJ-MM-TT.';
$string['chatheading'] = 'Chat API Einstellungen';
$string['chatheadingdesc'] = 'Diese Einstellungen gelten für Chat- und Azure-API-Typen.';
$string['prompt'] = 'Prompt';
$string['promptdesc'] = 'Der Prompt, der der KI vor dem Gespräch gesendet wird.';
$string['assistantname'] = 'Name des Assistenten';
$string['assistantnamedesc'] = 'Interner Name, den die KI für sich selbst nutzt (auch UI-Überschrift).';
$string['username'] = 'Name des Benutzers';
$string['usernamedesc'] = 'Interner Name, den die KI für den Benutzer nutzt (auch UI-Überschrift).';
$string['sourceoftruth'] = 'Wissensbasis';
$string['sourceoftruthdesc'] = 'Obwohl die KI sehr leistungsfähig ist, gibt sie bei Unklarheit eher selbstbewusst falsche Antworten. Hier kannst du häufige Fragen und Antworten hinterlegen. Format: <pre>Q: Frage 1<br />A: Antwort 1<br /><br />Q: Frage 2<br />A: Antwort 2</pre>
Du kannst auch Platzhalter verwenden:
Mein Name ist {user.fullname}.
Heute ist {userdate}.
Kursgesamtbewertung ist {grade:coursetotal}.
Möglicher Notenbereich für den Kurs ist {range:coursetotal}.
';
$string['showlabels'] = 'Labels anzeigen';
$string['advanced'] = 'Erweitert';
$string['advanceddesc'] = 'Erweiterte Parameter für OpenAI. Nur ändern, wenn du weißt, was du tust!';
$string['allowinstancesettings'] = 'Instanzbezogene Einstellungen';
$string['allowinstancesettingsdesc'] = 'Erlaubt es, dass Block-Instanzen eigene Einstellungen haben, und dass Lehrer, oder jede Person mit Rechten den Block hinzuzufügen, diese Einstellungen ändern kann. Kann zu höheren Kosten führen.';
$string['model'] = 'Modell';
$string['modeldesc'] = 'Das Modell, das die Antwort generiert. Einige Modelle sind für natürliche Sprachaufgaben geeignet, andere spezialisieren sich auf Code.';
$string['temperature'] = 'Temperatur';
$string['temperaturedesc'] = 'Steuert Zufälligkeit: Niedriger = deterministischer. Höher = kreativer.';
$string['maxlength'] = 'Maximale Länge';
$string['maxlengthdesc'] = 'Maximale Tokenanzahl pro Anfrage (Prompt + Antwort). Ein Token entspricht etwa 4 Zeichen normalem englischen Text.';
$string['topp'] = 'Top P';
$string['toppdesc'] = 'Diversität via Nucleus Sampling; 0.5 = 50% der kumulativen Wahrscheinlichkeit werden berücksichtigt.';
$string['frequency'] = 'Frequenz penalty';
$string['frequencydesc'] = 'Reduziert Wiederholungen bereits genutzter Tokens.';
$string['presence'] = 'Presence penalty';
$string['presencedesc'] = 'Erhöht Wahrscheinlichkeit, neue Themen anzusprechen.';

$string['config_assistant'] = 'Assistent';
$string['config_assistant_help'] = 'Wähle den Assistenten für diesen Block. Weitere Assistenten können im OpenAI-Konto erstellt werden.';
$string['config_sourceoftruth'] = 'Wissensbasis';
$string['config_sourceoftruth_help'] = "Informationen im Frage-Antwort-Format, die die KI zur Beantwortung nutzt. Format:\n\nQ: Wann ist Abschnitt 3 fällig?<br />A: Donnerstag, 16. März.\n\nQ: Wann sind Sprechstunden?<br />A: Professorin Schumpeter ist dienstags und donnerstags 14:00–16:00 Uhr im Büro.";
$string['config_instructions'] = 'Benutzerdefinierte Anweisungen';
$string['config_instructions_help'] = 'Überschreibt die Standard-Anweisungen des Assistenten.';
$string['config_prompt'] = 'Prompt';
$string['config_prompt_help'] = 'Prompt, der der KI vor dem Gespräch gesendet wird. Standard: "Below is a conversation ..." Wenn leer, wird der globale Prompt genutzt.';
$string['config_username'] = 'Benutzername';
$string['config_username_help'] = 'Name, den die KI für den Benutzer verwendet (oder globaler Standard).';
$string['config_assistantname'] = 'Assistentenname';
$string['config_assistantname_help'] = 'Name, den die KI für sich verwendet (oder globaler Standard).';
$string['config_persistconvo'] = 'Konversation merken';
$string['config_persistconvo_help'] = 'Merkt sich die Unterhaltung dieser Blockinstanz zwischen Seitenaufrufen.';
$string['config_apikey'] = 'API-Schlüssel';
$string['config_apikey_help'] = 'Optionaler API-Schlüssel für diese Instanz (überschreibt globalen).';
$string['config_model'] = 'Modell';
$string['config_model_help'] = 'Das Modell, das die Antwort generiert.';
$string['config_temperature'] = 'Temperatur';
$string['config_temperature_help'] = 'Steuert Zufälligkeit (niedriger = konsistenter).';
$string['config_maxlength'] = 'Maximale Länge';
$string['config_maxlength_help'] = 'Maximale Tokenanzahl für generierte Antwort (inkl. Prompt).';
$string['config_topp'] = 'Top P';
$string['config_topp_help'] = 'Diversität via Nucleus Sampling.';
$string['config_frequency'] = 'Frequenzstrafe';
$string['config_frequency_help'] = 'Verringert Wiederholung genau gleicher Zeilen.';
$string['config_presence'] = 'Themenstrafe';
$string['config_presence_help'] = 'Ermutigt, neue Themen einzubringen.';

$string['defaultprompt'] = 'Below is a conversation between a user and a support assistant for a Moodle site, where users go for online learning:'; // Belassen (kontextuell englisch)
$string['defaultassistantname'] = 'Assistent';
$string['defaultusername'] = 'Benutzer';
$string['askaquestion'] = 'Stell eine Frage...';
$string['apikeymissing'] = 'Bitte trage deinen OpenAI API-Schlüssel in den Blockeinstellungen ein.';
$string['erroroccurred'] = 'Es ist ein Fehler aufgetreten! Bitte versuche es später erneut.';
$string['sourceoftruthpreamble'] = "Unten findest du Fragen und Antworten. Diese Informationen dienen als Referenz:\n\n";
$string['sourceoftruthreinforcement'] = ' Der Assistent versucht zuerst, die obigen Referenzinformationen zu nutzen. Falls eine Frage darin vorkommt, wird die vorgegebene Antwort verwendet. Fehlt ein Thema, nutzt der Assistent sein anderes Wissen.';
$string['new_chat'] = 'Neuer Chat';
$string['popout'] = 'Chatfenster öffnen';
$string['loggingenabled'] = 'Protokollierung aktiv. Gesendete und empfangene Nachrichten werden gespeichert und können vom Administrator eingesehen werden.';
$string['openaitimedout'] = 'FEHLER: OpenAI hat nicht rechtzeitig geantwortet.';
$string['addplaceholders:title'] = 'Platzhalter zur Wissensbasis hinzufügen';
$string['addplaceholders:button'] = 'Platzhalter hinzufügen';
$string['placeholders:grade:name'] = 'Ergebnis von {$a}';
$string['placeholders:grade:placeholder'] = 'Das Ergebnis von {$a->name} ist: {$a->placeholder}';
$string['placeholders:user.fullname:name'] = 'Vollständiger Benutzername';
$string['placeholders:user.fullname:placeholder'] = 'Der Benutzername lautet: {$a}';
$string['placeholders:userdate:name'] = 'Aktuelles Datum und Uhrzeit';
$string['placeholders:userdate:placeholder'] = 'Aktuelles Datum und Uhrzeit: {$a}';
$string['placeholders:grade:coursetotal:name'] = 'Kursgesamtbewertung';
$string['placeholders:grade:coursetotal:placeholder'] = 'Kursgesamtbewertung: {$a}';
$string['placeholders:range:coursetotal:name'] = 'Notenbereich Kursgesamt';
$string['placeholders:range:coursetotal:placeholder'] = 'Notenbereich Kursgesamt: {$a}';
$string['placeholders:range:name'] = 'Bereich von {$a}';
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
$string['debugfileloggingdesc'] = 'Alle API-Aufrufe (Benutzernachrichten, KI-Antworten und Funktionsaufrufe) werden in moodledata/log/exaaichat.log gespeichert.';

// Additional message (responses type) setting.
$string['additionalmessage'] = 'Zusätzlicher Text für jede Nachricht';
$string['additionalmessagedesc'] = 'Dieser Text wird an jede Benutzernachricht angehängt, bevor sie an die KI gesendet wird.';

// Type select option labels.
$string['type_chat'] = 'Chat API';
$string['type_assistant'] = 'Assistants API'; // Belassen zwecks Wiedererkennung
$string['type_azure'] = 'Azure OpenAI';
$string['type_responses'] = 'Responses API'; // Belassen
