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
 * Function to rename files during zip download
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
defined('MOODLE_INTERNAL') || die();


const FILERENAMING_TAGS = ['[idnumber]', '[lastname]', '[firstname]', '[fullname]', '[assignmentname]', '[group]', '[filename]'];

/**
 * File rename function
 * Used by assign for renaming at upload or download of files
 *
 * @param String $original original filename
 * @param stdClass $user owner of the file
 * @param assign $assign assign instance the file belongs to
 * @param stdClass $submission submission containing the file
 * @param String $groupname assign team submission groupname
 * @param optional array $zipfiles array of filenames that must not be used in the same download
 */
function filerenaming_rename_file($prefixedfilename, $original, $user, $assign, $submission, $groupname, $zipfiles = null) {
    global $CFG;

    // Select filerenaming pattern out of (session|moodle default) in this order.
    $placeholders = ['[idnumber]', '[lastname]', '[firstname]', '[fullname]', '[assignmentname]', '[group]', '[filename]'];
    $filerenaminguserpref = get_user_preferences('filerenamingpattern', '');
    $o = '';
    if (ispatternvalid(FILERENAMING_TAGS, $filerenaminguserpref)) {
        // Use locally set filerenaming.
        $o = $filerenaminguserpref;
    } else {
        // Nothing to replace.
        return clean_custom($prefixedfilename);
    }

    // Reduce to a length of max 256, reserve three digits for existing files (max 999 equal filenames in db).
    $maxlength = 252;

    // Get filename and extension.
    $filename  = pathinfo($original, PATHINFO_FILENAME);
    $extension = pathinfo($original, PATHINFO_EXTENSION);

    $extension = ($extension != '') ? '.'.$extension : $extension;

    // Handle special double extension 'tar.gz' (ie. do not split it during file renaming).
    if ($extension == '.gz') {
        $tmpextension = pathinfo($filename, PATHINFO_EXTENSION);
        $tmpextension = ($tmpextension != '') ? '.'.$tmpextension : $tmpextension;
        $tmpfilename = pathinfo($filename, PATHINFO_FILENAME);
        if ($tmpextension == '.tar') {
            $extension = '.tar.gz';
            $filename = $tmpfilename;
        }
    }

    // Declare some variables.
    $assignmentname = $assign->get_instance()->name;
    $coursemodule   = $assign->get_course_module();

    // Assign a groupname if possible (existing and unique!)
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

    // Replace pattern.
    if ($assign->is_blind_marking()) {
        $blind = get_string('hiddenuser', 'local_filerenaming').'_'.$assign->get_uniqueid_for_user($user->id);

        $o = str_replace('[idnumber]', $blind, $o);
        $o = (strpos($o, $blind) === false) ? str_replace('[fullname]',  $blind, $o) : str_replace('[fullname]',  '', $o);
        $o = (strpos($o, $blind) === false) ? str_replace('[firstname]', $blind, $o) : str_replace('[firstname]', '', $o);
        $o = (strpos($o, $blind) === false) ? str_replace('[lastname]',  $blind, $o) : str_replace('[lastname]',  '', $o);
    } else {
        $o = str_replace('[idnumber]',  $user->idnumber, $o);
        $o = str_replace('[fullname]',  fullname($user), $o);
        $o = str_replace('[firstname]', $user->firstname, $o);
        $o = str_replace('[lastname]',  $user->lastname, $o);
    }

    $o = replace_custom($o, $maxlength, '[assignmentname]', $assignmentname);

    if (!$groupname || empty($groupname) || strcmp($groupname, '-') == 0) {
        $o = str_replace('[group]', '', $o);
    } else {
        $o = str_replace('[group]', $groupname, $o);
    }

    $o = replace_custom($o, $maxlength, '[filename]', $filename);

    // Check for existing files in download archive.
    $o = clean_custom($o);
    if (!empty($zipfiles)) {
        $temp = $o; $i = 1;
        while (array_key_exists($temp.$extension, $zipfiles)) {
            $temp = $o.'-'.$i++;
        }
        $o = $temp;
    }

    // Just to be sure clean_custom once more, should not be necessary.
    $o = clean_custom($o.$extension);
    return $o;
}

// Helper function to check if the given filerenaming string contains any acceptable pattern.
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

function filerenaming_clean_custom($filename) {
    return clean_custom($filename);
}

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

function clean_custom($filename) {
    global $CFG;

    $cleanfilenameuserpref = get_user_preferences('clean_filerenaming', '');

    if ((isset($cleanfilenameuserpref) && $cleanfilenameuserpref)) {
        $replace = array(
            'Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue', 'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 's', ' ' => '_'
        );

        $o = preg_replace('/[^A-Za-z0-9\_\-\.]/', '', strtr($filename, $replace));
        return clean_filename($o);
    } else {
        return clean_filename($filename);
    }
}