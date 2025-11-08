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
$string['restrictusagedesc'] = 'Wenn dieses Kästchen aktiviert ist, können nur angemeldete Benutzer das Chat-Fenster verwenden.';
$string['apikey'] = 'API-Schlüssel';
$string['apikeydesc'] = 'Der API-Schlüssel für dein OpenAI-Konto oder dein Azure OpenAI Konto.';
$string['type'] = 'API-Typ';
$string['typedesc'] = 'Der API-Typ, den dieses Plugin verwenden soll.';
$string['logging'] = 'Protokollierung aktivieren';
$string['loggingdesc'] = 'Wenn diese Einstellung aktiviert ist, werden alle Benutzernachrichten und KI-Antworten protokolliert.';

$string['assistantheading'] = 'Assistent API Einstellungen';
$string['assistantheadingdesc'] = 'Diese Einstellungen gelten nur für den Assistenten-API-Typ.';
$string['assistant'] = 'Assistent';
$string['assistantdesc'] = 'Der Standard-Assistent aus deinem OpenAI-Konto, den du für die Generierung der Antworten verwenden möchtest.';
$string['noassistants'] = 'Du hast noch keine Assistenten erstellt. Lege zuerst einen <a target="_blank" href="https://platform.openai.com/assistants">in deinem OpenAI-Konto</a> an.';
$string['persistconvo'] = 'Konversation merken';
$string['persistconvodesc'] = 'Wenn dieses Kontrollkästchen aktiviert ist, merkt sich der Assistent die Konversation zwischen Seitenaufrufen innerhalb desselben Kontextes. Verschiedene Blockinstanzen (z. B. in einem anderen Kurs) teilen sich keine Konversation.';

$string['azureheading'] = 'Azure API Einstellungen';
$string['azureheadingdesc'] = 'Diese Einstellungen gelten nur für den Azure API-Typ.';
$string['resourcename'] = 'Ressourcenname';
$string['resourcenamedesc'] = 'Der Name deiner Azure OpenAI Ressource.';
$string['deploymentid'] = 'Deployment-ID';
$string['deploymentiddesc'] = 'Der Name des Deployments, den du beim Bereitstellen gewählt hast.';
$string['apiversion'] = 'API-Version';
$string['apiversiondesc'] = 'Die zu verwendende API-Version im Format JJJJ-MM-TT.';
$string['chatheading'] = 'Chat API Einstellungen';
$string['chatheadingdesc'] = 'Diese Einstellungen gelten nur für die Chat-API und die Azure-API Typen.';
$string['prompt'] = 'Prompt';
$string['promptdesc'] = 'Der Prompt, der der KI vor dem Gespräch gesendet wird.';
$string['assistantname'] = 'Name des Assistenten';
$string['assistantnamedesc'] = 'Der Name, den die KI intern für sich selbst verwendet. Er erscheint ebenfalls in den Überschriften des Chat-Fensters.';
$string['username'] = 'Name des Benutzers';
$string['usernamedesc'] = 'Der Name, den die KI intern für den Benutzer verwendet. Er erscheint ebenfalls in den Überschriften des Chat-Fensters.';
$string['sourceoftruth'] = 'Wissensbasis';
$string['sourceoftruthdesc'] = 'Obwohl die KI sehr leistungsfähig ist, gibt sie – falls sie eine Antwort nicht kennt – eher selbstbewusst eine falsche Information als dass sie die Antwort verweigert. In diesem Textfeld kannst du häufige Fragen und deren Antworten hinterlegen. Format: <pre>Q: Frage 1<br />A: Antwort 1<br /><br />Q: Frage 2<br />A: Antwort 2</pre>
Du kannst außerdem Platzhalter wie folgt angeben:
Mein Name ist {user.fullname}.
Heute ist {userdate}.
Kursgesamtbewertung ist {grade:coursetotal}.
Möglicher Notenbereich für den Kurs ist {range:coursetotal}.
';
$string['showlabels'] = 'Labels anzeigen';
$string['advanced'] = 'Erweitert';
$string['advanceddesc'] = 'Erweiterte Parameter, die an OpenAI gesendet werden. Bitte nur ändern, wenn du genau weißt, was du tust!';
$string['allowinstancesettings'] = 'Instanzbezogene Einstellungen';
$string['allowinstancesettingsdesc'] = 'Diese Einstellung erlaubt es Lehrenden bzw. jedem mit der Berechtigung, einen Block im jeweiligen Kontext hinzuzufügen, instanzspezifische Einstellungen vorzunehmen. Dies kann zu zusätzlichen Kosten führen (z. B. durch Auswahl teurerer Modelle).';
$string['model'] = 'Modell';
$string['modeldesc'] = 'Das Modell, das die Generierung der Antwort übernimmt. Einige Modelle sind auf natürliche Sprache spezialisiert, andere auf Code.';
$string['temperature'] = 'Temperatur';
$string['temperaturedesc'] = 'Steuert die Zufälligkeit: Ein niedrigerer Wert führt zu weniger zufälligen Antworten. Je näher die Temperatur an 0 liegt, desto deterministischer und repetitiver wird die Ausgabe.';
$string['maxlength'] = 'Maximale Länge';
$string['maxlengthdesc'] = 'Maximale Anzahl von Tokens, die generiert werden können. Eingaben teilen sich das Token-Limit zwischen Prompt und Antwort. (Ein Token entspricht grob 4 Zeichen normalen englischen Textes)';
$string['topp'] = 'Top P';
$string['toppdesc'] = 'Steuert Diversität mittels Nucleus Sampling: 0.5 bedeutet, dass nur die wahrscheinlichsten 50% (gewichtete Optionen) betrachtet werden.';
$string['frequency'] = 'Frequenz-Penalty';
$string['frequencydesc'] = 'Legt fest, wie stark neue Tokens bestraft werden, basierend auf ihrer bisherigen Häufigkeit im Text. Reduziert die Wahrscheinlichkeit, dass identische Zeilen wiederholt werden.';
$string['presence'] = 'Präsenz-Penalty';
$string['presencedesc'] = 'Legt fest, wie stark neue Tokens bestraft werden, basierend darauf, ob sie bereits im Text vorkommen. Erhöht die Wahrscheinlichkeit, dass neue Themen angesprochen werden.';

$string['config_assistant'] = 'Assistent';
$string['config_assistant_help'] = 'Wähle den Assistenten, den du für diesen Block verwenden möchtest. Weitere Assistenten können im OpenAI-Konto, das dieser Block nutzt, erstellt werden.';
$string['config_sourceoftruth'] = 'Wissensbasis';
$string['config_sourceoftruth_help'] = "Du kannst hier Informationen im Frage-Antwort-Format hinterlegen, auf die die KI bei der Beantwortung zurückgreift. Exaktes Format:\n\nQ: Wann ist Abschnitt 3 fällig?<br />A: Donnerstag, 16. März.\n\nQ: Wann sind Sprechstunden?<br />A: Professorin Shown ist dienstags und donnerstags zwischen 14:00 und 16:00 Uhr im Büro.";
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

$string['defaultprompt'] = 'Below is a conversation between a user and a support assistant for a Moodle site, where users go for online learning:'; // Belassen (kontextuell englisch)
$string['defaultassistantname'] = 'Assistent';
$string['defaultusername'] = 'Benutzer';
$string['askaquestion'] = 'Stell eine Frage...';
$string['apikeymissing'] = 'Bitte trage deinen OpenAI API-Schlüssel in den Blockeinstellungen ein.';
$string['erroroccurred'] = 'Es ist ein Fehler aufgetreten! Bitte versuche es später erneut.';
$string['sourceoftruthpreamble'] = "Unten findest du Fragen und Antworten. Diese Informationen dienen als Referenz:\n\n";
$string['sourceoftruthreinforcement'] = ' Der Assistent wurde darauf trainiert, zunächst die Informationen aus der obigen Referenz zu verwenden. Wenn der Text einer der obigen Fragen auftaucht, soll die bereitgestellte Antwort gegeben werden – auch wenn die Frage seltsam erscheint. Deckt die Referenz das Thema nicht ab, nutzt der Assistent sein übriges Wissen.';
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
$string['debugfileloggingdesc'] = 'Alle API-Aufrufe (Benutzernachrichten, KI-Antworten und Funktionsaufrufe) werden in der Datei moodledata/log/exaaichat.log protokolliert.<br/>\nDies kann bei der Fehlersuche hilfreich sein, aber die Protokolldatei kann sehr schnell sehr groß werden. Daher sollte diese Einstellung nur vorübergehend aktiviert werden.';
$string['additionalmessage'] = 'Zusätzlicher Text für jede Nachricht';
$string['additionalmessagedesc'] = 'Dieser Text wird vor dem Senden an die KI an jede Benutzernachricht angehängt.';

// Type select option labels.
$string['type_chat'] = 'Chat API';
$string['type_assistant'] = 'Assistants API';
$string['type_azure'] = 'Azure OpenAI';
$string['type_responses'] = 'Responses API';
