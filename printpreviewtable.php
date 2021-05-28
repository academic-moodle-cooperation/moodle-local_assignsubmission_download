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
 * This file contains the definition for the printpreview table that is very simlar
 * to assign grading table and subclassses easy_table
 *
 * @package       local
 * @subpackage    assignsubmission_download
 * @author        Günther Bernsteiner
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot.'/mod/assign/locallib.php');

/**
 * Printpreview table definition
 *
 * @package       local
 * @subpackage    assignsubmission_download
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class printpreview_table extends table_sql implements renderable {
    /** @var assign $assignment */
    private $assignment = null;
    /** @var int $perpage */
    private $perpage = 10;
    /** @var int $rownum (global index of current row in table) */
    private $rownum = -1;
    /** @var renderer_base for getting output */
    private $output = null;
    /** @var stdClass gradinginfo */
    private $gradinginfo = null;
    /** @var int $tablemaxrows */
    private $tablemaxrows = 10000;
    /** @var boolean $quickgrading */
    private $quickgrading = false;
    /** @var boolean $hasgrantextension - Only do the capability check once for the entire table */
    private $hasgrantextension = false;
    /** @var array $groupsubmissions - A static cache of group submissions */
    private $groupsubmissions = array();
    /** @var array $submissiongroups - A static cache of submission groups */
    private $submissiongroups = array();
    /** @var string $plugingradingbatchoperations - List of plugin supported batch operations */
    public $plugingradingbatchoperations = array();
    /** @var array $plugincache - A cache of plugin lookups to match a column name to a plugin efficiently */
    private $plugincache = array();
    /** @var array $scale - A list of the keys and descriptions for the custom scale */
    private $scale = null;

    /**
     * overridden constructor keeps a reference to the assignment class that is displaying this table
     *
     * @param assign $assignment The assignment class
     * @param int $perpage how many per page
     * @param string $filter The current filter
     * @param int $rowoffset For showing a subsequent page of results
     * @param string $downloadfilename
     */
    public function __construct(assign $assignment,
                                $perpage,
                                $filter,
                                $rowoffset,
                                $downloadfilename = null,
                                $selectedusers = null) {
        global $CFG, $PAGE, $DB, $USER;
        parent::__construct('mod_assign_grading');
        $this->assignment = $assignment;

        foreach ($assignment->get_feedback_plugins() as $plugin) {
            if ($plugin->is_visible() && $plugin->is_enabled()) {
                foreach ($plugin->get_grading_batch_operations() as $action => $description) {
                    if (empty($this->plugingradingbatchoperations)) {
                        $this->plugingradingbatchoperations[$plugin->get_type()] = array();
                    }
                    $this->plugingradingbatchoperations[$plugin->get_type()][$action] = $description;
                }
            }
        }
        $this->perpage = $perpage;
        $this->quickgrading = false;
        $this->output = $PAGE->get_renderer('mod_assign');
        $this->is_printpreview = true;

        $urlparams = array('id' => $assignment->get_course_module()->id);
        $url = new moodle_url($CFG->wwwroot . '/local/assignsubmission_download/view_printpreview.php', $urlparams);
        $this->define_baseurl($url);

        // Do some business - then set the sql.
        $currentgroup = groups_get_activity_group($assignment->get_course_module(), true);

        if ($rowoffset) {
            $this->rownum = $rowoffset - 1;
        }

        $users = isset($selectedusers) ? $selectedusers : array_keys( $assignment->list_participants($currentgroup, true));
        if (count($users) == 0) {
            // Insert a record that will never match to the sql is still valid.
            $users[] = -1;
        }

        $params = array();
        $params['assignmentid1'] = (int)$this->assignment->get_instance()->id;
        $params['assignmentid2'] = (int)$this->assignment->get_instance()->id;
        $params['assignmentid3'] = (int)$this->assignment->get_instance()->id;
        $params['assignmentid4'] = (int)$this->assignment->get_instance()->id;
        $params['assignmentid5'] = (int)$this->assignment->get_instance()->id;



        $fields = \core_user\fields::for_identity($this->assignment->get_context(), false);
        $extrauserfields = $fields->get_required_fields();

        $userfields = \core_user\fields::for_userpic();
        $userfields->including(...$extrauserfields);
        $selects = $userfields->get_sql('u', false, '', 'id', false)->selects;
        $selects = str_replace(', ', ',', $selects);

        $fields = $selects . ', ';
        $fields .= 'u.id as userid, ';
        $fields .= 's.status as status, ';
        $fields .= 's.id as submissionid, ';
        $fields .= 's.timecreated as firstsubmission, ';
        $fields .= 's.timemodified as timesubmitted, ';
        $fields .= 's.attemptnumber as attemptnumber, ';
        $fields .= 'g.id as gradeid, ';
        $fields .= 'g.grade as grade, ';
        $fields .= 'g.timemodified as timemarked, ';
        $fields .= 'g.timecreated as firstmarked, ';
        $fields .= 'uf.mailed as mailed, ';
        $fields .= 'uf.locked as locked, ';
        $fields .= 'uf.extensionduedate as extensionduedate, ';
        $fields .= 'uf.workflowstate as workflowstate, ';
        $fields .= 'uf.allocatedmarker as allocatedmarker ';

        $submissionmaxattempt = 'SELECT mxs.userid, MAX(mxs.attemptnumber) AS maxattempt
                                 FROM {assign_submission} mxs
                                 WHERE mxs.assignment = :assignmentid4 GROUP BY mxs.userid';
        $grademaxattempt = 'SELECT mxg.userid, MAX(mxg.attemptnumber) AS maxattempt
                            FROM {assign_grades} mxg
                            WHERE mxg.assignment = :assignmentid5 GROUP BY mxg.userid';
        $from = '{user} u
                         LEFT JOIN ( ' . $submissionmaxattempt . ' ) smx ON u.id = smx.userid
                         LEFT JOIN ( ' . $grademaxattempt . ' ) gmx ON u.id = gmx.userid
                         LEFT JOIN {assign_submission} s ON
                            u.id = s.userid AND
                            s.assignment = :assignmentid1 AND
                            s.attemptnumber = smx.maxattempt
                         LEFT JOIN {assign_grades} g ON
                            u.id = g.userid AND
                            g.assignment = :assignmentid2 AND
                            g.attemptnumber = gmx.maxattempt
                         LEFT JOIN {assign_user_flags} uf ON u.id = uf.userid AND uf.assignment = :assignmentid3';

        $userparams = array();
        $userindex = 0;

        list($userwhere, $userparams) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED, 'user');
        $where = 'u.id ' . $userwhere;
        $params = array_merge($params, $userparams);

        // The filters do not make sense when there are no submissions, so do not apply them.
        if ($this->assignment->is_any_submission_plugin_enabled()) {
            if ($filter == ASSIGN_FILTER_SUBMITTED) {
                $where .= ' AND (s.timemodified IS NOT NULL AND
                                 s.status = :submitted) ';
                $params['submitted'] = ASSIGN_SUBMISSION_STATUS_SUBMITTED;

            } else if ($filter == ASSIGN_FILTER_REQUIRE_GRADING) {
                $where .= ' AND (s.timemodified IS NOT NULL AND
                                 s.status = :submitted AND
                                 (s.timemodified > g.timemodified OR g.timemodified IS NULL))';
                $params['submitted'] = ASSIGN_SUBMISSION_STATUS_SUBMITTED;

            } else if (strpos($filter, ASSIGN_FILTER_SINGLE_USER) === 0) {
                $filters = explode('=', $filter);
                $userfilter = (int) array_pop($filters);
                $where .= ' AND (u.id = :userid)';
                $params['userid'] = $userfilter;
            }
        }

        if ($this->assignment->get_instance()->markingallocation) {
            if (has_capability('mod/assign:manageallocations', $this->assignment->get_context())) {
                // Check to see if marker filter is set.
                $markerfilter = (int)get_user_preferences('assign_markerfilter', '');
                if (!empty($markerfilter)) {
                    $where .= ' AND uf.allocatedmarker = :markerid';
                    $params['markerid'] = $markerfilter;
                }
            } else { // Only show users allocated to this marker.
                $where .= ' AND uf.allocatedmarker = :markerid';
                $params['markerid'] = $USER->id;
            }
        }

        if ($this->assignment->get_instance()->markingworkflow) {
            $workflowstates = $this->assignment->get_marking_workflow_states_for_current_user();
            if (!empty($workflowstates)) {
                $workflowfilter = get_user_preferences('assign_workflowfilter', '');
                if ($workflowfilter == ASSIGN_MARKING_WORKFLOW_STATE_NOTMARKED) {
                    $where .= ' AND (uf.workflowstate = :workflowstate OR uf.workflowstate IS NULL OR '.
                        $DB->sql_isempty('assign_user_flags', 'workflowstate', true, true).')';
                    $params['workflowstate'] = $workflowfilter;
                } else if (array_key_exists($workflowfilter, $workflowstates)) {
                    $where .= ' AND uf.workflowstate = :workflowstate';
                    $params['workflowstate'] = $workflowfilter;
                }
            }
        }

        $this->set_sql($fields, $from, $where, $params);

        if ($downloadfilename) {
            $this->is_downloading('pdf', $downloadfilename);
        }

        $columns = array();
        $headers = array();

        // Select.
        if (!$this->is_downloading()) {
            $columns[] = 'select';
            $headers[] = get_string('select') .
                    '<div class="selectall"><label class="accesshide" for="selectall">' . get_string('selectall') . '</label>
                    <input type="checkbox" id="selectall" name="selectall" title="' . get_string('selectall') . '"/></div>';
        }

        // Fullname.
        if (!$this->assignment->is_blind_marking()) {

            $columns[] = 'fullname';
            $headers[] = get_string('fullname');

            foreach ($extrauserfields as $extrafield) {
                $columns[] = $extrafield;
                $headers[] = \core_user\fields::get_display_name($extrafield);
            }
        } else {
            // Record ID.
            $columns[] = 'recordid';
            $headers[] = get_string('recordid', 'assign');
        }

        // Submission plugins.
        if ($assignment->is_any_submission_plugin_enabled()) {
            foreach ($this->assignment->get_submission_plugins() as $plugin) {
                if ($plugin->is_visible() && $plugin->is_enabled()
                &&  $plugin->has_user_summary() && $plugin->get_type() != 'comments') {
                    $index = 'plugin' . count($this->plugincache);
                    $this->plugincache[$index] = array($plugin);
                    $columns[] = $index;
                    $headers[] = $plugin->get_name();
                }
            }

            $columns[] = 'timesubmitted';
            $headers[] = get_string('lastmodifiedsubmission', 'assign');
        }

        // Grade.
        $columns[] = 'grade';
        $headers[] = get_string('grade', 'grades');

        // Time marked.
        $columns[] = 'timemarked';
        $headers[] = get_string('lastmodifiedgrade', 'assign');

        // Feedback plugins.
        foreach ($this->assignment->get_feedback_plugins() as $plugin) {
            $plugin->has_user_summary();
            if ($this->is_downloading()) {
                if ($plugin->is_visible() && $plugin->is_enabled()) {
                    foreach ($plugin->get_editor_fields() as $field => $description) {
                        $index = 'plugin' . count($this->plugincache);
                        $this->plugincache[$index] = array($plugin, $field);
                        $columns[] = $index;
                        $headers[] = $description;
                    }
                }
            } else if ($plugin->is_visible() && $plugin->is_enabled() && $plugin->has_user_summary()) {
                $index = 'plugin' . count($this->plugincache);
                $this->plugincache[$index] = array($plugin);
                $columns[] = $index;
                $headers[] = $plugin->get_name();
            }
        }

        // Load the grading info for all users.
        $this->gradinginfo = grade_get_grades($this->assignment->get_course()->id,
                                              'mod',
                                              'assign',
                                              $this->assignment->get_instance()->id,
                                              $users);
        $this->hasgrantextension = has_capability('mod/assign:grantextension',
                                                  $this->assignment->get_context());

        if (!empty($CFG->enableoutcomes) && !empty($this->gradinginfo->outcomes)) {
            $columns[] = 'outcomes';
            $headers[] = get_string('outcomes', 'grades');
        }

        // Set the columns.
        $this->define_columns($columns);
        $this->define_headers($headers);
        foreach ($extrauserfields as $extrafield) {
             $this->column_class($extrafield, $extrafield);
        }
        // We require at least one unique column for the sort.
        $this->sortable(true, 'userid');
        $this->no_sorting('recordid');
        $this->no_sorting('finalgrade');
        $this->no_sorting('userid');
        $this->no_sorting('select');
        $this->no_sorting('outcomes');

        if ($assignment->get_instance()->teamsubmission) {
            $this->no_sorting('team');
            $this->no_sorting('teamstatus');
        }

        $plugincolumnindex = 0;
        foreach ($this->assignment->get_submission_plugins() as $plugin) {
            if ($plugin->is_visible() && $plugin->is_enabled() && $plugin->has_user_summary()) {
                $submissionpluginindex = 'plugin' . $plugincolumnindex++;
                $this->no_sorting($submissionpluginindex);
            }
        }
        foreach ($this->assignment->get_feedback_plugins() as $plugin) {
            if ($plugin->is_visible() && $plugin->is_enabled() && $plugin->has_user_summary()) {
                $feedbackpluginindex = 'plugin' . $plugincolumnindex++;
                $this->no_sorting($feedbackpluginindex);
            }
        }

        // When there is no data we still want the column headers printed in the csv file.
        if ($this->is_downloading()) {
            $this->export_class_instance()->setup_table($this->assignment->get_course()->shortname,
                                                        $this->assignment->get_course_module(),
                                                        $this->assignment->get_instance(),
                                                        $columns, $headers);
            $this->start_output();
        }
    }

    /**
     * Query the db. Store results in the table object for use by build_table.
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar. Bar
     * will only be used if there is a fullname column defined for the table.
     */
    public function query_db($pagesize, $useinitialsbar=true) {
        global $DB;

        if ($this->countsql === null) {
            $this->countsql = 'SELECT COUNT(1) FROM '.$this->sql->from.' WHERE '.$this->sql->where;
            $this->countparams = $this->sql->params;
        }
        $grandtotal = $DB->count_records_sql($this->countsql, $this->countparams);
        if ($useinitialsbar && !$this->is_downloading()) {
            $this->initialbars($grandtotal > $pagesize);
        }

        list($wsql, $wparams) = $this->get_sql_where();
        if ($wsql) {
            $this->countsql .= ' AND '.$wsql;
            $this->countparams = array_merge($this->countparams, $wparams);

            $this->sql->where .= ' AND '.$wsql;
            $this->sql->params = array_merge($this->sql->params, $wparams);

            $total  = $DB->count_records_sql($this->countsql, $this->countparams);
        } else {
            $total = $grandtotal;
        }

        $this->pagesize($pagesize, $total);

        // Fetch the attempts.
        $sort = $this->get_sql_sort();
        if ($sort) {
            $sort = "ORDER BY $sort";
        }
        $sql = "SELECT
                {$this->sql->fields}
                FROM {$this->sql->from}
                WHERE {$this->sql->where}
                {$sort}";

        if (!$this->is_downloading()) {
            $this->rawdata = $DB->get_records_sql($sql, $this->sql->params, $this->get_page_start(), $this->get_page_size());
        } else {
            $this->rawdata = $DB->get_records_sql($sql, $this->sql->params);
        }
    }

    /**
     * Before adding each row to the table make sure rownum is incremented.
     *
     * @param array $row row of data from db used to make one row of the table.
     * @return array one row for the table
     */
    public function format_row($row) {
        if ($this->rownum < 0) {
            $this->rownum = $this->currpage * $this->pagesize;
        } else {
            $this->rownum += 1;
        }

        return parent::format_row($row);
    }


    /**
     * Add the userid to the row class so it can be updated via ajax.
     *
     * @param stdClass $row The row of data
     * @return string The row class
     */
    public function get_row_class($row) {
        return 'user' . $row->userid;
    }

    /**
     * Return the number of rows to display on a single page.
     *
     * @return int The number of rows per page
     */
    public function get_rows_per_page() {
        return $this->perpage;
    }

    /**
     * Using the current filtering and sorting - load all rows and return a single column from them.
     *
     * @param string $columnname The name of the raw column data
     * @return array of data
     */
    public function get_column_data($columnname) {
        $this->setup();
        $this->currpage = 0;
        $this->query_db($this->tablemaxrows);
        $result = array();
        foreach ($this->rawdata as $row) {
            $result[] = $row->$columnname;
        }
        return $result;
    }

    /**
     * Insert a checkbox for selecting the current row for batch operations.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_select(stdClass $row) {
        $selectcol = '<label class="accesshide" for="selectuser_' . $row->userid . '">';
        $selectcol .= get_string('selectuser', 'assign', fullname($row));
        $selectcol .= '</label>';
        $selectcol .= '<input type="checkbox"
                              id="selectuser_' . $row->userid . '"
                              name="selectedusers"
                              value="' . $row->userid . '"/>';
        $selectcol .= '<input type="hidden"
                              name="grademodified_' . $row->userid . '"
                              value="' . $row->timemarked . '"/>';
        return $selectcol;
    }

    /**
     * Add a column with an ID that uniquely identifies this user in this assignment.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_recordid(stdClass $row) {
        return get_string('hiddenuser', 'assign') .
               $this->assignment->get_uniqueid_for_user($row->userid);
    }

    /**
     * Format a user record for display (link to profile).
     *
     * @param stdClass $row
     * @return string
     */
    public function col_fullname($row) {
        if (!$this->is_downloading()) {
            $courseid = $this->assignment->get_course()->id;
            $link = new moodle_url('/user/view.php', array('id' => $row->id, 'course' => $courseid));
            $fullname = $this->output->action_link($link, fullname($row));
        } else {
            $fullname = fullname($row);
        }

        if (!$this->assignment->is_active_user($row->id)) {
            $suspendedstring = get_string('userenrolmentsuspended', 'grades');
            $fullname .= ' ' . html_writer::empty_tag('img', array('src' => $this->output->image_url('i/enrolmentsuspended'),
                'title' => $suspendedstring, 'alt' => $suspendedstring, 'class' => 'usersuspendedicon'));
            $fullname = html_writer::tag('span', $fullname, array('class' => 'usersuspended'));
        }
        return $fullname;
    }

    /**
     * Return a users grades from the listing of all grade data for this assignment.
     *
     * @param int $userid
     * @return mixed stdClass or false
     */
    private function get_gradebook_data_for_user($userid) {
        if (isset($this->gradinginfo->items[0]) && $this->gradinginfo->items[0]->grades[$userid]) {
            return $this->gradinginfo->items[0]->grades[$userid];
        }
        return false;
    }

    /**
     * For download only - list all the valid options for this custom scale.
     *
     * @param stdClass $row - The row of data
     * @return string A list of valid options for the current scale
     */
    public function col_scale($row) {
        global $DB;

        if (empty($this->scale)) {
            $dbparams = array('id' => -($this->assignment->get_instance()->grade));
            $this->scale = $DB->get_record('scale', $dbparams);
        }

        if (!empty($this->scale->scale)) {
            return implode("\n", explode(',', $this->scale->scale));
        }
        return '';
    }

    /**
     * Format a column of data for display.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_grade(stdClass $row) {
        $o = '';

        $link = '';
        $separator = $this->output->spacer(array(), true);
        $grade = '';
        $gradingdisabled = $this->assignment->grading_disabled($row->id);

        if (!$this->is_downloading()) {
            $name = fullname($row);
            if ($this->assignment->is_blind_marking()) {
                $name = get_string('hiddenuser', 'assign') .
                        $this->assignment->get_uniqueid_for_user($row->userid);
            }
            $icon = $this->output->pix_icon('gradefeedback',
                                            get_string('gradeuser', 'assign', $name),
                                            'mod_assign');
            $urlparams = array('id' => $this->assignment->get_course_module()->id,
                               'rownum' => $this->rownum,
                               'action' => 'grade');
            $url = new moodle_url('/mod/assign/view.php', $urlparams);
            $link = $this->output->action_link($url, $icon);
            $grade .= $link . $separator;
        }

        $grade .= $this->display_grade($row->grade,
                                       $this->quickgrading && !$gradingdisabled,
                                       $row->userid,
                                       $row->timemarked);

        return $grade;
    }

    /**
     * Format a column of data for display
     *
     * @param stdClass $row
     * @return string
     */
    public function col_grademax(stdClass $row) {
        return format_float($this->assignment->get_instance()->grade, 2);
    }

    /**
     * Display a grade with scales etc.
     *
     * @param string $grade
     * @param boolean $editable
     * @param int $userid The user id of the user this grade belongs to
     * @param int $modified Timestamp showing when the grade was last modified
     * @return string The formatted grade
     */
    public function display_grade($grade, $editable, $userid, $modified) {
        if ($this->is_downloading()) {
            if ($this->assignment->get_instance()->grade >= 0) {
                if ($grade == -1 || $grade === null) {
                    return '';
                }
                return format_float($grade, 2).' / '.
                       format_float($this->assignment->get_instance()->grade, 2);
            } else {
                // This is a custom scale.
                $scale = $this->assignment->display_grade($grade, false);
                if ($scale == '-') {
                    $scale = '';
                }
                return $scale;
            }
        }
        return $this->assignment->display_grade($grade, $editable, $userid, $modified);
    }

    /**
     * Format a column of data for display.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_timemarked(stdClass $row) {
        $o = '-';

        if ($row->timemarked && $row->grade !== null && $row->grade >= 0) {
            $o = userdate($row->timemarked);
        }
        if ($row->timemarked && $this->is_downloading()) {
            // Force it for downloads as it affects import.
            $o = userdate($row->timemarked);
        }

        return $o;
    }

    /**
     * Format a column of data for display.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_timesubmitted(stdClass $row) {
        $o = '-';

        if ($row->timesubmitted) {
            $o = userdate($row->timesubmitted);
        }

        return $o;
    }

    /**
     * Write the plugin summary with an optional link to view the full feedback/submission.
     *
     * @param assign_plugin $plugin Submission plugin or feedback plugin
     * @param stdClass $item Submission or grade
     * @param string $returnaction The return action to pass to the
     *                             view_submission page (the current page)
     * @param string $returnparams The return params to pass to the view_submission
     *                             page (the current page)
     * @return string The summary with an optional link
     */
    private function format_plugin_summary_with_link(assign_plugin $plugin,
                                                     stdClass $item,
                                                     $returnaction,
                                                     $returnparams) {
        $link = '';
        $showviewlink = false;

        $summary = $plugin->view_summary($item, $showviewlink);
        $separator = '';
        if ($showviewlink) {
            $viewstr = get_string('view' . substr($plugin->get_subtype(), strlen('assign')), 'assign');
            $icon = $this->output->pix_icon('t/preview', $viewstr);
            $urlparams = array('id' => $this->assignment->get_course_module()->id,
                                                     'sid' => $item->id,
                                                     'gid' => $item->id,
                                                     'plugin' => $plugin->get_type(),
                                                     'action' => 'viewplugin' . $plugin->get_subtype(),
                                                     'returnaction' => $returnaction,
                                                     'returnparams' => http_build_query($returnparams));
            $url = new moodle_url('/mod/assign/view.php', $urlparams);
            $link = $this->output->action_link($url, $icon);
            $separator = $this->output->spacer(array(), true);
        }

        return $link . $separator . $summary;
    }


    /**
     * Format the submission and feedback columns.
     *
     * @param string $colname The column name
     * @param stdClass $row The submission row
     * @return mixed string or NULL
     */
    public function other_cols($colname, $row) {
        // For extra user fields the result is already in $row.
        if (empty($this->plugincache[$colname])) {
            return $row->$colname;
        }

        // This must be a plugin field.
        $plugincache = $this->plugincache[$colname];

        $plugin = $plugincache[0];

        $field = null;
        if (isset($plugincache[1])) {
            $field = $plugincache[1];
        }

        if ($plugin->is_visible() && $plugin->is_enabled()) {
            if ($plugin->get_subtype() == 'assignsubmission') {
                if ($this->assignment->get_instance()->teamsubmission) {
                    $group = false;
                    $submission = false;

                    $this->get_group_and_submission($row->id, $group, $submission, -1);
                    if ($submission) {
                        if ($submission->status == ASSIGN_SUBMISSION_STATUS_REOPENED) {
                            // For a newly reopened submission - we want to show the previous submission in the table.
                            $this->get_group_and_submission($row->id, $group, $submission, $submission->attemptnumber - 1);
                        }
                        if (isset($field)) {
                            return $plugin->get_editor_text($field, $submission->id);
                        }
                        return $this->format_plugin_summary_with_link($plugin,
                                                                      $submission,
                                                                      'grading',
                                                                      array());
                    }
                } else if ($row->submissionid) {
                    if ($row->status == ASSIGN_SUBMISSION_STATUS_REOPENED) {
                        // For a newly reopened submission - we want to show the previous submission in the table.
                        $submission = $this->assignment->get_user_submission($row->userid, false, $row->attemptnumber - 1);
                    } else {
                        $submission = new stdClass();
                        $submission->id = $row->submissionid;
                        $submission->timecreated = $row->firstsubmission;
                        $submission->timemodified = $row->timesubmitted;
                        $submission->assignment = $this->assignment->get_instance()->id;
                        $submission->userid = $row->userid;
                        $submission->attemptnumber = $row->attemptnumber;
                    }
                    // Field is used for only for import/export and refers the the fieldname for the text editor.
                    if (isset($field)) {
                        return $plugin->get_editor_text($field, $submission->id);
                    }
                    return $this->format_plugin_summary_with_link($plugin,
                                                                  $submission,
                                                                  'grading',
                                                                  array());
                }
            } else {
                $grade = null;
                if (isset($field)) {
                    return $plugin->get_editor_text($field, $row->gradeid);
                }

                if ($row->gradeid) {
                    $grade = new stdClass();
                    $grade->id = $row->gradeid;
                    $grade->timecreated = $row->firstmarked;
                    $grade->timemodified = $row->timemarked;
                    $grade->assignment = $this->assignment->get_instance()->id;
                    $grade->userid = $row->userid;
                    $grade->grade = $row->grade;
                    $grade->mailed = $row->mailed;
                    $grade->attemptnumber = $row->attemptnumber;
                }
                if ($this->quickgrading && $plugin->supports_quickgrading()) {
                    return $plugin->get_quickgrading_html($row->userid, $grade);
                } else if ($grade) {
                    return $this->format_plugin_summary_with_link($plugin,
                                                                  $grade,
                                                                  'grading',
                                                                  array());
                }
            }
        }
        return '';
    }

    /**
     * Use a static cache to try and reduce DB calls.
     *
     * @param int $userid The user id for this submission
     * @param int $group The groupid (returned)
     * @param stdClass|false $submission The stdClass submission or false (returned)
     * @param int $attemptnumber Return a specific attempt number (-1 for latest)
     */
    protected function get_group_and_submission($userid, &$group, &$submission, $attemptnumber) {
        $group = false;
        if (isset($this->submissiongroups[$userid])) {
            $group = $this->submissiongroups[$userid];
        } else {
            $group = $this->assignment->get_submission_group($userid, false);
            $this->submissiongroups[$userid] = $group;
        }

        $groupid = 0;
        if ($group) {
            $groupid = $group->id;
        }

        // Static cache is keyed by groupid and attemptnumber.
        // We may need both the latest and previous attempt in the same page.
        if (isset($this->groupsubmissions[$groupid . ':' . $attemptnumber])) {
            $submission = $this->groupsubmissions[$groupid . ':' . $attemptnumber];
        } else {
            $submission = $this->assignment->get_group_submission($userid, $groupid, false, $attemptnumber);
            $this->groupsubmissions[$groupid . ':' . $attemptnumber] = $submission;
        }
    }

    /**
     * Get, and optionally set, the export class.
     * Remark: overridden function, to make it handle special 'pdf' case (AK)
     * @param $exportclass (optional) if passed, set the table to use this export class.
     * @return table_default_export_format_parent the export class in use (after any set).
     */
    public function export_class_instance($exportclass = null) {
        if (!is_null($exportclass)) {
            $this->started_output = true;
            $this->exportclass = $exportclass;
            $this->exportclass->table = $this;
        } else if (is_null($this->exportclass) && !empty($this->download)) {
            // There is currently (3.1) no writer.php for pdf; we use our mtablepdf.
            if ($this->download === 'pdf') {
                $this->exportclass = new table_pdf_export_format($this, $this->download);
            } else {
                $this->exportclass = new table_dataformat_export_format($this, $this->download);
            }
            if (!$this->exportclass->document_started()) {
                $this->exportclass->start_document($this->filename);
            }
        }
        return $this->exportclass;
    }

    /**
     * Return things to the renderer.
     *
     * @return string the assignment name
     */
    public function get_assignment_name() {
        return $this->assignment->get_instance()->name;
    }

    /**
     * Return things to the renderer.
     *
     * @return int the course module id
     */
    public function get_course_module_id() {
        return $this->assignment->get_course_module()->id;
    }

    /**
     * Return things to the renderer.
     *
     * @return int the course id
     */
    public function get_course_id() {
        return $this->assignment->get_course()->id;
    }

    /**
     * Return things to the renderer.
     *
     * @return stdClass The course context
     */
    public function get_course_context() {
        return $this->assignment->get_course_context();
    }

    /**
     * Return things to the renderer.
     *
     * @return bool Does this assignment accept submissions
     */
    public function submissions_enabled() {
        return $this->assignment->is_any_submission_plugin_enabled();
    }

    /**
     * Return things to the renderer.
     *
     * @return bool Can this user view all grades (the gradebook)
     */
    public function can_view_all_grades() {
        $context = $this->assignment->get_course_context();
        return has_capability('gradereport/grader:view', $context) &&
               has_capability('moodle/grade:viewall', $context);
    }

    /**
     * Override the table show_hide_link to not show for select column.
     *
     * @param string $column the column name, index into various names.
     * @param int $index numerical index of the column.
     * @return string HTML fragment.
     */
    protected function show_hide_link($column, $index) {
        if ($index > 0) {
            return parent::show_hide_link($column, $index);
        }
        return '';
    }

    /**
     * This function is not part of the public api.
     */
    public function start_html() {
        global $OUTPUT;
        $this->initialbars(true);
        return parent::start_html();
    }

}
