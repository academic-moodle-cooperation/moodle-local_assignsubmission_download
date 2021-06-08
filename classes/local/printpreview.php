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
 * Printpreview class extending assign
 *
 * @package       local
 * @subpackage    assignsubmission_download
 * @author        Günther Bernsteiner
 * @author        Andreas Krieger
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_assignsubmission_download\local;

use assign;
use assign_form;
use assign_header;
use help_icon;
use html_writer;
use mod_assign_printpreview_settings_form;
use moodle_url;
use printpreview_table;
use stdClass;
use url_select;

defined('MOODLE_INTERNAL') || die();


/**
 * The printpreview class, extending assign.
 *
 * @package       local
 * @subpackage    assignsubmission_download
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class printpreview extends assign {

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
        $ifirst = optional_param('tifirst', false, PARAM_ALPHA);
        if (!($ifirst === false)) {
            $SESSION->assignment->i_first = $ifirst;
        }
        $ilast = optional_param('tilast', false, PARAM_ALPHA);
        if (!($ilast === false)) {
            $SESSION->assignment->i_last = $ilast;
        }

        $o .= $this->view_printpreview_page();

        return $o;
    }

    /**
     * View entire printpreview page.
     *
     * @return string
     */
    protected function view_printpreview_page() {
        global $CFG;

        $o = '';
        // Need submit permission to submit an assignment.
        require_capability('mod/assign:grade', $this->get_context());
        require_once($CFG->dirroot . '/local/assignsubmission_download/printpreviewsettingsform.php');
        require_once($CFG->dirroot . '/local/assignsubmission_download/printpreviewtable.php');

        // Only load this if it is.

        $this->process_save_printpreview_settings();
        $o .= $this->view_printpreview_table();
        $o .= $this->view_footer();

        \local_assignsubmission_download\event\assignsubmission_download_table_viewed::create_from_assign($this)->trigger();

        return $o;
    }

    /**
     * View the grading table of all submissions for this assignment.
     *
     * @return string
     */
    protected function view_printpreview_table() {
        global $CFG, $PAGE, $USER, $OUTPUT;

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
        $gradingmanager = get_grading_manager($this->get_context(), 'mod_assign', 'submissions');

        $filter  = get_user_preferences('assign_filter', '');
        $perpage = get_user_preferences('assign_perpage', get_config('local_assignsubmission_download', 'assignmentpatch_perpage'));
        $optimum = get_user_preferences('assign_optimum', 0);
        $perpage = ($perpage <= 0 || $optimum) ? get_config('local_assignsubmission_download', 'assignmentpatch_perpage') : $perpage;
        $optimum = ($perpage == 0 || $perpage == '') ? 1 : 0;

        $textsize = get_user_preferences('assign_textsize', 0);
        $pageorientation = get_user_preferences('assign_pageorientation', 0);
        $printheader = get_user_preferences('assign_printheader', 1);

        $controller = $gradingmanager->get_active_controller();
        $showquickgrading = empty($controller);
        $quickgrading = get_user_preferences('assign_quickgrading', false);

        $urlparams = array('id' => $this->get_course_module()->id, 'action' => 'grading');
        $currenturl = new moodle_url($CFG->wwwroot . '/local/assignsubmission_download/view_printpreview.php', $urlparams);

        $printpreviewsettingsformparams = array('cm' => $this->get_course_module(),
                                                'contextid' => $this->get_context()->id,
                                                'currenturl' => $currenturl,
                                                'userid' => $USER->id,
                                                'submissionsenabled' => $this->is_any_submission_plugin_enabled(),
                                                'textsize' => $textsize,
                                                'pageorientation' => $pageorientation,
                                                'printheader' => $printheader);

        $classoptions = array('class' => 'gradingbatchoperationsform');
        // Print options for changing the filter and changing the number of results per page.
        $printpreviewsettingsform = new mod_assign_printpreview_settings_form(null,
                                                                  $printpreviewsettingsformparams,
                                                                  'post',
                                                                  '',
                                                                  $classoptions);
        $printpreviewsettingsdata = new stdClass();
        $printpreviewsettingsdata->filter = $filter;
        $printpreviewsettingsdata->grpperpage['perpage'] = $perpage;
        $printpreviewsettingsdata->grpperpage['optimum'] = $optimum;
        $printpreviewsettingsdata->textsize = $textsize;
        $printpreviewsettingsdata->pageorientation = $pageorientation;
        $printpreviewsettingsdata->printheader = $printheader;
        $printpreviewsettingsform->set_data($printpreviewsettingsdata);

        $actionformtext = $this->get_renderer()->render($gradingactions);
        $header = new assign_header($this->get_instance(),
                                    $this->get_context(),
                                    false,
                                    $this->get_course_module()->id,
                                    get_string('grading', 'assign'),
                                    $actionformtext);
        $o .= $this->get_renderer()->render($header);

        $o .= $this->get_renderer()->render(new assign_form('printpreviewsettingsform',
                                                            $printpreviewsettingsform
                                                            ));

        // Plagiarism update status apearring in the grading book.
        if (!empty($CFG->enableplagiarism)) {
            require_once($CFG->libdir . '/plagiarismlib.php');
            $o .= plagiarism_update_status($this->get_course(), $this->get_course_module());
        }

        // Load and print the table of submissions.
        $o .= html_writer::start_tag('div', array('class' => 'table_printpreview'));

        $helpicon = new help_icon('data_preview', 'local_assignsubmission_download');
        $o .= html_writer::tag('div', get_string('data_preview', 'local_assignsubmission_download')
           .$OUTPUT->render($helpicon), array('class' => 'data_bold'));

        $gradingtable = new printpreview_table($this, $perpage, $filter, 0, null);
        $o .= $PAGE->get_renderer('local_assignsubmission_download')->render($gradingtable);

        $o .= html_writer::end_tag('div');
        return $o;
    }

    /**
     * Save grading options.
     *
     * @return void
     */
    protected function process_save_printpreview_settings() {
        global $CFG, $USER, $SESSION;

        // Include grading options form.
        require_once($CFG->dirroot . '/local/assignsubmission_download/printpreviewsettingsform.php');

        // Need submit permission to submit an assignment.
        require_capability('mod/assign:grade', $this->get_context());

        $urlparams = array('id' => $this->get_course_module()->id, 'action' => 'grading');
        $currenturl = new moodle_url($CFG->wwwroot . '/local/assignsubmission_download/view_printpreview.php', $urlparams);

        $printpreviewsettingsparams = array('cm' => $this->get_course_module(),
                                            'currenturl' => $currenturl,
                                            'contextid' => $this->get_context()->id,
                                            'userid' => $USER->id,
                                            'submissionsenabled' => $this->is_any_submission_plugin_enabled());

        $mform = new mod_assign_printpreview_settings_form(null, $printpreviewsettingsparams);

        if ($data = $mform->get_data()) {
            set_user_preference('assign_filter', $data->filter);
            set_user_preference('assign_exportformat', $data->exportformat);
            set_user_preference('assign_perpage',
                    isset($data->grpperpage['perpage']) ? $data->grpperpage['perpage'] : get_config('local_assignsubmission_download', 'assignmentpatch_perpage'));
            set_user_preference('assign_optimum', $data->grpperpage['optimum']);
            set_user_preference('assign_textsize', isset($data->textsize) ? $data->textsize : 0);
            set_user_preference('assign_pageorientation', isset($data->pageorientation) ? $data->pageorientation : 0);
            set_user_preference('assign_printheader', $data->printheader);

            $SESSION->selectedusers = explode(',', $data->selectedusers);
            // Download submissions.
            if (isset($data->submittoprint)) {
                $this->export_printpreview_table();
            }
        }
    }

    /**
     * Finally export to pdf
     */
    protected function export_printpreview_table() {
        global $CFG, $USER, $SESSION, $PAGE;

        require_once($CFG->dirroot.'/local/assignsubmission_download/mtablepdf.php');
        require_once($CFG->dirroot.'/local/assignsubmission_download/ptablepdf.php');
        require_once($CFG->dirroot.'/local/assignsubmission_download/printpreviewtable.php');

        $PAGE->set_pagelayout('popup');

        $filter  = get_user_preferences('assign_filter', '');
        $perpage = get_user_preferences('assign_perpage', get_config('local_assignsubmission_download', 'assignmentpatch_perpage'));
        $optimum = get_user_preferences('assign_optimum', 0);
        $perpage = ($perpage <= 0 || $optimum) ? get_config('local_assignsubmission_download', 'assignmentpatch_perpage') : $perpage;
        $optimum = ($perpage == 0 || $perpage == '') ? 1 : 0;
        $selectedusers = $SESSION->selectedusers;

        \local_assignsubmission_download\event\assignsubmission_download_table_downloaded::create_from_assign($this)->trigger();

        $filename = $this->get_course()->fullname.'-'.$this->get_instance()->name;
        $export = new printpreview_table($this, $perpage, $filter, 0, $filename, $selectedusers);
        $PAGE->get_renderer('local_assignsubmission_download')->render($export);

        return;
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
            $currenturl = new moodle_url('/local/assignsubmission_download/view_printpreview.php', array('id' => $cm->id));
        }

        $currenturl->params($params);
        $PAGE->set_url($currenturl);
    }

}