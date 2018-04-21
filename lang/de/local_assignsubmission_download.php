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
 * German lang file
 *
 * @package       local
 * @subpackage    assignsubmission_download
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        2012 Alwin Weninger
 * @author        2013 onwards Günther Bernsteiner
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Export- und Dateiumbenennung von Abgaben';
$string['pluginname_print'] = 'Export';
$string['pluginname_submissions'] = 'Alle Abgaben umbenannt herunterladen';

$string['perpage_propertyname'] = 'Default - Angezeigte Abgaben';
$string['perpage_propertydescription'] = 'Setzt den Defaultwert für die Anzahl der Abgaben die pro Seite angezeigt werden sollen, wenn Trainer/innen diese einsehen.'.
    '<br>Wird von den persönlichen Einstellungen der Trainer/innen überschrieben. Der Absolutwert der Eingabe wird gespeichert.';
$string['perpage_propertydefault'] = '100';

// Print preview assignment.
$string['printpreview'] = 'Export';
$string['submissions'] = 'Abgaben';

$string['show'] = 'Anzeigen';
$string['all'] = 'Alle';

$string['exportformat'] = 'Format';
$string['perpage'] = 'Abgaben pro Seite';
$string['perpage_help'] = 'Setzt den Defaultwert für die Anzahl der Abgaben, die pro Seite im pdf angezeigt werden sollen.
Wenn in Ihrem Kurs sehr viele Teilnehmer/innen eingeschrieben sind, können Sie mittels der Einstellung "Optimal" die Aufteilung der Listeneinträge pro Seite entsprechend der gewählten Schriftgröße und Seitenausrichtung optimieren.';
$string['optimum'] = 'Optimal';
$string['strtextsize'] = 'Textgrösse';
$string['strsmall'] = 'klein';
$string['strmedium'] = 'mittel';
$string['strlarge'] = 'groß';
$string['strprintheader'] = 'Kopf-/Fußzeile';

$string['printsettingstitle'] = 'Exporteinstellungen';
$string['onlypdf'] = 'PDF Einstellungen';

$string['stror'] = 'oder';
$string['strallononepage'] = 'alles auf eine Seite';
$string['strpageorientation'] = 'Seitenausrichtung';
$string['strportrait'] = 'Hochformat';
$string['strlandscape'] = 'Querformat';
$string['strpapersizes'] = 'Papierformat';
$string['strprint'] = 'Datei herunterladen';
$string['strprintheaderlong'] = 'inkludiere Kopf-/Fußzeilen';
$string['strprintheader_help'] = 'inkludiert Kopf-/Fußzeilen wenn angekreuzt';

$string['data_preview'] = 'Datenvorschau';
$string['data_preview_help'] = 'Klicken Sie in der Datenvorschau auf [+] und [-] um die zu druckenden Spalten ein bzw. auszuschalten!';
$string['datapreviewtitle'] = 'Datenvorschau';
$string['datasettingstitle'] = 'Dateneinstellungen';
$string['strrefreshdata'] = 'Vorschau aktualisieren';

// PDF.
$string['pdf_view'] = 'Druckansicht';
$string['pdf_course'] = 'Kurs';
$string['pdf_assignment'] = 'Aufgabe';
$string['pdf_availablefrom'] = 'Abgabebeginn';
$string['pdf_duedate'] = 'Abgabetermin';
$string['pdf_notactive'] = 'nicht aktiviert';
$string['pdf_group'] = 'Gruppe';
$string['pdf_nogroup'] = 'keine Gruppe';

// Event.
$string['printpreviewtableviewed'] = 'Exportansicht angezeigt';
$string['printpreviewtableviewed_description'] = 'Der Benutzer mit id {$a->userid} besuchte die Exportansicht für die Aufgabe '.
    'mit der Kursmodul id {$a->contextinstanceid}.';
$string['viewprintpreviewtable'] = 'Abgabentabelle anzeigen.';

$string['printpreviewtabledownloaded'] = 'Exportansicht heruntergeladen';
$string['printpreviewtabledownloaded_description'] = 'Der Benutzer mit id {$a->userid} lud die Exportansicht für die Aufgabe '.
    'mit der Kursmodul id {$a->contextinstanceid} herunter.';
$string['downloadprintpreviewtable'] = 'Abgabentabelle herunterladen.';

// Filerenaming.
$string['filerenamesettingstitle'] = 'Alle Abgaben umbenannt herunterladen';
$string['strfilerenaming'] = 'Abgaben herunterladen';

$string['filerenamingpattern'] = 'Namensschema';
$string['rename_propertydescription'] = 'Verfügbare Tags: {$a}';
$string['filerenamingpattern_help'] = 'Der Parameter \'Namensschema\' legt die Benennung der neuen Dateinamen fest. Für die Benennung stehen folgende Klammerausdrücke \'Tags\' zur Verfügung:<br>
    <br>
    [filename] ursprünglicher Dateiname<br>
    [firstname] Vorname <br>
    [lastname] Nachname <br>
    [fullname] Voller Name <br>
    [idnumber] Matrikelnummer<br>
    [assignmentname] Name der Aufgabe<br>
    [group] Gruppe, wenn Teilnehmer/in eingeschrieben<br>
    <br>
    Werden im Textfeld zusätzliche alphanumerische Zeichen eingetragen, so werden diese allen Dateien zugefügt.<br>
    <br>
    Beispiel:<br>
    Der Eintrag \'[idnumber]-[lastname]_[assignmentname]\' würde folgenden beispielhaften Dateinamen ergeben: \'01234567-Muster_Aufgabenname\'';
$string['clean_filerenaming'] = 'Bereinigte Dateinamen ';
$string['clean_filerenaming_help'] = 'Entfernt Leerzeichen und Sonderzeichen aus dem Dateinamen und schreibt Umlaute um, z.B.: \'Übung 1-Gruppe$4\' wird zu \'Uebung1-Gruppe4\'';
$string['onlinetext_defaultfilename'] = 'Onlinetext';
$string['hiddenuser'] = 'Teilnehmer/in';
$string['notreuploadable_hint'] = 'Hinweis: Beachten Sie, dass bei aktivierten Feedback-Typen \'Feedbackdateien\' bzw. \'Offline-Bewertungstabelle\' die umbenannten Abgaben dieser Seite nicht wieder hochgeladen werden können';

$string['defaultfilerenamingpattern'] = '[filename]';

$string['show_propertyname'] = '\'{$a->entrytoshow}\' anzeigen';
$string['show_propertydescription'] = 'Bestimmt, ob der \'{$a->entrytoshow}\' Menüeintrag in der Aufgabe angezeigt werden soll';

$string['userfilter'] = 'Benutzerfilter';