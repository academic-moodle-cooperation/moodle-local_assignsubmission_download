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
 * Settings
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

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_assignsubmission_download',
            get_string('pluginname', 'local_assignsubmission_download'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configtext('assignmentpatch_perpage',
            get_string('perpage_propertyname', 'local_assignsubmission_download'),
            get_string('perpage_propertydescription', 'local_assignsubmission_download'), 100, PARAM_INT , 10));

    $a = new \stdClass();
    $a->entrytoshow = get_string('pluginname_submissions', 'local_assignsubmission_download');
    $settings->add(new admin_setting_configcheckbox('assignsubmission_download_showfilerenaming',
            get_string('show_propertyname', 'local_assignsubmission_download', $a),
            get_string('show_propertydescription', 'local_assignsubmission_download', $a), true));

    $a->entrytoshow = get_string('pluginname_print', 'local_assignsubmission_download');
    $settings->add(new admin_setting_configcheckbox('assignsubmission_download_showexport',
            get_string('show_propertyname', 'local_assignsubmission_download', $a),
            get_string('show_propertydescription', 'local_assignsubmission_download', $a), true));

}