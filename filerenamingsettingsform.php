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
 * @author        Günther Bernsteiner
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once('mtablepdf.php'); // For constants.
require_once($CFG->dirroot.'/local/assignsubmission_download/locallib.php');

/**
 * Filerenaming form, to enter pattern and clean filename checkbox
 *
 * @package       local
 * @subpackage    assignsubmission_download
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_assign_filerenaming_settings_form extends moodleform {
    /**
     * Define this form - called from the parent constructor
     */
    public function definition() {
        global $CFG, $OUTPUT, $PAGE;

        $mform = $this->_form;
        $instance = $this->_customdata;
        $dirtyclass = array('class' => 'ignoredirty');

        $mform->disable_form_change_checker();

        // Filerename-settings.
        $mform->addElement('header', 'filerenamesettings',
                get_string('filerenamesettingstitle', 'local_assignsubmission_download'));

        if ($instance['show_notreuploadable_hint']) {
            $mform->addElement('html', $OUTPUT->notification(
                    get_string('notreuploadable_hint', 'local_assignsubmission_download'), 'info'));
        }


        $downloadtypegroup = [];

        $downloadtypegroup[] =&
            $mform->createElement('advcheckbox',
                'downloadtype_submissions',
                '',
                get_string('downloadtype:submissions', 'local_assignsubmission_download'),
                [], [0, 1]);

        $downloadtypegroup[] =&
            $mform->createElement('advcheckbox',
                'downloadtype_feedbacks',
                '',
                get_string('downloadtype:feedbacks', 'local_assignsubmission_download'),
                [], [0, 1]);

        $mform->addGroup($downloadtypegroup, 'downloadtype',
            get_string('downloadtype', 'local_assignsubmission_download'),
            '<br />',
            false);
        $mform->addHelpButton('downloadtype', 'downloadtype', 'local_assignsubmission_download');
        $mform->setDefault('downloadtype_submissions', 1);

        $mform->addRule('downloadtype', '', 'required', null, 'server');

        $tags = array();
        foreach (FILERENAMING_TAGS as $tag) {
            $tags[] = html_writer::tag('span', $tag, array('class' => 'nametag', 'data-nametag' => $tag));
        }

        $mform->addElement('text', 'filerenamingpattern',
                get_string('filerenamingpattern', 'local_assignsubmission_download'),  array('size' => '100'));
        $mform->setType('filerenamingpattern', PARAM_RAW_TRIMMED);
        $mform->setDefault('filerenamingpattern', get_string('defaultfilerenamingpattern', 'local_assignsubmission_download'));
        $mform->addElement('static', 'tags', '',
                get_string('rename_propertydescription', 'local_assignsubmission_download', implode("", $tags)));
        $mform->addHelpButton('filerenamingpattern', 'filerenamingpattern', 'local_assignsubmission_download');

        $PAGE->requires->js_call_amd('local_assignsubmission_download/filerenaming_tagsupport', 'initializer', array());

        $mform->addElement('advcheckbox', 'clean_filerenaming',
                get_string('clean_filerenaming', 'local_assignsubmission_download'), ' ');
        $mform->setDefault('clean_filerenaming', true);
        $mform->addHelpButton('clean_filerenaming', 'clean_filerenaming', 'local_assignsubmission_download');

        $cm = $PAGE->cm;
        $groupmode = groups_get_activity_groupmode($cm);

        $course = $PAGE->course;
        $activitygroupings = groups_get_all_groupings($course->id);

        $groupingallparticipants = new stdClass();
        $groupingallparticipants->name = get_string('allparticipants');
        $groupingallparticipants->id = "0";

        $groupallparticipants = new stdClass();
        $groupallparticipants->name = get_string('allparticipants');
        $groupallparticipants->id = "0";

        array_unshift($activitygroupings, $groupingallparticipants);
        $jsgroupings = array();
        if (($groupmode != NOGROUPS)) {
            $selectgrouping = $mform->createElement('select', 'coursegrouping',
                get_string('labelgrouping', 'local_assignsubmission_download'));
            foreach ($activitygroupings as $index => $curgrouping) {
                $selectgrouping->addOption($curgrouping->name, $curgrouping->id, null);
                $jsgroupings[$curgrouping->id] = new stdClass();
                $jsgroupings[$curgrouping->id]->name = $curgrouping->name;
                $jsgroupings[$curgrouping->id]->id = $curgrouping->id;

                $jsgroupings[$curgrouping->id]->groups = array();
                $groupsingrouping = groups_get_all_groups($course->id, null, $curgrouping->id);
                array_unshift($groupsingrouping, $groupallparticipants);
                foreach ($groupsingrouping as $gindex => $curgroup) {
                    $groupinginfo = new stdClass();
                    $groupinginfo->gid = $curgroup->id;
                    $groupinginfo->name = $curgroup->name;
                    array_push($jsgroupings[$curgrouping->id]->groups, $groupinginfo);
                }
            }
            $mform->addElement($selectgrouping);
            $mform->addHelpButton('coursegrouping', 'labelgrouping', 'local_assignsubmission_download');
        }

        $activitygroups = groups_get_activity_allowed_groups($cm);
        if (($groupmode != NOGROUPS)) {
            $selectgroup = $mform->createElement('select', 'coursegroup',
                get_string('labelgroup', 'local_assignsubmission_download'));
            $selectgroup->addOption(get_string('allparticipants'), 0);
            foreach ($activitygroups as $index => $curgroup) {
                $selectgroup->addOption($curgroup->name, $index, null);
            }
            $mform->addElement($selectgroup);
            $mform->addHelpButton('coursegroup', 'labelgroup', 'local_assignsubmission_download');
        }

        // Datetimepicker for including only files submitted past a given time.
        $mform->addElement('date_time_selector', 'submissionneweras',
                get_string('submissionneweras', 'local_assignsubmission_download'), ['optional' => true]);
        $mform->addHelpButton('submissionneweras', 'submissionneweras',
                'local_assignsubmission_download');

        if (!empty($this->_customdata['lastdownloaded'])) {
            $mform->addElement('static', 'lastdownloaded',
                get_string('lastdownloaded_title', 'local_assignsubmission_download'),
                $this->_customdata['lastdownloaded']);
            $mform->addHelpButton('lastdownloaded', 'lastdownloaded_title',
                'local_assignsubmission_download');
        }
        if (!empty($this->_customdata['lastdownloadedfeedback'])) {
            $mform->addElement('static', 'lastdownloadedfeedback',
                get_string('lastdownloadedfeedbacks_title', 'local_assignsubmission_download'),
                $this->_customdata['lastdownloadedfeedback']);
            $mform->addHelpButton('lastdownloadedfeedback', 'lastdownloadedfeedbacks_title',
                'local_assignsubmission_download');
        }

        $PAGE->requires->js_call_amd('local_assignsubmission_download/filerenaming_groupingtoggle', 'initializer', array($jsgroupings));

        // Hidden params.
        // $mform->addElement('hidden', 'mydata', '');
        // $mform->setAttributes('mydata', 'data-groupings', array($jsgroupings));

        $mform->addElement('hidden', 'contextid', $instance['contextid']);
        $mform->setType('contextid', PARAM_INT);
        $mform->addElement('hidden', 'id', $instance['cm']->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'userid', $instance['userid']);
        $mform->setType('userid', PARAM_INT);
        $mform->addElement('hidden', 'action', 'grading');
        $mform->setType('action', PARAM_ALPHA);

        // Button.
        $mform->addElement('submit', 'submittodownload', get_string('strfilerenaming', 'local_assignsubmission_download'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if ($data['downloadtype_feedbacks'] == '0' && $data['downloadtype_submissions'] == '0') {
            $errors['downloadtype'] = get_string('downloadtype:error', 'local_assignsubmission_download');
        }
        return $errors;
    }
}
