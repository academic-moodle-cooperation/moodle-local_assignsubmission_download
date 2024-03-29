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
 * This file is the entry point to the printpreview module.
 *
 * @package       local
 * @subpackage    assignsubmission_download
 * @author        Andreas Krieger
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');

$id = required_param('id', PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'assign');

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

require_capability('local/assignsubmission_download:view', $context);

$printpreview = new \local_assignsubmission_download\local\printpreview($context, $cm, $course);

$urlparams = array('id' => $id,
                  'action' => optional_param('action', '', PARAM_TEXT),
                  'rownum' => optional_param('rownum', 0, PARAM_INT),
                  'useridlistid' => optional_param('useridlistid', $printpreview->get_useridlist_key_id(), PARAM_ALPHANUM));

$url = new moodle_url('/local/assignsubmission_download/view_printpreview.php', $urlparams);
$PAGE->set_url($url);

$PAGE->navbar->add(get_string('pluginname_print', 'local_assignsubmission_download'),
                   new moodle_url('/local/assignsubmission_download/view_printpreview.php',
                                  array('id' => $id)));

$output = $PAGE->get_renderer('local_assignsubmission_download');

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Get the class to render the page.
echo $printpreview->view(optional_param('action', 'grading', PARAM_TEXT));
