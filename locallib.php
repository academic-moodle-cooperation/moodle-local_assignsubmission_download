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
 * Functions to rename files during zip download
 *
 * @package       local_assignsubmission_download
 * @author        Alwin Weninger
 * @author        Günther Bernsteiner
 * @author        Andreas Krieger
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * List of tags that can be used in the filerenaming pattern.
 */
const FILERENAMING_TAGS = ['[idnumber]', '[lastname]', '[firstname]', '[fullname]', '[group]', '[groupid]', '[filename]',
    '[filenumber]', '[assignmentname]', '[courseshortname]', '[currentdate]', '[currenttime]',
    '[lastnamephonetic]', '[firstnamephonetic]', '[username]', '[alternatename]',
];
/**
 * List of tags that can be used in the ziprenaming pattern.
 * @var array
 */
const ZIPRENAMING_TAGS = ['[assignmentname]', '[assignmentid]', '[courseshortname]', '[currentdate]', '[currenttime]',
];

/**
 * File rename function
 * Used by assign for renaming at upload or download of files
 *
 * @param string $prefixedfilename prefixed filename
 * @param string $original original filename
 * @param stdClass $user owner of the file
 * @param assign $assign assign instance the file belongs to
 * @param stdClass $submission submission containing the file
 * @param string $groupname assign team submission groupname
 * @param int $sequence sequence number of the file
 * @param ?string[] $zipfiles array of filenames that must not be used in the same download
 * @param bool $preventprefix if true, the prefix will not be added to the filename
 * @return string The renamed filename
 */
function filerenaming_rename_file(
    $prefixedfilename,
    $original,
    $user,
    $assign,
    $submission,
    $groupname,
    $sequence,
    $zipfiles = null,
    $preventprefix = false
) {
    $filerenaminguserpref = get_user_preferences('filerenamingpattern', '');
    $o = '';
    if ($preventprefix && ispatternvalid(FILERENAMING_TAGS, $filerenaminguserpref)) {
        $o = $filerenaminguserpref;
    } else {
        // Use locally set filerenaming.
        $o = $preventprefix ? $filerenaminguserpref : $filerenaminguserpref . $prefixedfilename;
    }

    // Reduce to a length of max 256, reserve three digits for existing files (max 999 equal filenames in db).
    $maxlength = 252;

    // Get filename and extension.
    $filename  = pathinfo($original, PATHINFO_FILENAME);
    $extension = pathinfo($original, PATHINFO_EXTENSION);

    $extension = ($extension != '') ? '.' . $extension : $extension;

    // Handle special double extension 'tar.gz' (ie. do not split it during file renaming).
    if ($extension == '.gz') {
        $tmpextension = pathinfo($filename, PATHINFO_EXTENSION);
        $tmpextension = ($tmpextension != '') ? '.' . $tmpextension : $tmpextension;
        $tmpfilename = pathinfo($filename, PATHINFO_FILENAME);
        if ($tmpextension == '.tar') {
            $extension = '.tar.gz';
            $filename = $tmpfilename;
        }
    }

    // Declare some variables.
    $assignmentname = $assign->get_instance()->name;
    $coursemodule   = $assign->get_course_module();

    // Assign a groupname if possible (existing and unique!).
    if ($groupname == '' && groups_get_activity_groupmode($coursemodule)) {
        $usergroups = groups_get_all_groups($coursemodule->course, $user->id, $coursemodule->groupingid);
        if (count($usergroups) == 1) {
            foreach ($usergroups as $g) {
                $groupname = $g->name;
            }
        }
    } else {
        $groupname = substr($groupname, 0, -1);
    }

    // Get group idnumber if existing.
    $groupidnumber = '';

    if ($groupname != '') {
        $groupid = groups_get_group_by_name($coursemodule->course, $groupname);
        if ($groupid) {
            $group = groups_get_group($groupid);
            if ($group) {
                $groupidnumber = $group->idnumber; // Get the idnumber from the group.
            }
        }
    }

    // Get course shortname.
    $courseshortname = $assign->get_course()->shortname;

    // Get current time HHMM and date YYYYMMDD.
    $now = time();
    $currenttime = userdate($now, '%H%M', 99, false, false);
    $currentdate = date('Ymd');

    // Replace pattern.
    if ($assign->is_blind_marking()) {
        $blind = get_string('hiddenuser', 'local_assignsubmission_download') . '_' . $assign->get_uniqueid_for_user($user->id);

        $o = str_replace('[idnumber]', $blind, $o);
        $o = (strpos($o, $blind) === false) ? str_replace('[fullname]', $blind, $o) : str_replace('[fullname]', '', $o);
        $o = (strpos($o, $blind) === false) ? str_replace('[firstname]', $blind, $o) : str_replace('[firstname]', '', $o);
        $o = (strpos($o, $blind) === false) ? str_replace('[lastname]',  $blind, $o) : str_replace('[lastname]',  '', $o);
        $o = (strpos($o, $blind) === false) ? str_replace('[username]',  $blind, $o) : str_replace('[username]',  '', $o);
        $o = (strpos($o, $blind) === false) ? str_replace('[alternatename]',  $blind, $o) : str_replace('[alternatename]',  '', $o);
        $o = (strpos($o, $blind) === false) ?
            str_replace('[firstnamephonetic]', $blind, $o) : str_replace('[firstnamephonetic]', '', $o);
        $o = (strpos($o, $blind) === false) ?
            str_replace('[lastnamephonetic]',  $blind, $o) : str_replace('[lastnamephonetic]',  '', $o);
    } else {
        $o = str_replace('[idnumber]', $user->idnumber, $o);
        $o = str_replace('[fullname]', fullname($user), $o);
        $o = str_replace('[firstname]', $user->firstname, $o);
        $o = str_replace('[lastname]',  $user->lastname, $o);
        $o = str_replace('[username]',  $user->username, $o);
        $o = str_replace('[alternatename]',  $user->alternatename, $o);
        $o = str_replace('[firstnamephonetic]', $user->firstnamephonetic, $o);
        $o = str_replace('[lastnamephonetic]',  $user->lastnamephonetic, $o);
    }

    $o = replace_custom($o, $maxlength, '[assignmentname]', $assignmentname);
    $o = replace_custom($o, $maxlength, '[filenumber]', sprintf('%02d', $sequence));

    if (!$groupname || empty($groupname) || strcmp($groupname, '-') == 0) {
        $o = str_replace('[group]', '', $o);
        $o = str_replace('[groupid]', '', $o);
    } else {
        $o = str_replace('[group]', $groupname, $o);
        $o = str_replace('[groupid]', $groupidnumber, $o);
    }

    $o = replace_custom($o, $maxlength, '[filename]', $filename);
    $o = replace_custom($o, $maxlength, '[courseshortname]', $courseshortname);

    $o = replace_custom($o, $maxlength, '[currenttime]', $currenttime);
    $o = replace_custom($o, $maxlength, '[currentdate]', $currentdate);

    // Check for existing files in download archive.
    $o = clean_custom($o);
    if (!empty($zipfiles)) {
        $temp = $o;
        $i = 1;
        while (array_key_exists($temp . $extension, $zipfiles)) {
            $temp = $o . '-' . $i++;
        }
        $o = $temp;
    }

    // Just to be sure clean_custom once more, should not be necessary.
    $o = clean_custom($o . $extension);
    return $o;
}

/**
 * Helper function to check if the given filerenaming string contains any acceptable pattern.
 *
 * @param string[] $acceptedplaceholders accepted placeholder patterns to test string against
 * @param string $teststring tested filerenaming string
 * @return bool True if the pattern is valid, false otherwise
 */
function ispatternvalid($acceptedplaceholders, $teststring) {
    $isvalidpattern = false;
    foreach ($acceptedplaceholders as $placeholder) {
        if (stripos($teststring, $placeholder) === false) {
            continue;
        } else {
            $isvalidpattern = true;
            break;
        }
    }
    return $isvalidpattern;
}

/**
 * Helper function to replace a pattern with a string up to a certain lenght
 * due to file name length restrictions.
 *
 * @param string $o full string
 * @param int $maxlength replace up to maxlength
 * @param string $pattern pattern which to replace
 * @param string $string string to replace pattern against
 * @return string the full string with pattern replaced by string, up to maxlength
 */
function replace_custom($o, $maxlength, $pattern, $string) {
    $temp = str_replace($pattern, $string, $o);
    if (strlen($temp) < $maxlength) {
        $o = $temp;
    } else {
        $string = substr($string, 0, $maxlength - (strlen($o) - strlen($pattern)));
        $o = str_replace($pattern, $string, $o);
    }
    return $o;
}

/**
 * Helper function to replace umlauts with regular characters in filenames.
 * Also replaces spaces with underscores. If clean_filerenaming is set, also
 * removes any other special characters.
 *
 * @param string $filename filename which to clean up
 * @return string the clean filename, without special characters
 */
function clean_custom($filename) {
    $replace = [
        'Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue', 'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 's', ' ' => '_',
    ];
    $filename = str_replace(array_keys($replace), array_values($replace), $filename);

    $cleanfilenameuserpref = get_user_preferences('clean_filerenaming', '');
    if ((isset($cleanfilenameuserpref) && $cleanfilenameuserpref)) {
        $filename = core_text::specialtoascii($filename);
        $filename = preg_replace('/[^A-Za-z0-9\_\-\.]/', '_', $filename);
    }
    return clean_filename($filename);
}

/**
 * ZIP archive rename function
 * Function to rename the zip archive name based on the user name and pattern
 *
 * @param assign $assign assign instance the files inside the zip archive belong to
 */
function ziprenaming_rename_zip_archive($assign) {
    $ziprenaminguserpref = get_user_preferences('nameofziparchive', '');
    $o = '';

    // Declare the variables to replace.
    $assignmentname = $assign->get_instance()->name;
    $assignmentid = $assign->get_course_module()->id;
    $courseshortname = $assign->get_course()->shortname;
    $currentdate = date('Ymd');
    $currenttime = userdate(time(), '%H%M', 99, false, false);

    // Replace pattern.
    $o = str_replace('[assignmentname]', $assignmentname, $ziprenaminguserpref);
    $o = str_replace('[assignmentid]', $assignmentid, $o);
    $o = str_replace('[courseshortname]', $courseshortname, $o);
    $o = str_replace('[currentdate]', $currentdate, $o);
    $o = str_replace('[currenttime]', $currenttime, $o);

    // Cleaned name and added .zip extension.
    $o = clean_custom($o);
    $o = $o . '.zip';
    return $o;
}
