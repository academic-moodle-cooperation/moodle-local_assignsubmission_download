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
 * @package       local_assignsubmission_download
 * @author        Günther Bernsteiner
 * @author        Andreas Krieger
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_assignsubmission_download\local;

use assign;
use assign_form;
use mod_assign\output\assign_header;
use core_php_time_limit;
use mod_assign_filerenaming_settings_form;
use stdClass;
use url_select;
use moodle_url;
use zip_packer;


/**
 * The filerenaming class, extending assign.
 *
 * @package       local_assignsubmission_download
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filerenaming extends assign {
    /**
     * @var null|\moodleform $filerenamingform Holds the instance of the file renaming form.
     * Initially set to null.
     */
    private $filerenamingform = null;

    /**
     * Main view setup
     *
     * @return string
     */
    protected function view_grading_page() {
        global $SESSION;

        // Ugly hack, dont try this at home!
        if (empty($SESSION->assignment)) {
            $SESSION->assignment = new stdClass();
        }

        $o = $this->view_filerenaming_page();

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
        global $USER, $OUTPUT;

        $o = '';
        $cmid = $this->get_course_module()->id;
        $filenumberinfo = false;

        $links = [];
        if (
            has_capability('gradereport/grader:view', $this->get_course_context()) &&
                has_capability('moodle/grade:viewall', $this->get_course_context())
        ) {
            $gradebookurl = '/grade/report/grader/index.php?id=' . $this->get_course()->id;
            $links[$gradebookurl] = get_string('viewgradebook', 'assign');
        }
        if ($this->is_any_submission_plugin_enabled() && $this->count_submissions()) {
            $downloadurl = '/mod/assign/view.php?id=' . $cmid . '&action=downloadall';
            $links[$downloadurl] = get_string('downloadall', 'assign');
        }
        if (
            $this->is_blind_marking() &&
                has_capability('mod/assign:revealidentities', $this->get_context())
        ) {
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
        // Check if a file submission type is activated with a file submission count higher than one.
        foreach ($this->get_submission_plugins() as $plugin) {
            if ($plugin->is_enabled() && $plugin->get_config('maxfilesubmissions') > 1) {
                $filenumberinfo = true;
                break;
            }
        }

        $gradingactions = new url_select($links);
        $gradingactions->set_label(get_string('choosegradingaction', 'local_assignsubmission_download'));

        $filerenamingsettingsform = $this->get_filenrenaming_form();
        $filerenamingsettingsdata = $this->generate_filerenaming_settings_data($cmid, $USER->id);
        $filerenamingsettingsform->set_data($filerenamingsettingsdata);

        $actionformtext = "";
        $header = new assign_header(
            $this->get_instance(),
            $this->get_context(),
            false,
            $this->get_course_module()->id,
            get_string('grading', 'assign'),
            $actionformtext
        );
        $o .= $this->get_renderer()->render($header);
        // Show info dialogue if more than one file can be uploaded.
        if ($filenumberinfo) {
            $o .= $OUTPUT->box($OUTPUT->notification(get_string(
                'filenumberinfo',
                'local_assignsubmission_download'
            ), 'info'), 'generalbox', 'nogroupsinfo');
        }
        $o .= $this->get_renderer()->render(new assign_form(
            'filerenamingsettingsform',
            $filerenamingsettingsform
        ));

        return $o;
    }

    /**
     * Returns the filerenaming form
     * @return mod_assign_filerenaming_settings_form
     */
    private function get_filenrenaming_form() {
        global $USER, $CFG;
        if (empty($this->filerenamingform)) {
            $urlparams = ['id' => $this->get_course_module()->id, 'action' => 'grading'];
            $currenturl = new moodle_url($CFG->wwwroot . '/local/assignsubmission_download/view_filerenaming.php', $urlparams);

            $cmid = $this->get_course_module()->id;
            $userid = $USER->id;

            $downloadtable = 'local_assignsubmission_download';
            $feedbacktable = 'local_assignsubmission_download_feedback';

            $lastdownloaded = $this->get_lastdownloaded_date($cmid, $userid);
            $lastdownloadedfeedback = $this->get_lastdownloaded_date($cmid, $userid, true);
            $lastfilenamingscheme = $this->get_filenamingscheme($cmid, $userid, $downloadtable);
            $lastfilenamingschemefeedback = $this->get_filenamingscheme($cmid, $userid, $feedbacktable);
            $lastpreventnameext = $this->get_preventnameextension_as_string($cmid, $userid, $downloadtable);
            $lastpreventnameextfeedback = $this->get_preventnameextension_as_string($cmid, $userid, $feedbacktable);
            $lastcleanfilename = $this->get_cleanfilename_as_string($cmid, $userid, $downloadtable);
            $lastcleanfilenamefeedback = $this->get_cleanfilename_as_string($cmid, $userid, $feedbacktable);
            $lastsubneweras = $this->get_submission_neweras_date($cmid, $userid, $downloadtable);
            $lastsubnewerasfeedback = $this->get_submission_neweras_date($cmid, $userid, $feedbacktable);
            $lastgroup = $this->get_group($cmid, $userid, $downloadtable);
            $lastgroupfeedback = $this->get_group($cmid, $userid, $feedbacktable);
            $lastgrouping = $this->get_grouping($cmid, $userid, $downloadtable);
            $lastgroupingfeedback = $this->get_grouping($cmid, $userid, $feedbacktable);
            $lastzipnamingscheme = $this->get_zipnamingscheme($cmid, $userid, $downloadtable);
            $lastzipnamingschemefeedback = $this->get_zipnamingscheme($cmid, $userid, $feedbacktable);

            $shownotreuploadablehint = $this->get_feedback_plugin_by_type('offline')->is_enabled()
                || $this->get_feedback_plugin_by_type('file')->is_enabled();

            $filerenamingsettingsformparams = [
                'cm' => $this->get_course_module(),
                'contextid' => $this->get_context()->id,
                'currenturl' => $currenturl,
                'userid' => $USER->id,
                'submissionsenabled' => $this->is_any_submission_plugin_enabled(),
                'show_notreuploadable_hint' => $shownotreuploadablehint,
                'lastdownloaded' => $lastdownloaded,
                'lastdownloadedfeedback' => $lastdownloadedfeedback,
                'lastfilenamingscheme' => $lastfilenamingscheme,
                'lastfilenamingschemefeedback' => $lastfilenamingschemefeedback,
                'lastpreventnameextension' => $lastpreventnameext,
                'lastpreventnameextensionfeedback' => $lastpreventnameextfeedback,
                'lastcleanfilename' => $lastcleanfilename,
                'lastcleanfilenamefeedback' => $lastcleanfilenamefeedback,
                'lastsubneweras' => $lastsubneweras,
                'lastsubnewerasfeedback' => $lastsubnewerasfeedback,
                'lastgroup' => $lastgroup,
                'lastgroupfeedback' => $lastgroupfeedback,
                'lastgrouping' => $lastgrouping,
                'lastgroupingfeedback' => $lastgroupingfeedback,
                'lastzipnamingscheme' => $lastzipnamingscheme,
                'lastzipnamingschemefeedback' => $lastzipnamingschemefeedback,
            ];

            $classoptions = ['class' => 'gradingbatchoperationsform', 'data-double-submit-protection' => 'off'];

            $this->filerenamingform = new mod_assign_filerenaming_settings_form(
                null,
                $filerenamingsettingsformparams,
                'post',
                '',
                $classoptions
            );
        }
        return $this->filerenamingform;
    }

    /**
     * Save grading options.
     *
     * @return void
     */
    protected function process_save_filerenaming_settings() {
        global $CFG;

        // Include grading options form.
        require_once($CFG->dirroot . '/local/assignsubmission_download/filerenamingsettingsform.php');

        // Need submit permission to submit an assignment.
        require_capability('mod/assign:grade', $this->get_context());

        $mform = $this->get_filenrenaming_form();

        if ($data = $mform->get_data()) {
            set_user_preference('filerenamingpattern', $data->filerenamingpattern);
            set_user_preference('prevent_nameextension', $data->prevent_nameextension);
            set_user_preference('clean_filerenaming', $data->clean_filerenaming);
            set_user_preference('submissionneweras', $data->submissionneweras);
            set_user_preference('nameofziparchive', $data->nameofziparchive);
            set_user_preference('downloadtype_submissions', $data->downloadtype_submissions);
            set_user_preference('downloadtype_feedbacks', $data->downloadtype_feedbacks);

            // Download submissions.
            if (!isset($data->coursegroup)) {
                $data->coursegroup = 0;
            }
            if (!isset($data->coursegrouping)) {
                $data->coursegrouping = 0;
            }
            if (!isset($data->submissionneweras)) {
                $data->submissionneweras = 0;
            }
            if (!isset($data->onegroupsubmission)) {
                $data->onegroupsubmission = 0;
            }
            $downloadsubmissions = $data->downloadtype_submissions == '1';
            $downloadfeedbacks = $data->downloadtype_feedbacks == '1';
            if (isset($data->submittodownload)) {
                $this->download_submissions(
                    $data->coursegroup,
                    $data->coursegrouping,
                    $data->submissionneweras,
                    $downloadsubmissions,
                    $downloadfeedbacks,
                    $data->prevent_nameextension,
                    $data->onegroupsubmission
                );
            }
        }
    }

    /**
     * Generate the filerenamening settings data for the filerenaming settings form.
     *
     * @param int $cmid int coursemodule id
     * @param int $userid int user id
     * @return stdClass filerenaming settings data object
     */
    private function generate_filerenaming_settings_data($cmid, $userid) {
        $tablename = $this->get_latest_download_settings_tablename($cmid, $userid);
        $data = new stdClass();
        if ($tablename != null) {
            $data->downloadtype_submissions = get_user_preferences('downloadtype_submissions', 0);
            $data->downloadtype_feedbacks = get_user_preferences('downloadtype_feedbacks', 0);
            $data->filerenamingpattern = $this->get_filenamingscheme($cmid, $userid, $tablename);
            $data->clean_filerenaming = $this->get_cleanfilename_as_bool($cmid, $userid, $tablename);
            $data->prevent_nameextension = $this->get_preventnameextension_as_bool($cmid, $userid, $tablename);
            $data->submissionneweras = $this->get_submission_neweras_setting($cmid, $userid, $tablename);
            $data->nameofziparchive = $this->get_zipnamingscheme($cmid, $userid, $tablename);
        }
        return $data;
    }

    /**
     * Returns the last downloaded date for module and user as string
     * @param int $cmid int coursemodule id
     * @param int $userid int user id
     * @param bool $feedback
     * @return \lang_string|string
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected function get_lastdownloaded_date($cmid, $userid, $feedback = false) {
        global $DB;
        $tablename = $feedback ? 'local_assignsubmission_download_feedback' : 'local_assignsubmission_download';
        $lastdownload = $DB->get_record($tablename, ['userid' => $userid, 'cmid' => $cmid]);
        if ($lastdownload) {
            return userdate($lastdownload->lastdownloaded);
        } else {
            return get_string('nodownloadsyet', 'local_assignsubmission_download');
        }
    }

    /**
     * Updates the last downloaded date for module and user
     * @param int $cmid int course module id
     * @param int $userid int user id
     * @param bool $feedback
     * @throws \dml_exception
     * @return void
     */
    protected function update_lastdownloaded_date($cmid, $userid, $feedback = false) {
        global $DB;
        $tablename = $feedback ? 'local_assignsubmission_download_feedback' : 'local_assignsubmission_download';
        $lastdownload = $DB->get_record($tablename, ['userid' => $userid, 'cmid' => $cmid]);
        if ($lastdownload) {
            $lastdownload->lastdownloaded = time();
            $DB->update_record($tablename, $lastdownload);
        } else {
            $lastdownload = new stdClass();
            $lastdownload->cmid = $cmid;
            $lastdownload->userid = $userid;
            $lastdownload->lastdownloaded = time();
            $DB->insert_record($tablename, $lastdownload);
        }
    }

    /**
     * Returns the last filenaming scheme for module and user as string from database
     * @param int $cmid int coursemodule id
     * @param int $userid int user id
     * @param string $tablename
     * @return string last filenaming scheme
     */
    protected function get_filenamingscheme($cmid, $userid, $tablename) {
        global $DB;
        $databaseentry = $DB->get_record($tablename, ['userid' => $userid, 'cmid' => $cmid]);
        return $databaseentry->filenamingscheme ?? get_string('nodownloadsyet', 'local_assignsubmission_download');
    }

    /**
     * Transform int 0/1 to string 'yes'/'no'
     * @param int $int
     * @return string
     */
    private function int_to_string($int) {
        return $int ? 'yes' : 'no';
    }

    /**
     * Return the last prevent name extension setting for module and user as string from database
     * @param int $cmid int coursemodule id
     * @param int $userid int user id
     * @param string $tablename
     * @return string last prevent name extension setting as 'yes'/'no'
     */
    protected function get_preventnameextension_as_string($cmid, $userid, $tablename) {
        global $DB;
        $databaseentry = $DB->get_record($tablename, ['userid' => $userid, 'cmid' => $cmid]);
        if ($databaseentry && $databaseentry->preventnameextension !== null) {
            return $this->int_to_string($databaseentry->preventnameextension);
        } else {
            return get_string('nodownloadsyet', 'local_assignsubmission_download');
        }
    }

    /**
     * Return the last prevent name extension setting for module and user as boolean (int) from database
     * @param int $cmid int coursemodule id
     * @param int $userid int user id
     * @param string $tablename
     * @return int (bool) last prevent name extension setting as 0/1
     */
    protected function get_preventnameextension_as_bool($cmid, $userid, $tablename) {
        global $DB;
        $databaseentry = $DB->get_record($tablename, ['userid' => $userid, 'cmid' => $cmid]);
        return $databaseentry->preventnameextension ?? 0;
    }

    /**
     * Return the last clean filename setting for module and user as string from database
     * @param int $cmid int coursemodule id
     * @param int $userid int user id
     * @param string $tablename
     * @return string last clean filename setting as 'yes'/'no'
     */
    protected function get_cleanfilename_as_string($cmid, $userid, $tablename) {
        global $DB;
        $databaseentry = $DB->get_record($tablename, ['userid' => $userid, 'cmid' => $cmid]);
        if ($databaseentry && $databaseentry->cleanfilenames !== null) {
            return $this->int_to_string($databaseentry->cleanfilenames);
        } else {
            return get_string('nodownloadsyet', 'local_assignsubmission_download');
        }
    }

    /**
     * Return the last clean filename setting for module and user as boolean (int) from database
     * @param int $cmid int coursemodule id
     * @param int $userid int user id
     * @param string $tablename
     * @return bool (int) last clean filename setting as 0/1
     */
    protected function get_cleanfilename_as_bool($cmid, $userid, $tablename) {
        global $DB;
        $databaseentry = $DB->get_record($tablename, ['userid' => $userid, 'cmid' => $cmid]);
        return $databaseentry->cleanfilenames ?? 0;
    }

    /**
     * Returns the last submission newer as date setting
     *
     * @param int $cmid int coursemodule id
     * @param int $userid int user id
     * @param string $tablename string table name
     * @return string date as string or info text
     */
    private function get_submission_neweras_date($cmid, $userid, $tablename) {
        global $DB;
        $databaseentry = $DB->get_record($tablename, ['userid' => $userid, 'cmid' => $cmid]);
        if ($databaseentry && $databaseentry->lastsubneweras !== null) {
            return userdate($databaseentry->lastsubneweras);
        } else if ($databaseentry && $databaseentry->lastsubneweras === null) {
            return get_string('functionnotused', 'local_assignsubmission_download');
        } else {
            return get_string('nodownloadsyet', 'local_assignsubmission_download');
        }
    }

    /**
     * Return the last submission newer as setting as int from database, 0 if not set
     *
     * @param int $cmid int coursemodule id
     * @param int $userid int user id
     * @param string $tablename string table name
     * @return int date setting as int, 0 if not set
     */
    private function get_submission_neweras_setting($cmid, $userid, $tablename): int {
        global $DB;
        $databaseentry = $DB->get_record($tablename, ['userid' => $userid, 'cmid' => $cmid]);
        if ($databaseentry && $databaseentry->lastsubneweras !== null) {
            return $databaseentry->lastsubneweras;
        }
        return 0;
    }

    /**
     * Return the last group setting for module and user as string from database
     * @param int $cmid int coursemodule id
     * @param int $userid int user id
     * @param string $tablename
     * @return string last group setting
     */
    protected function get_group($cmid, $userid, $tablename) {
        global $DB;
        $databaseentry = $DB->get_record($tablename, ['userid' => $userid, 'cmid' => $cmid]);
        if ($databaseentry && $databaseentry->choosegroup !== null) {
            return $databaseentry->choosegroup;
        } else if ($databaseentry && $databaseentry->choosegroup === null) {
            return get_string('functionnotused', 'local_assignsubmission_download');
        } else {
            return get_string('nodownloadsyet', 'local_assignsubmission_download');
        }
    }

    /**
     * Return the last grouping setting for module and user as string from database
     * @param int $cmid int coursemodule id
     * @param int $userid int user id
     * @param string $tablename
     * @return string last grouping setting
     */
    protected function get_grouping($cmid, $userid, $tablename) {
        global $DB;
        $databaseentry = $DB->get_record($tablename, ['userid' => $userid, 'cmid' => $cmid]);
        if ($databaseentry && $databaseentry->choosegrouping !== null) {
            return $databaseentry->choosegrouping;
        } else if ($databaseentry && $databaseentry->choosegrouping === null) {
            return get_string('functionnotused', 'local_assignsubmission_download');
        } else {
            return get_string('nodownloadsyet', 'local_assignsubmission_download');
        }
    }

    /**
     * Return the last zip naming scheme for module and user as string from database
     * @param int $cmid int coursemodule id
     * @param int $userid int user id
     * @param string $tablename
     * @return string last zip naming scheme
     */
    protected function get_zipnamingscheme($cmid, $userid, $tablename) {
        global $DB;
        $databaseentry = $DB->get_record($tablename, ['userid' => $userid, 'cmid' => $cmid]);
        return $databaseentry->zipnamingscheme ?? get_string('nodownloadsyet', 'local_assignsubmission_download');
    }

    /**
     * Update or insert the database entry for the download settings
     * @param int $cmid int coursemodule id
     * @param int $userid int user id
     * @param string $tablename string table name for the database entry
     * @param string $filenamingscheme string filenaming scheme
     * @param int $preventnameextension int prevent name extension setting
     * @param int $cleanfilenames int clean filename setting
     * @param int|null $submissionneweras submission newer as date setting
     * @param string|null $coursegroupname course group name
     * @param string|null $coursegroupingname course grouping name
     * @param string $zipnamingscheme string zip naming scheme
     * @return void
     */
    public function update_database_entry($cmid, $userid, $tablename, $filenamingscheme, $preventnameextension, $cleanfilenames,
     $submissionneweras, $coursegroupname, $coursegroupingname, $zipnamingscheme) {
        global $DB;
        $databaseentry = $DB->get_record($tablename, ['userid' => $userid, 'cmid' => $cmid]);
        if ($databaseentry) {
            $databaseentry->filenamingscheme = $filenamingscheme;
            $databaseentry->preventnameextension = $preventnameextension;
            $databaseentry->cleanfilenames = $cleanfilenames;
            $databaseentry->lastsubneweras = $submissionneweras;
            $databaseentry->choosegroup = $coursegroupname;
            $databaseentry->choosegrouping = $coursegroupingname;
            $databaseentry->zipnamingscheme = $zipnamingscheme;
            $DB->update_record($tablename, $databaseentry);
        } else {
            $databaseentry = new stdClass();
            $databaseentry->cmid = $cmid;
            $databaseentry->userid = $userid;
            $databaseentry->filenamingscheme = $filenamingscheme;
            $databaseentry->preventnameextension = $preventnameextension;
            $databaseentry->cleanfilenames = $cleanfilenames;
            $databaseentry->lastsubneweras = $submissionneweras;
            $databaseentry->choosegroup = $coursegroupname;
            $databaseentry->choosegrouping = $coursegroupingname;
            $databaseentry->zipnamingscheme = $zipnamingscheme;
            $DB->insert_record($tablename, $databaseentry);
        }
    }

    /**
     * Returns the table name with the latest download settings based on the unix timestamp of the last download.
     *
     * If the tables have the same last entry date, the table name of the file download settings is returned -
     * the entries are then the same anyway.
     *
     * @param int $cmid int coursemodule id
     * @param int $userid int user id
     * @return string|null table name or null if no entry is found
     */
    private function get_latest_download_settings_tablename($cmid, $userid) {
        global $DB;
        $downloadtablename = 'local_assignsubmission_download';
        $feedbacktablename = 'local_assignsubmission_download_feedback';
        $lastdownload = $DB->get_record($downloadtablename, ['userid' => $userid, 'cmid' => $cmid]);
        $lastfeedback = $DB->get_record($feedbacktablename, ['userid' => $userid, 'cmid' => $cmid]);
        if ($lastdownload && $lastfeedback) {
            if ($lastdownload->lastdownloaded > $lastfeedback->lastdownloaded) {
                return $downloadtablename;
            } else {
                return $feedbacktablename;
            }
        } else if ($lastdownload) {
            return $downloadtablename;
        } else if ($lastfeedback) {
            return $feedbacktablename;
        } else {
            return null;
        }
    }

    /**
     * Updates all the download settings for the selected download type
     * This can be either the file download settings or the feedback download settings.
     *
     * @param string $tablename string table name
     * @param int $cmid int coursemodule id
     * @param int $userid int user id
     * @param int $groupid int group id
     * @param int $groupingid int grouping id
     * @return void
     */
    private function handle_download_settings($tablename, $cmid, $userid, $groupid, $groupingid) {
        $filenamingscheme = get_user_preferences('filerenamingpattern', '');
        $preventnameextension = get_user_preferences('prevent_nameextension', '');
        $cleanfilename = get_user_preferences('clean_filerenaming', '');
        $submissionneweras = get_user_preferences('submissionneweras', 0);
        $ziparchivename = get_user_preferences('nameofziparchive', '');
        $groupname = $groupid ? format_string(groups_get_group_name($groupid)) : null;
        $groupingname = $groupingid ? format_string(groups_get_grouping_name($groupingid)) : null;
        $this->update_database_entry(
            $cmid,
            $userid,
            $tablename,
            $filenamingscheme,
            (int) $preventnameextension,
            (int) $cleanfilename,
            $submissionneweras,
            $groupname,
            $groupingname,
            $ziparchivename
        );
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
            $currenturl = new moodle_url('/local/assignsubmission_download/view_filerenaming.php', ['id' => $cm->id]);
        }

        $currenturl->params($params);
        $PAGE->set_url($currenturl);
    }

    /**
     * Check if content is only HTML structure.
     *
     * @param string $content The content to check.
     * @return bool
     */
    public function is_only_html_structure($content) {
        return $content == '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body></body></html>';
    }

    /**
     * Download a zip file of all assignment submissions.
     * @param mixed $coursegroup
     * @param mixed $coursegrouping
     * @param mixed $submissionneweras
     * @param mixed $downloadsubmissions
     * @param mixed $downloadfeedbacks
     * @param bool $preventnameextension Select if the automatic extension of file names should be prevented.
     * @param bool $onegroupsubmission Select if only one submission per group should be downloaded.
     * @return string - If an error occurs, this will contain the error page.
     */
    protected function download_submissions(
        $coursegroup = false,
        $coursegrouping = false,
        $submissionneweras = 0,
        $downloadsubmissions = true,
        $downloadfeedbacks = false,
        $preventnameextension = false,
        $onegroupsubmission = false
    ) {
        global $CFG, $USER;

        // More efficient to load this here.
        require_once($CFG->libdir . '/filelib.php');
        require_once($CFG->dirroot . '/local/assignsubmission_download/locallib.php');

        // Increase the server timeout to handle the creation and sending of large zip files.
        core_php_time_limit::raise();

        $this->require_view_grades();

        // Load all users with submit.
        $students = get_enrolled_users(
            $this->get_context(),
            "mod/assign:submit",
            $coursegroup,
            'u.*',
            null,
            0,
            0,
            $this->show_only_active_users()
        );

        // Build a list of files to zip.
        $filesforzipping = [];

        $groupmode = groups_get_activity_groupmode($this->get_course_module());
        // All users.
        $groupid = $coursegroup;
        $groupingid = $coursegrouping;

        $groupname = '';
        if ($groupmode) {
            $groupname = groups_get_group_name($groupid) . '-';
        }

        $fs = get_file_storage();

        // Construct the zip file name.
        $zipname = ziprenaming_rename_zip_archive($this);

        // Array to remember already downloaded group files.
        $alreadydownloadedgroups = [];

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
            if (count($groupinggroupsforuser) > 0) {
                $isuseringrouping = true;
            }

            $groupsforuser = groups_get_all_groups($this->get_course()->id, $userid);
            if (count($groupsforuser) == 1) {
                $groupname = array_values($groupsforuser)[0]->name . "-";
                $resetgroupname = true;
            }

            $isuseringroup = false;
            if ((groups_is_member($groupid, $userid))) {
                $isuseringrouping = false; // This is because "in group" has priority over "in grouping".
                $isuseringroup = true;
            }
            $nogrouprestriction = false;
            if (!$groupmode || (!$groupid && !$groupingid)) {
                $nogrouprestriction = true;
            }

            if ($isuseringroup || $isuseringrouping || $nogrouprestriction) {
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

                if ($onegroupsubmission && $submissiongroup) {
                    if (in_array($submissiongroup->id, $alreadydownloadedgroups)) {
                        // This group has already been downloaded.
                        continue;
                    } else {
                        $alreadydownloadedgroups[] = $submissiongroup->id;
                    }
                }
                if ($submission) {
                    $downloadasfolders = get_user_preferences('assign_downloadasfolders', 1);
                    // TODO is this ever been used / when did it last work? TBD whether it will be used - 15.06.2022.
                    $downloadasfolders = false;
                    if ($downloadsubmissions) {
                        foreach ($this->get_submission_plugins() as $plugin) {
                            if ($plugin->is_enabled() && $plugin->is_visible()) {
                                if ($downloadasfolders) {
                                    // Create a folder for each user for each assignment plugin.
                                    // This is the default behavior for version of Moodle >= 3.1.
                                    $submission->exportfullpath = true;
                                    $pluginfiles = $plugin->get_files($submission, $student);
                                    $sequence = 1;
                                    foreach ($pluginfiles as $zipfilepath => $file) {
                                        // Todo Kick out files out of the cutoff date here if they have there own timestamp!
                                        $type = $plugin->get_type();
                                        // Compare $submissionneweras against the file timestamp if type is file.
                                        // Otherwise compare against the timestamp of the submission.
                                        if (
                                            ($type == 'file'
                                                && $file->get_timemodified() >= $submissionneweras)
                                            || ($type != 'file'
                                                && $submission->timemodified >= $submissionneweras)
                                        ) {
                                            $subtype = $plugin->get_subtype();
                                            $zipfilename = basename($zipfilepath);
                                            $prefixedfilename = clean_filename(/*$prefix . */
                                                '_' .
                                                $subtype .
                                                '_' .
                                                $type .
                                                '_'
                                            );
                                            if ($type == 'file') {
                                                $pathfilename = $prefixedfilename . $file->get_filepath() . $zipfilename;
                                            } else if ($type == 'onlinetext') {
                                                $pathfilename = $prefixedfilename . '/' . $zipfilename;
                                            } else {
                                                $pathfilename = $prefixedfilename . '/' . $zipfilename;
                                            }
                                            // AMC moodle university code start.
                                            $pathfilename = filerenaming_rename_file(
                                                $pathfilename,
                                                $zipfilename,
                                                $student,
                                                $this,
                                                $submission,
                                                $groupname,
                                                $sequence++,
                                                $filesforzipping,
                                                $preventnameextension
                                            );
                                            // AMC moodle university code end.
                                            $pathfilename = clean_param($pathfilename, PARAM_PATH);
                                            $filesforzipping[$pathfilename] = $file;
                                        }
                                    }
                                } else {
                                    // Create a single folder for all users of all assignment plugins.
                                    // This was the default behavior for version of Moodle < 3.1.
                                    $submission->exportfullpath = false;
                                    $pluginfiles = $plugin->get_files($submission, $student);
                                    $type = $plugin->get_type();
                                    $typestr = $plugin->get_name();
                                    $sequence = 1;

                                    $onlinetextfilestorename = [];
                                    $onlinetextcontents = '';
                                    $onlinetextfilename = '';
                                    foreach ($pluginfiles as $zipfilename => $file) {
                                        // Compare $submissionneweras against the file timestamp if type is file.
                                        // Otherwise compare against the timestamp of the submission.
                                        if (
                                            ($type == 'file'
                                                && $file->get_timemodified() >= $submissionneweras)
                                            || ($type != 'file'
                                                && $submission->timemodified >= $submissionneweras)
                                        ) {
                                            $subtype = get_string('submission', 'mod_assign');
                                            $prefixedfilename = clean_filename(/*$prefix .*/
                                                '_' .
                                                $subtype .
                                                '_' .
                                                $typestr
                                            );
                                            // AMC moodle university code start.
                                            // AMC moodle university code end.
                                            if ($type == 'onlinetext') {
                                                if ($zipfilename != 'onlinetext.html') {
                                                    $dirname = filerenaming_rename_file(
                                                        $prefixedfilename,
                                                        '',
                                                        $student,
                                                        $this,
                                                        $submission,
                                                        $groupname,
                                                        $sequence,
                                                        $filesforzipping,
                                                        $preventnameextension
                                                    );
                                                    $prefixedfilename = $dirname . '_files/' . $zipfilename;
                                                    $filesforzipping[$prefixedfilename] = $file;
                                                    $onlinetextfilestorename[$zipfilename] = $prefixedfilename;
                                                } else {
                                                    $prefixedfilename = filerenaming_rename_file(
                                                        $prefixedfilename,
                                                        $zipfilename,
                                                        $student,
                                                        $this,
                                                        $submission,
                                                        $groupname,
                                                        $sequence++,
                                                        $filesforzipping,
                                                        $preventnameextension
                                                    );
                                                    $onlinetextcontents = $file[0];
                                                    $onlinetextfilename = $prefixedfilename;
                                                }
                                                $onlinetextcontents = str_replace(
                                                    array_keys($onlinetextfilestorename),
                                                    array_values($onlinetextfilestorename),
                                                    $onlinetextcontents
                                                );
                                                // Adds the onlinetext file only if it is not empty.
                                                if (!$this->is_only_html_structure($onlinetextcontents)) {
                                                    $filesforzipping[$onlinetextfilename] = [$onlinetextcontents];
                                                }
                                            } else {
                                                $prefixedfilename = filerenaming_rename_file(
                                                    $prefixedfilename,
                                                    $zipfilename,
                                                    $student,
                                                    $this,
                                                    $submission,
                                                    $groupname,
                                                    $sequence++,
                                                    $filesforzipping,
                                                    $preventnameextension
                                                );
                                                $filesforzipping[$prefixedfilename] = $file;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ($downloadfeedbacks) {
                        $feedback = $this->get_assign_feedback_status_renderable($student);
                        // The feedback for our latest submission.
                        if ($feedback && $feedback->grade) {
                            $sequence = 1;
                            foreach ($this->get_feedback_plugins() as $feedbackplugin) {
                                if (!$feedbackplugin->is_enabled() || !$feedbackplugin->is_visible()) {
                                    continue;
                                }
                                $component = $feedbackplugin->get_subtype() . '_' . $feedbackplugin->get_type();
                                $subtype = get_string('feedback');
                                $type = $feedbackplugin->get_type();
                                $typestr = $feedbackplugin->get_name();
                                if (method_exists($feedbackplugin, 'get_user_data_file_areas')) {
                                    $fileareas = $feedbackplugin->get_user_data_file_areas();
                                } else {
                                    $fileareas = $feedbackplugin->get_file_areas();
                                }
                                $commentsfilestorename = [];

                                foreach ($fileareas as $filearea => $name) {
                                    if (
                                        $areafiles = $fs->get_area_files(
                                            $this->get_context()->id,
                                            $component,
                                            $filearea,
                                            $feedback->grade->id,
                                            'itemid, filepath, filename',
                                            false
                                        )
                                    ) {
                                        foreach ($areafiles as $file) {
                                            $zipfilename = $file->get_filename();
                                            if ($type == 'comments') {
                                                $dirname = filerenaming_rename_file(
                                                    $prefixedfilename,
                                                    '',
                                                    $student,
                                                    $this,
                                                    $submission,
                                                    $groupname,
                                                    $sequence,
                                                    $filesforzipping,
                                                    $preventnameextension
                                                );
                                                $prefixedfilename = $dirname . '_files/' . $zipfilename;
                                                $filesforzipping[$prefixedfilename] = $file;
                                                $commentsfilestorename[$zipfilename] = $prefixedfilename;
                                            } else {
                                                $prefixedfilename = clean_filename(/*$prefix .*/
                                                    '_' .
                                                    $subtype .
                                                    '_' .
                                                    $typestr
                                                );
                                                // AMC moodle university code start.
                                                $prefixedfilename = filerenaming_rename_file(
                                                    $prefixedfilename,
                                                    $zipfilename,
                                                    $student,
                                                    $this,
                                                    $submission,
                                                    $groupname,
                                                    $sequence++,
                                                    $filesforzipping,
                                                    $preventnameextension
                                                );
                                                $filesforzipping[$prefixedfilename] = $file;
                                            }
                                        }
                                    }
                                }

                                if ($type == 'comments') {
                                    $comments = $feedbackplugin->get_editor_text('comments', $feedback->grade->id);
                                    $comments = str_replace('@@PLUGINFILE@@/', '', $comments);
                                    $comments = str_replace(
                                        array_keys($commentsfilestorename),
                                        array_values($commentsfilestorename),
                                        $comments
                                    );
                                    if (mb_strlen(trim($comments)) > 0) {
                                        $comments = self::convert_content_to_html_doc($feedbackplugin->get_name(), $comments);
                                        $zipfilename = $typestr . '.html';
                                        $prefixedfilename = clean_filename(/*$prefix .*/
                                            '_' .
                                            $subtype .
                                            '_' .
                                            $typestr
                                        );
                                        // AMC moodle university code start.
                                        $prefixedfilename = filerenaming_rename_file(
                                            $prefixedfilename,
                                            $zipfilename,
                                            $student,
                                            $this,
                                            $submission,
                                            $groupname,
                                            $sequence++,
                                            $filesforzipping,
                                            $preventnameextension
                                        );

                                        $filesforzipping[$prefixedfilename] = [$comments];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $result = '';
        if (count($filesforzipping) == 0) {
            $header = new assign_header(
                $this->get_instance(),
                $this->get_context(),
                false,
                $this->get_course_module()->id,
                get_string('downloadall', 'assign')
            );
            $result .= $this->get_renderer()->render($header);

            // Print warning with date if files were found and $submissionneweras was set.
            // Otherwise print normale warning.
            // Depending on if submissions or feedbacks were downloaded.
            if ((isset($pluginfiles) && count($pluginfiles) > 0) || $submissionneweras > 0) {
                if ($downloadsubmissions) {
                    $result .= $this->get_renderer()->notification(get_string('nosubmissionneweras',
                            'local_assignsubmission_download', userdate($submissionneweras)));
                } else {
                    $result .= $this->get_renderer()->notification(get_string('nofeedbackneweras',
                            'local_assignsubmission_download', userdate($submissionneweras)));
                }
            } else {
                if ($downloadsubmissions) {
                    $result .= $this->get_renderer()->notification(get_string('nosubmission', 'assign'));
                } else {
                    $result .= $this->get_renderer()->notification(get_string('nofeedback', 'local_assignsubmission_download'));
                }
            }

            $url = new moodle_url('/mod/assign/view.php', ['id' => $this->get_course_module()->id,
                                                                    'action' => 'grading', ]);
            $result .= $this->get_renderer()->continue_button($url);
            $result .= $this->view_footer();
            echo $result;
            $result = '';
            die;
        } else {
            if ($downloadsubmissions) {
                $this->update_lastdownloaded_date($this->get_course_module()->id, $USER->id);
                $this->handle_download_settings(
                    'local_assignsubmission_download',
                    $this->get_course_module()->id,
                    $USER->id,
                    $groupid,
                    $groupingid
                );
            }
            if ($downloadfeedbacks) {
                $this->update_lastdownloaded_date($this->get_course_module()->id, $USER->id, true);
                $this->handle_download_settings(
                    'local_assignsubmission_download_feedback',
                    $this->get_course_module()->id,
                    $USER->id,
                    $groupid,
                    $groupingid
                );
            }
            \mod_assign\event\all_submissions_downloaded::create_from_assign($this)->trigger();

            // Close the session to avoid tab block.
            \core\session\manager::write_close();

            $zipwriter = \core_files\archive_writer::get_stream_writer($zipname, \core_files\archive_writer::ZIP_WRITER);

            foreach ($filesforzipping as $pathinzip => $file) {
                if ($file instanceof \stored_file) {
                    // Most of cases are \stored_file.
                    $zipwriter->add_file_from_stored_file($pathinzip, $file);
                } else if (is_array($file)) {
                    // Save $file as contents, from onlinetext subplugin.
                    $content = reset($file);
                    $zipwriter->add_file_from_string($pathinzip, $content);
                } else if (is_string($file)) {
                    $zipwriter->add_file_from_filepath($pathinzip, $file);
                }
            }

            // Finish the archive.
            $zipwriter->finish();
            die;
            // We will not get here - send_temp_file calls exit.
        }
    }

    /**
     * Converts provided content into a full HTML document.
     *
     * @param string $title The title of the HTML document.
     * @param string $content The main content to be included in the body of the HTML document.
     * @param string $additionalhead Optional. Additional elements to be included in the head of the HTML document.
     *                               Default is an empty string.
     * @return string The complete HTML document as a string.
     */
    public static function convert_content_to_html_doc($title, $content, $additionalhead = '') {
        return <<<HTML
<!doctype html>
<html>
<head>
    <title>$title</title>
    <meta charset="utf-8">
    $additionalhead
</head>
<body>
$content
</body>
</html>
HTML;
    }
}
