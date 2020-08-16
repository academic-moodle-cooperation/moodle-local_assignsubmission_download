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
 * English lang file
 *
 * @package       local
 * @subpackage    assignsubmission_download
 * @author        2012 Alwin Weninger
 * @author        2013 onwards Günther Bernsteiner
 * @author        Andreas Krieger
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Export- and file renaming of submissions';
$string['pluginname_print'] = 'Export';
$string['pluginname_submissions'] = 'Download renamed submissions';

$string['perpage_propertyname'] = 'Default - Displayed Submissions';
$string['perpage_propertydescription'] = 'This sets the number of submissions which are displayed per page, when teachers are viewing assignment submissions.'.
    '<br>It is overwritten by the teacher\'s individual preferences. Input will be absolute-valued.';
$string['perpage_propertydefault'] = '100';

// Print preview assignment.
$string['printpreview'] = 'Export';
$string['submissions'] = 'Submissions';

$string['show'] = 'Show';
$string['all'] = 'All';

$string['exportformat'] = 'Format';
$string['perpage'] = 'Submissions shown per page';
$string['perpage_help'] = 'This sets the number of submissions which are displayed per page in the pdf.
Choose "Optimum" to optimize the distribution of list entries according to the chosen textsize and page orientation, if there are plenty of participants registered in your course.';
$string['optimum'] = 'Optimum';
$string['strtextsize'] = 'Textsize';
$string['strsmall'] = 'small';
$string['strmedium'] = 'medium';
$string['strlarge'] = 'large';
$string['strprintheader'] = 'Print header/footer';

$string['printsettingstitle'] = 'Export settings';
$string['onlypdf'] = 'PDF Settings';

$string['stror'] = 'or';
$string['strallononepage'] = 'print all on one page';
$string['strpageorientation'] = 'Page orientation';
$string['strportrait'] = 'portrait';
$string['strlandscape'] = 'landscape';
$string['strpapersizes'] = 'Papersize';
$string['strprint'] = 'Download file';
$string['strprintheaderlong'] = 'print header and footer if checked';
$string['strprintheader_help'] = 'Prints header and footer if checked';

$string['data_preview'] = 'Data preview';
$string['data_preview_help'] = 'Click on [+] or [-] for showing or hiding columns in the print-preview.';
$string['datapreviewtitle'] = 'Datenvorschau';
$string['datasettingstitle'] = 'Data settings';
$string['strrefreshdata'] = 'Update data preview';

// PDF.
$string['pdf_view'] = 'Print preview';
$string['pdf_course'] = 'Course';
$string['pdf_assignment'] = 'Assignment';
$string['pdf_availablefrom'] = 'Available from';
$string['pdf_duedate'] = 'Due date';
$string['pdf_notactive'] = 'not activated';
$string['pdf_group'] = 'Group';
$string['pdf_nogroup'] = 'keine Gruppe';

// Event.
$string['printpreviewtableviewed'] = 'Export table viewed';
$string['printpreviewtableviewed_description'] = 'The user with id {$a->userid} viewed the export table for the assignment '.
    'with the course module id {$a->contextinstanceid}.';
$string['viewprintpreviewtable'] = 'View submission export table.';

$string['printpreviewtabledownloaded'] = 'Export table downloaded';
$string['printpreviewtabledownloaded_description'] = 'The user with id {$a->userid} downloaded the export table for the assignment '.
    'with the course module id {$a->contextinstanceid}.';
$string['downloadprintpreviewtable'] = 'Download submission export table.';

// Filerenaming.
$string['filerenamesettingstitle'] = 'Download renamed submissions';
$string['strfilerenaming'] = 'Download submissions';

$string['filerenamingpattern'] = 'Naming scheme';
$string['rename_propertydescription'] = 'Available tags: {$a}';
$string['filerenamingpattern_help'] = 'The parameter \'naming scheme\' determines the naming of the filenames. The following bracket terms (\'tags\') are available:<br>
    <br>
    [filename] original filename<br>
    [firstname] first name<br>
    [lastname] last name<br>
    [fullname] full name<br>
    [idnumber] matriculation number<br>
    [assignmentname] name of the assignment<br>
    [group] group, in case the participant is enroled into a group<br>
    <br>
    If you add any alphanumeric characters (without brackets), these characters will be added to all the uploaded/downloaded assignments<br>
    <br>
    Example:<br>
    The entry \'[idnumber]-[lastname]_[assignmentname]\' will result the following filename: \'01234567-Muster_assignmentname\'';
$string['clean_filerenaming'] = 'Clean filenames';
$string['clean_filerenaming_help'] = 'Removes white spaces and special characters from filenames and rewrites umlauts, e.g. \'Übung 1-Gruppe$4\' becomes \'Uebung1-Gruppe\'';
$string['onlinetext_defaultfilename'] = 'Onlinetext';
$string['hiddenuser'] = 'Participant';
$string['notreuploadable_hint'] = 'Note, if one of the feedback types \'Feedback files\' or \'Offline grading worksheet\' is checked, the renamed download files of this page cannot be uploaded anymore.';

$string['defaultfilerenamingpattern'] = '[filename]';

$string['show_propertyname'] = 'Show \'{$a->entrytoshow}\'';
$string['show_propertydescription'] = 'Used to show or hide the \'{$a->entrytoshow}\' menu entry';

$string['userfilter'] = 'User filter';

$string['labelgroup'] = 'Focus download on group';
$string['labelgroup_help'] = 'Download assignments of students from a specific group only.';

$string['assignsubmission_download:view'] = 'Grant access to file renaming and submission export';

$string['labelgrouping'] = 'Focus download on grouping';
$string['labelgrouping_help'] = 'Download assignments of students from a specific group only.';

$string['submissionneweras'] = 'Submission newer as';

$string['privacy:metadata:preference:filerenamingpattern'] = 'Preference for the naming scheme used for file renaming on downloaded submissions.';
$string['privacy:metadata:preference:clean_filerenaming'] = 'Preference on whether to additionaly clean file names from special chars in downloaded submissions.';
$string['privacy:metadata:preference:userfilter'] = 'Preference on which users are filtered for when exporting to a file.';
$string['privacy:metadata:preference:exportformat'] = 'Preference on which format to use when exporting to a file.';
$string['privacy:metadata:preference:perpage'] = 'Preference on how many submissions to display per page when exporting to a pdf file.';
$string['privacy:metadata:preference:optimum'] = 'Preference on whether to automatically decide how many sumbissions to display per page when exporting to a pdf file.';
$string['privacy:metadata:preference:textsize'] = 'Preference on which text size to use when exporting to a pdf file.';
$string['privacy:metadata:preference:pageorientation'] = 'Preference on which page orientation to use when exporting to a pdf file.';
$string['privacy:metadata:preference:printheader'] = 'Preference on whether to print header and footer when exporting to a pdf file.';
