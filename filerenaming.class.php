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
 * This file contains the filerenaming class (extending assign)
 *
 * @package       local
 * @subpackage    assignsubmission_download
 * @author        Günther Bernsteiner
 * @author        Andreas Krieger
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The filerenaming class, extending assign.
 *
 * @package       local
 * @subpackage    assignsubmission_download
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filerenaming extends assign {

    /**
     * Main view setup
     *
     * @global type $SESSION
     */
    protected function view_grading_page() {
        global $CFG, $SESSION, $PAGE;

        $id = required_param('id', PARAM_INT);
        $o = '';

        // Ugly hack, dont try this at home!
        if (empty($SESSION->assignment)) {
            $SESSION->assignment = new stdClass();
        }

        $o .= $this->view_filerenaming_page();

        return $o;
    }

    /**
     * View entire printpreview page.
     *
     * @return string
     */
    protected function view_filerenaming_page() {
        global $CFG;

        $o = '';
        // Need submit permission to submit an assignment.
        require_capability('mod/assign:grade', $this->get_context());
        require_once($CFG->dirroot . '/local/assignsubmission_download/filerenamingsettingsform.php');

        // Only load this if it is.

        $this->process_save_filerenaming_settings();
        $o .= $this->view_filerenaming_table();
        $o .= $this->view_footer();

        \local_assignsubmission_download\event\assignsubmission_download_table_viewed::create_from_assign($this)->trigger();

        return $o;
    }

    /**
     * View the grading table of all submissions for this assignment.
     *
     * @return string
     */
    protected function view_filerenaming_table() {
        global $CFG, $USER;

        $o = '';
        $cmid = $this->get_course_module()->id;

        $links = array();
        if (has_capability('gradereport/grader:view', $this->get_course_context()) &&
                has_capability('moodle/grade:viewall', $this->get_course_context())) {
            $gradebookurl = '/grade/report/grader/index.php?id=' . $this->get_course()->id;
            $links[$gradebookurl] = get_string('viewgradebook', 'assign');
        }
        if ($this->is_any_submission_plugin_enabled() && $this->count_submissions()) {
            $downloadurl = '/mod/assign/view.php?id=' . $cmid . '&action=downloadall';
            $links[$downloadurl] = get_string('downloadall', 'assign');
        }
        if ($this->is_blind_marking() &&
                has_capability('mod/assign:revealidentities', $this->get_context())) {
            $revealidentitiesurl = '/mod/assign/view.php?id=' . $cmid . '&action=revealidentities';
            $links[$revealidentitiesurl] = get_string('revealidentities', 'assign');
        }
        foreach ($this->get_feedback_plugins() as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible()) {
                foreach ($plugin->get_grading_actions() as $action => $description) {
                    $url = '/mod/assign/view.php' .
                           '?id=' .  $cmid .
                           '&plugin=' . $plugin->get_type() .
                           '&pluginsubtype=assignfeedback' .
                           '&action=viewpluginpage&pluginaction=' . $action;
                    $links[$url] = $description;
                }
            }
        }
        $gradingactions = new url_select($links);
        $gradingactions->set_label(get_string('choosegradingaction', 'assign'));

        $pattern = get_user_preferences('filerenamingpattern', '');
        $cleanfilename = get_user_preferences('clean_filerenaming', '');

        $urlparams = array('id' => $this->get_course_module()->id, 'action' => 'grading');
        $currenturl = new moodle_url($CFG->wwwroot . '/local/assignsubmission_download/view_filerenaming.php', $urlparams);

        $shownotreuploadablehint = $this->get_feedback_plugin_by_type('offline')->is_enabled()
                                    || $this->get_feedback_plugin_by_type('file')->is_enabled();

        $filerenamingsettingsformparams = array('cm' => $this->get_course_module(),
                                                'contextid' => $this->get_context()->id,
                                                'currenturl' => $currenturl,
                                                'userid' => $USER->id,
                                                'submissionsenabled' => $this->is_any_submission_plugin_enabled(),
                                                'show_notreuploadable_hint' => $shownotreuploadablehint);

        $classoptions = array('class' => 'gradingbatchoperationsform');

        $filerenamingsettingsform = new mod_assign_filerenaming_settings_form(null,
                                                                  $filerenamingsettingsformparams,
                                                                  'post',
                                                                  '',
                                                                  $classoptions);
        $filerenamingsettingsdata = new stdClass();
        $filerenamingsettingsdata->filerenamingpattern = $pattern;
        $filerenamingsettingsdata->clean_filerenaming = $cleanfilename;
        $filerenamingsettingsform->set_data($filerenamingsettingsdata);

        $actionformtext = "";
        $header = new assign_header($this->get_instance(),
                                    $this->get_context(),
                                    false,
                                    $this->get_course_module()->id,
                                    get_string('grading', 'assign'),
                                    $actionformtext);
        $o .= $this->get_renderer()->render($header);

        $o .= $this->get_renderer()->render(new assign_form('filerenamingsettingsform',
                                                            $filerenamingsettingsform
                                                            ));

        // Plagiarism update status apearring in the grading book.
        if (!empty($CFG->enableplagiarism)) {
            require_once($CFG->libdir . '/plagiarismlib.php');
            $o .= plagiarism_update_status($this->get_course(), $this->get_course_module());
        }

        return $o;
    }

    /**
     * Save grading options.
     *
     * @return void
     */
    protected function process_save_filerenaming_settings() {
        global $CFG, $USER, $SESSION;

        // Include grading options form.
        require_once($CFG->dirroot . '/local/assignsubmission_download/filerenamingsettingsform.php');

        // Need submit permission to submit an assignment.
        require_capability('mod/assign:grade', $this->get_context());

        $urlparams = array('id' => $this->get_course_module()->id, 'action' => 'grading');
        $currenturl = new moodle_url($CFG->wwwroot . '/local/assignsubmission_download/view_filerenaming.php', $urlparams);

        $shownotreuploadablehint = $this->get_feedback_plugin_by_type('offline')->is_enabled()
                                    || $this->get_feedback_plugin_by_type('file')->is_enabled();

        $filerenamingsettingsparams = array('cm' => $this->get_course_module(),
                                            'currenturl' => $currenturl,
                                            'contextid' => $this->get_context()->id,
                                            'userid' => $USER->id,
                                            'submissionsenabled' => $this->is_any_submission_plugin_enabled(),
                                            'show_notreuploadable_hint' => $shownotreuploadablehint);

        $mform = new mod_assign_filerenaming_settings_form(null, $filerenamingsettingsparams);

        if ($data = $mform->get_data()) {
            set_user_preference('filerenamingpattern', $data->filerenamingpattern);
            set_user_preference('clean_filerenaming', $data->clean_filerenaming);

            // Download submissions.
            if (!isset($data->coursegroup)) {
                $data->coursegroup = 0;
            }
            if (!isset($data->coursegrouping)) {
                $data->coursegrouping = 0;
            }
            if (isset($data->submittodownload)) {
                $this->download_submissions($data->coursegroup, $data->coursegrouping);
            }
        }
    }


    /**
     * Set the action and parameters that can be used to return to the current page.
     *
     * @param string $action The action for the current page
     * @param array $params An array of name value pairs which form the parameters
     *                      to return to the current page.
     * @return void
     */
    public function register_return_link($action, $params) {
        global $PAGE;
        $params['action'] = $action;
        $cm = $this->get_course_module();
        if ($cm) {
            $currenturl = new moodle_url('/local/assignsubmission_download/view_filerenaming.php', array('id' => $cm->id));
        }

        $currenturl->params($params);
        $PAGE->set_url($currenturl);
    }

    /**
     * Download a zip file of all assignment submissions.
     *
     * @param array $userids Array of user ids to download assignment submissions in a zip file
     * @return string - If an error occurs, this will contain the error page.
     */
    protected function download_submissions($coursegroup = false, $coursegrouping = false) {
        global $CFG, $DB;

        // More efficient to load this here.
        require_once($CFG->libdir.'/filelib.php');
        require_once($CFG->dirroot.'/local/assignsubmission_download/locallib.php');

        // Increase the server timeout to handle the creation and sending of large zip files.
        core_php_time_limit::raise();

        $this->require_view_grades();

        // Load all users with submit.
        $students = get_enrolled_users($this->get_context(), "mod/assign:submit", $coursegroup, 'u.*', null, null, null,
                        $this->show_only_active_users());

        // Build a list of files to zip.
        $filesforzipping = array();

        $groupmode = groups_get_activity_groupmode($this->get_course_module());
        // All users.
        $groupid = $coursegroup;
        $groupingid = $coursegrouping;

        $groupname = '';
        if ($groupmode) {
            $groupname = groups_get_group_name($groupid).'-';
        }

        // Construct the zip file name.
        $filename = filerenaming_clean_custom($this->get_course()->shortname . '-' . // AMC moodle university code one line.
                                   $this->get_instance()->name . '-' .
                                   $groupname . $this->get_course_module()->id . '.zip');

        // Get all the files for each student.
        $resetgroupname = false;
        foreach ($students as $student) {
            if (!$resetgroupname) {
               $defaultgroupname = $groupname;
            } else {
               $groupname = $defaultgroupname;
               $resetgroupname = false;
            }

            $userid = $student->id;
            
            $isuseringrouping = false;
            $groupinggroupsforuser = groups_get_all_groups($this->get_course()->id, $userid, $groupingid);
            if (sizeof($groupinggroupsforuser)  > 0)  {
                $isuseringrouping = true;
            }
            
            $groupsforuser = groups_get_all_groups($this->get_course()->id, $userid);
            if (sizeof($groupsforuser) == 1) {
                $groupname = array_values($groupsforuser)[0]->name."-";
                $resetgroupname = true;
            }
            
            $isuseringroup = false;
            if ((groups_is_member($groupid, $userid)))  {
                $isuseringrouping = false; // This is because "in group" has priority over "in grouping".
                $isuseringroup = true;
            }
            $nogrouprestriction = false;
            if (!$groupmode or (!$groupid and !$groupingid)) {
                $nogrouprestriction = true;
            }
            
            if ($isuseringroup or $isuseringrouping or $nogrouprestriction) {
                // Get the plugins to add their own files to the zip.

                $submissiongroup = false;
                if ($this->get_instance()->teamsubmission) {
                    $submission = $this->get_group_submission($userid, 0, false);
                    $submissiongroup = $this->get_submission_group($userid);
                    if ($submissiongroup) {
                        $groupname = $submissiongroup->name . '-';
                    } else {
                        $groupname = get_string('defaultteam', 'assign') . '-';
                    }
                    $resetgroupname = true;
                } else {
                    $submission = $this->get_user_submission($userid, false);
                }

                if ($this->is_blind_marking()) {
                    $prefix = str_replace('_', ' ', $groupname . get_string('participant', 'assign'));
                    $prefix = clean_filename($prefix . '_' . $this->get_uniqueid_for_user($userid));
                } else {
                    $prefix = str_replace('_', ' ', $groupname . fullname($student));
                    $prefix = clean_filename($prefix . '_' . $this->get_uniqueid_for_user($userid));
                }

                if ($submission) {
                    $downloadasfolders = get_user_preferences('assign_downloadasfolders', 1);
                    foreach ($this->get_submission_plugins() as $plugin) {
                        if ($plugin->is_enabled() && $plugin->is_visible()) {
                            if ($downloadasfolders) {
                                // Create a folder for each user for each assignment plugin.
                                // This is the default behavior for version of Moodle >= 3.1.
                                $submission->exportfullpath = true;
                                $pluginfiles = $plugin->get_files($submission, $student);
                                foreach ($pluginfiles as $zipfilepath => $file) {
                                    $subtype = $plugin->get_subtype();
                                    $type = $plugin->get_type();
                                    $zipfilename = basename($zipfilepath);
                                    $prefixedfilename = clean_filename($prefix .
                                                                       '_' .
                                                                       $subtype .
                                                                       '_' .
                                                                       $type .
                                                                       '_');
                                    if ($type == 'file') {
                                        $pathfilename = $prefixedfilename . $file->get_filepath() . $zipfilename;
                                    } else if ($type == 'onlinetext') {
                                        $pathfilename = $prefixedfilename . '/' . $zipfilename;
                                    } else {
                                        $pathfilename = $prefixedfilename . '/' . $zipfilename;
                                    }

                                    // AMC moodle university code start.
                                    $pathfilename = filerenaming_rename_file($pathfilename, $zipfilename, $student,
                                            $this, $submission, $groupname, $filesforzipping);
                                    // AMC moodle university code end.

                                    $pathfilename = clean_param($pathfilename, PARAM_PATH);
                                    $filesforzipping[$pathfilename] = $file;
                                }
                            } else {
                                // Create a single folder for all users of all assignment plugins.
                                // This was the default behavior for version of Moodle < 3.1.
                                $submission->exportfullpath = false;
                                $pluginfiles = $plugin->get_files($submission, $student);
                                foreach ($pluginfiles as $zipfilename => $file) {
                                    $subtype = $plugin->get_subtype();
                                    $type = $plugin->get_type();
                                    $prefixedfilename = clean_filename($prefix .
                                                                       '_' .
                                                                       $subtype .
                                                                       '_' .
                                                                       $type .
                                                                       '_' .
                                                                       $zipfilename);
                                    // AMC moodle university code start.
                                    $prefixedfilename = filerenaming_rename_file($prefixedfilename, $zipfilename, $student,
                                            $this, $submission, $groupname, $filesforzipping);
                                    // AMC moodle university code end.
                                    $filesforzipping[$prefixedfilename] = $file;
                                }
                            }
                        }
                    }
                }
            }
        }
        $result = '';
        if (count($filesforzipping) == 0) {
            $header = new assign_header($this->get_instance(),
                                        $this->get_context(),
                                        '',
                                        $this->get_course_module()->id,
                                        get_string('downloadall', 'assign'));
            $result .= $this->get_renderer()->render($header);
            $result .= $this->get_renderer()->notification(get_string('nosubmission', 'assign'));
            $url = new moodle_url('/mod/assign/view.php', array('id' => $this->get_course_module()->id,
                                                                    'action' => 'grading'));
            $result .= $this->get_renderer()->continue_button($url);
            $result .= $this->view_footer();
            echo $result;
            $result = '';
        } else if ($zipfile = $this->pack_files($filesforzipping)) {
            \mod_assign\event\all_submissions_downloaded::create_from_assign($this)->trigger();
            // Send file and delete after sending.
            send_temp_file($zipfile, $filename);
            // We will not get here - send_temp_file calls exit.
        }
        return $result;
    }

}