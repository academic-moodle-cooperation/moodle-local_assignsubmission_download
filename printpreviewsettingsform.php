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
 * This file contains the forms to edit the export settings within printpreview tab
 *
 * @package       local
 * @subpackage    assignsubmission_download
 * @author        Günther Bernsteiner
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once('mtablepdf.php'); // For constants.

/**
 * Printpreview form, to enter print settings and export options
 *
 * @package       local
 * @subpackage    assignsubmission_download
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_assign_printpreview_settings_form extends moodleform {
    /**
     * Define this form - called from the parent constructor
     */
    public function definition() {
        global $CFG, $PAGE;

        $mform = $this->_form;
        $instance = $this->_customdata;
        $dirtyclass = array('class' => 'ignoredirty');

        $mform->disable_form_change_checker();

        // Printsettings.
        $mform->addElement('header', 'printsettings', get_string('printsettingstitle', 'local_assignsubmission_download'));

        $options = array(MTablePDF::OUTPUT_FORMAT_PDF => 'PDF',
                         MTablePDF::OUTPUT_FORMAT_XLSX => 'XLSX',
                         MTablePDF::OUTPUT_FORMAT_ODS => 'ODS',
                         MTablePDF::OUTPUT_FORMAT_CSV_COMMA => 'CSV (;)',
                         MTablePDF::OUTPUT_FORMAT_CSV_TAB => 'CSV (tab)');
        $mform->addElement('select', 'exportformat',
                get_string('exportformat', 'local_assignsubmission_download'), $options, $dirtyclass);

        $mform->addElement('html', html_writer::div(html_writer::span(
                get_string('onlypdf', 'local_assignsubmission_download'), null), 'bold'));

        $grpperpage = array();
        $grpperpage[] =& $mform->createElement('text', 'perpage',
                get_string('perpage', 'local_assignsubmission_download'), 'size="3"');
        $mform->setType('perpage', PARAM_INT);
        $mform->setDefault('perpage', get_user_preferences('assign_perpage', $CFG->assignmentpatch_perpage));

        $grpperpage[] =& $mform->createElement('advcheckbox', 'optimum', '',
                get_string('optimum', 'local_assignsubmission_download'));
        $mform->setDefault('optimum', get_user_preferences('assign_optimum', 0));

        $mform->addGroup($grpperpage, 'grpperpage', get_string('assignmentsperpage', 'assign'), array(''), true);
        $mform->setType('grpperpage', PARAM_RAW);
        $mform->addHelpButton('grpperpage', 'perpage', 'local_assignsubmission_download');

        $options = array(0 => get_string('strsmall', 'local_assignsubmission_download'),
                         1 => get_string('strmedium', 'local_assignsubmission_download'),
                         2 => get_string('strlarge', 'local_assignsubmission_download'));
        $mform->addElement('select', 'textsize',
                get_string('strtextsize', 'local_assignsubmission_download'), $options, $dirtyclass);

        $options = array(0 => get_string('strportrait', 'local_assignsubmission_download'),
                         1 => get_string('strlandscape', 'local_assignsubmission_download'));
        $mform->addElement('select', 'pageorientation',
            get_string('strpageorientation', 'local_assignsubmission_download'), $options, $dirtyclass);

        $mform->addElement('advcheckbox', 'printheader', get_string('strprintheader', 'local_assignsubmission_download'), ' ');
        $mform->addHelpButton('printheader', 'strprintheader', 'local_assignsubmission_download');
        $mform->setDefault('printheader', true);

        // Datasettings.
        $mform->addElement('header', 'datasettings', get_string('datasettingstitle', 'local_assignsubmission_download'));

        $options = array('' => get_string('all', 'local_assignsubmission_download'),
                         ASSIGN_FILTER_SUBMITTED => get_string('filtersubmitted', 'assign'),
                         ASSIGN_FILTER_REQUIRE_GRADING => get_string('filterrequiregrading', 'assign'));
        if ($instance['submissionsenabled']) {
            $mform->addElement('select', 'filter', get_string('show', 'local_assignsubmission_download'), $options, $dirtyclass);
            $mform->setDefault('filter', get_user_preferences('assign_filter', ''));
        }

        MoodleQuickForm::registerElementType('groupsactivitymenu',
            $CFG->dirroot.'/local/assignsubmission_download/groupsactivitymenu.php', 'MoodleQuickForm_groupsactivitymenu');
        $groupsactivitymenu = $mform->addElement('groupsactivitymenu', 'group');
        $groupsactivitymenu->set_data($instance['cm'], $instance['currenturl']);

        $params = new stdClass();
        $PAGE->requires->js_call_amd('local_assignsubmission_download/printpreviewer', 'initializer', array($params));

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
        $mform->addElement('submit', 'submittoprint', get_string('strprint', 'local_assignsubmission_download'));

    }
}