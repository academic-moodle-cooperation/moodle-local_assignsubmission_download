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
 * Callback function implementing navigation to printpreview and filerenaming
 *
 * @package       local
 * @subpackage    assignsubmission_download
 * @author        Andreas Krieger
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Function injects navigation node linking to current courses printpreview in settings navigation!
 *
 * @param settings_navigation $navref The settings navigation object
 * @param context $context The context, which the navigation depends upon
 * @return void
 */
function local_assignsubmission_download_extend_settings_navigation(settings_navigation $navref, context $context) {
    global $PAGE, $USER, $SESSION, $CFG;
    // Only add this settings item on non-site course pages.
    if (!$PAGE->course || $PAGE->course->id == SITEID) {
        return;
    }

    if (!$PAGE->cm) {
        return;
    }

    // Only let users with the appropriate capability see this settings item.
    if (!has_capability('local/assignsubmission_download:view', context_course::instance($PAGE->course->id))) {
        return;
    }

    // This is super fast!
    $modinfo = get_fast_modinfo($PAGE->course, -1)->cms[$PAGE->cm->id];
    if ($modinfo->modname != 'assign') {
        return;
    }

    // Check if item already added.
    if ($navref->find('assignsubmission_download_export', navigation_node::TYPE_CUSTOM)) {
        // Already added!
        return;
    }

    $modulesettings = $navref->get('modulesettings');
    if (!$modulesettings) {
        return; // Nothing to do!
    }
    $keys = $modulesettings->get_children_key_list();
    $beforekey = null;
    $i = array_search('modedit', $keys);
    if ($i === false && array_key_exists(0, $keys)) {
        $beforekey = $keys[0];
    } else if (array_key_exists($i + 1, $keys)) {
        $beforekey = $keys[$i + 1];
    }

    if (get_config('local_assignsubmission_download', 'showexport')) {
        $link = new moodle_url('/local/assignsubmission_download/view_printpreview.php', ['id' => $PAGE->cm->id]);
        $childnode = navigation_node::create(
            get_string('pluginname_print', 'local_assignsubmission_download'),
            $link,
            navigation_node::TYPE_SETTING,
            'assignsubmission_download_export_print',
            'assignsubmission_download_export_print');
        $modulesettings->add_node($childnode, $beforekey);
    }

    // Prepare our nodes!
    if (get_config('local_assignsubmission_download', 'showfilerenaming')) {
        $link = new moodle_url('/local/assignsubmission_download/view_filerenaming.php', ['id' => $PAGE->cm->id]);
        $childnode = navigation_node::create(
            get_string('pluginname_submissions', 'local_assignsubmission_download'),
            $link,
            navigation_node::TYPE_SETTING,
            'assignsubmission_download_export',
            'assignsubmission_download_export'
        );
        $modulesettings->add_node($childnode, $beforekey);
    }

}
