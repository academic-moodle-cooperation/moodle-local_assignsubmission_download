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
 * @package       local_assignsubmission_download
 * @author        2012 Alwin Weninger
 * @author        2013 onwards Günther Bernsteiner
 * @author        Andreas Krieger
 * @author        2024 Clemens Marx
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['all'] = 'All';
$string['assignsubmission_download:view'] = 'Grant access to file renaming and submission export';
$string['choosegradingaction'] = 'Grading action';
$string['clean_filerenaming'] = 'Clean filenames';
$string['clean_filerenaming_help'] = 'Removes special characters from filenames. White spaces and umlauts are always replaced, e.g. \'Übung 1-Gruppe$4\' becomes \'Uebung_1-Gruppe4\'';
$string['data_preview'] = 'Data preview';
$string['data_preview_help'] = 'Click on [+] or [-] for showing or hiding columns in the print-preview.';
$string['datapreviewtitle'] = 'Data preview';
$string['datasettingstitle'] = 'Data settings';
$string['dateinthefutureerror'] = 'Submission newer as cannot be in the future';
$string['defaultfilerenamingpattern'] = '[filename]';
$string['defaultziprenamingpattern'] = '[courseshortname]-[assignmentname]-[assignmentid]';
$string['downloadprintpreviewtable'] = 'Download submission export table.';
$string['downloadtype'] = 'Download';
$string['downloadtype:error'] = 'Please select at least one option';
$string['downloadtype:feedbacks'] = 'All feedback files from teachers';
$string['downloadtype:submissions'] = 'All file submissions from students';
$string['downloadtype_feedbacks'] = 'Download feedbacks';
$string['downloadtype_help'] = 'Here you can select which files you would like to download - files submitted by students and/or feedback from teachers (comments, annotated PDFs, feedback files)';
$string['downloadtype_submissions'] = 'Download submissions';
$string['exportformat'] = 'Format';
$string['filenumberinfo'] = 'Maximum number of uploaded files is larger than 1. Consider using [filenumber] to number all uploads of a student in order to improve sorting.';
$string['filerenamesettingstitle'] = 'Download renamed submissions';
$string['filerenamingpattern'] = 'Naming scheme';
$string['filerenamingpattern_help'] = 'The parameter \'naming scheme\' determines the naming of the filenames. The following bracket terms (\'tags\') are available:<br>
    <br>
    [idnumber] matriculation number<br>
    [lastname] last name<br>
    [firstname] first name<br>
    [fullname] full name<br>
    [group] group, in case the participant is enroled into a group<br>
    [groupid] group id, in case the participant is enroled into a group and it has an id<br>
    [filename] original filename<br>
    [filenumber] sequential number for files uploaded by a single student<br>
    [assignmentname] name of the assignment<br>
    [courseshortname] short name of the course<br>
    [currentdate] current date in format YYYYMMDD (YearMonthDay)<br>
    [currenttime] current time HHMM (HoursMinutes)<br>
    <br>
    If you add any alphanumeric characters (without brackets), these characters will be added to all the uploaded/downloaded assignments<br>
    <br>
    Example:<br>
    The entry \'[idnumber]-[lastname]_[assignmentname]\' will result the following filename: \'01234567-Muster_assignmentname\'';
$string['hiddenuser'] = 'Participant';
$string['labelgroup'] = 'Focus download on group';
$string['labelgroup_help'] = 'Download assignments of students from a specific group only.';
$string['labelgrouping'] = 'Focus download on grouping';
$string['labelgrouping_help'] = 'Download assignments of students from a specific group only.';
$string['lastdownloaded_title'] = 'User\'s last download submissions';
$string['lastdownloaded_title_help'] = 'The date shows the last download of the user of submissions from students.';
$string['lastdownloadedfeedbacks_title'] = 'User\'s last download feedback files';
$string['lastdownloadedfeedbacks_title_help'] = 'The date shows the last download of the user of feedback files from teachers.';
$string['lastfeedbackdownloadsettings'] = 'Last feedback file download settings';
$string['lastsubmissionsdownloadsettings'] = 'Last file submissions download settings';
$string['nameofziparchive'] = 'Name of the zip archive';
$string['nameofziparchive_help'] = 'Name of the zip archive which contains the downloaded files. The following bracket terms (\'tags\') are available:<br>
    <br>
    [assignmentname] name of the assignment<br>
    [assignmentid] id of the assignment<br>
    [courseshortname] short name of the course<br>
    [currentdate] current date in format YYYYMMDD (YearMonthDay)<br>
    [currenttime] current time HHMM (HoursMinutes)<br>
    <br>
    If you add any alphanumeric characters (without brackets), these characters will be added to the zip archive name<br>
    <br>
    Example:<br>
    The entry \'[courseshortname]-[assignmentname]_[currentdate]\' will result the following zip archive name: \'EC-exampleassignment_20240401\'';
$string['nodownloadsyet'] = 'no downloads yet';
$string['nosubmissionneweras'] = 'No submission was made after {$a}';
$string['notreuploadable_hint'] = 'Note, if one of the feedback types \'Feedback files\' or \'Offline grading worksheet\' is checked, the renamed download files of this page cannot be uploaded anymore.';
$string['onlinetext_defaultfilename'] = 'Onlinetext';
$string['onlypdf'] = 'PDF Settings';
$string['optimum'] = 'Optimum';
$string['pdf_assignment'] = 'Assignment';
$string['pdf_availablefrom'] = 'Available from';
$string['pdf_course'] = 'Course';
$string['pdf_duedate'] = 'Due date';
$string['pdf_group'] = 'Group';
$string['pdf_nogroup'] = 'keine Gruppe';
$string['pdf_notactive'] = 'not activated';
$string['pdf_view'] = 'Print preview';
$string['perpage'] = 'Submissions shown per page';
$string['perpage_help'] = 'This sets the number of submissions which are displayed per page in the pdf.
    Choose "Optimum" to optimize the distribution of list entries according to the chosen textsize and page orientation, if there are plenty of participants registered in your course.';
$string['perpage_propertydefault'] = '100';
$string['perpage_propertydescription'] = 'This sets the number of submissions which are displayed per page, when teachers are viewing assignment submissions.
    <br>It is overwritten by the teacher\'s individual preferences. Input will be absolute-valued.';
$string['perpage_propertyname'] = 'Default - Displayed Submissions';
$string['pluginname'] = 'Export- and file renaming of submissions';
$string['pluginname_print'] = 'Export';
$string['pluginname_submissions'] = 'Download renamed submissions';
$string['prevent_nameextension'] = 'Prevent automatic extension of file names';
$string['prevent_nameextension_help'] = 'This function prevents the automatic extension of the file namens (with terms such as "_Submission_File_submissions").';
$string['printpreview'] = 'Export';
$string['printpreviewtabledownloaded'] = 'Export table downloaded';
$string['printpreviewtabledownloaded_description'] = 'The user with id {$a->userid} downloaded the export table for the assignment with the course module id {$a->contextinstanceid}.';
$string['printpreviewtableviewed'] = 'Export table viewed';
$string['printpreviewtableviewed_description'] = 'The user with id {$a->userid} viewed the export table for the assignment with the course module id {$a->contextinstanceid}.';
$string['printsettingstitle'] = 'Export settings';
$string['privacy:metadata:local_assignsubm_download'] = 'Table for storing the information about the last download of submissions.';
$string['privacy:metadata:local_assignsubm_download:choosegroup'] = 'The group id of the group to filter for.';
$string['privacy:metadata:local_assignsubm_download:choosegrouping'] = 'The grouping id of the grouping to filter for.';
$string['privacy:metadata:local_assignsubm_download:cleanfilenames'] = 'Whether file names are cleaned from special chars.';
$string['privacy:metadata:local_assignsubm_download:cmid'] = 'The course module id of the assignment.';
$string['privacy:metadata:local_assignsubm_download:filenamingscheme'] = 'The filenaming scheme used for the fles for the download.';
$string['privacy:metadata:local_assignsubm_download:id'] = 'The unique identifier of the record.';
$string['privacy:metadata:local_assignsubm_download:lastdownloaded'] = 'The timestamp of the last download of submissions.';
$string['privacy:metadata:local_assignsubm_download:preventnameextension'] = 'Whether automatic extension of file names is prevented.';
$string['privacy:metadata:local_assignsubm_download:userid'] = 'The user id of the user who downloaded the submissions.';
$string['privacy:metadata:local_assignsubm_download:zipnamingscheme'] = 'The naming scheme used for the zip archive.';
$string['privacy:metadata:local_assignsubm_feedback'] = 'Table for storing the information about the last download of feedback.';
$string['privacy:metadata:local_assignsubm_feedback:choosegroup'] = 'The group id of the group to filter for.';
$string['privacy:metadata:local_assignsubm_feedback:choosegrouping'] = 'The grouping id of the grouping to filter for.';
$string['privacy:metadata:local_assignsubm_feedback:cleanfilenames'] = 'Whether file names are cleaned from special chars.';
$string['privacy:metadata:local_assignsubm_feedback:cmid'] = 'The course module id of the assignment.';
$string['privacy:metadata:local_assignsubm_feedback:filenamingscheme'] = 'The filenaming scheme used for the fles for the download.';
$string['privacy:metadata:local_assignsubm_feedback:id'] = 'The unique identifier of the record.';
$string['privacy:metadata:local_assignsubm_feedback:lastdownloaded'] = 'The timestamp of the last download of submissions.';
$string['privacy:metadata:local_assignsubm_feedback:preventnameextension'] = 'Whether automatic extension of file names is prevented.';
$string['privacy:metadata:local_assignsubm_feedback:userid'] = 'The user id of the user who downloaded the submissions.';
$string['privacy:metadata:local_assignsubm_feedback:zipnamingscheme'] = 'The naming scheme used for the zip archive.';
$string['privacy:metadata:preference:clean_filerenaming'] = 'Preference on whether to additionaly clean file names from special chars in downloaded submissions.';
$string['privacy:metadata:preference:downloadtype_feedbacks'] = 'Preference on whether to download feedbacks.';
$string['privacy:metadata:preference:downloadtype_submissions'] = 'Preference on whether to download submissions.';
$string['privacy:metadata:preference:exportformat'] = 'Preference on which format to use when exporting to a file.';
$string['privacy:metadata:preference:filerenamingpattern'] = 'Preference for the naming scheme used for file renaming on downloaded submissions.';
$string['privacy:metadata:preference:nameofziparchive'] = 'Preference on the name of the zip archive when downloading submissions or feedback.';
$string['privacy:metadata:preference:optimum'] = 'Preference on whether to automatically decide how many sumbissions to display per page when exporting to a pdf file.';
$string['privacy:metadata:preference:pageorientation'] = 'Preference on which page orientation to use when exporting to a pdf file.';
$string['privacy:metadata:preference:perpage'] = 'Preference on how many submissions to display per page when exporting to a pdf file.';
$string['privacy:metadata:preference:prevent_nameextension'] = 'Preference on whether to prevent automatic extension of file names when downloading submissions or feedback.';
$string['privacy:metadata:preference:printheader'] = 'Preference on whether to print header and footer when exporting to a pdf file.';
$string['privacy:metadata:preference:textsize'] = 'Preference on which text size to use when exporting to a pdf file.';
$string['privacy:metadata:preference:userfilter'] = 'Preference on which users are filtered for when exporting to a file.';
$string['rename_propertydescription'] = 'Available tags: {$a}';
$string['show'] = 'Show';
$string['show_propertydescription'] = 'Used to show or hide the \'{$a->entrytoshow}\' menu entry';
$string['show_propertyname'] = 'Show \'{$a->entrytoshow}\'';
$string['strallononepage'] = 'print all on one page';
$string['strfilerenaming'] = 'Download submissions';
$string['strlandscape'] = 'landscape';
$string['strlarge'] = 'large';
$string['strmedium'] = 'medium';
$string['stror'] = 'or';
$string['strpageorientation'] = 'Page orientation';
$string['strpapersizes'] = 'Papersize';
$string['strportrait'] = 'portrait';
$string['strprint'] = 'Download file';
$string['strprintheader'] = 'Print header/footer';
$string['strprintheader_help'] = 'Prints header and footer if checked';
$string['strprintheaderlong'] = 'print header and footer if checked';
$string['strrefreshdata'] = 'Update data preview';
$string['strsmall'] = 'small';
$string['strtextsize'] = 'Textsize';
$string['submissionneweras'] = 'Submission newer as';
$string['submissionneweras_help'] = 'Only download files that were modified after a given time. If a submissiontype does not have
    a timestamp (like onlinetext), the timestamp of the submission is used.';
$string['submissions'] = 'Submissions';
$string['userfilter'] = 'User filter';
$string['viewprintpreviewtable'] = 'View submission export table.';
