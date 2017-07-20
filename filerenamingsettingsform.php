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
 * This file contains the form to enter the modalities of filerenaming
 *
 * @package       local
 * @subpackage    assignsubmission_download
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Günther Bernsteiner
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once('mtablepdf.php'); // For constants.

/**
 * Assignment grading options form
 *
 * @package   local_assignsubmission_download
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_assign_filerenaming_settings_form extends moodleform {
    /**
     * Define this form - called from the parent constructor
     */
    public function definition() {
        global $CFG, $OUTPUT;

        $mform = $this->_form;
        $instance = $this->_customdata;
        $dirtyclass = array('class' => 'ignoredirty');

        $mform->disable_form_change_checker();

        // Filerename-settings.
        $mform->addElement('header', 'filerenamesettings', get_string('filerenamesettingstitle', 'local_assignsubmission_download'));

        if ($instance['show_notreuploadable_hint']) {
            $mform->addElement('html', $OUTPUT->notification(get_string('notreuploadable_hint', 'local_assignsubmission_download'), 'info'));
        }

        $mform->addElement('text', 'filerenamingpattern', get_string('filerenamingpattern', 'local_assignsubmission_download'), '', PARAM_RAW_TRIMMED, 100);
        $mform->setType('filerenamingpattern', PARAM_RAW_TRIMMED);
        $mform->setDefault('filerenamingpattern', get_string('defaultfilerenamingpattern', 'local_assignsubmission_download'));
        $mform->addElement('static', 'filerenamingpattern_help', '', get_string('rename_propertydescription', 'local_assignsubmission_download'));
        $mform->addHelpButton('filerenamingpattern', 'filerenamingpattern', 'local_assignsubmission_download');

        $mform->addElement('advcheckbox', 'clean_filerenaming', get_string('clean_filerenaming', 'local_assignsubmission_download'), ' ');
        $mform->setDefault('clean_filerenaming', true);
        $mform->addHelpButton('clean_filerenaming', 'clean_filerenaming', 'local_assignsubmission_download');

        // Hidden params.
        $mform->addElement('hidden', 'contextid', $instance['contextid']);
        $mform->setType('contextid', PARAM_INT);
        $mform->addElement('hidden', 'id', $instance['cm']->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'userid', $instance['userid']);
        $mform->setType('userid', PARAM_INT);
        $mform->addElement('hidden', 'selectedusers', '', array('class' => 'selectedusers'));
        $mform->setType('selectedusers', PARAM_SEQUENCE);
        $mform->addElement('hidden', 'action', 'grading');
        $mform->setType('action', PARAM_ALPHA);

        // Button.
        $mform->addElement('submit', 'submittodownload', get_string('strfilerenaming', 'local_assignsubmission_download'));
    }
}