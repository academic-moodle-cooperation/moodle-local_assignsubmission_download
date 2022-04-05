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
 *
 * @package       moodle311
 * @author        Simeon Naydenov (moniNaydenov@gmail.com)
 * @copyright     2021
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

function xmldb_local_assignsubmission_download_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2021051800) {
        global $DB;
        $configs = [
            'assignmentpatch_perpage' => 'assignmentpatch_perpage',
            'assignsubmission_download_showfilerenaming' => 'showfilerenaming',
            'assignsubmission_download_showexport' => 'showexport'
        ];
        foreach ($configs as $oldname => $newname) {
            $config = $DB->get_record('config', ['name' => $oldname]);
            if ($config) {
                $newconfig = new \stdClass;
                $newconfig->plugin = 'local_assignsubmission_download';
                $newconfig->name = $newname;
                $newconfig->value = $config->value;
                if (!$DB->record_exists('config_plugins',
                   ['plugin' => 'local_assignsubmission_download',
                    'name' => $newname])) {
                    $DB->insert_record('config_plugins', $newconfig);
                }
                $DB->delete_records('config', ['id' => $config->id]);
            }
        }
        // Assignsubmission Download savepoint reached.
        upgrade_plugin_savepoint(true, 2021051800, 'local', 'assignsubmission_download');
    }
    if ($oldversion < 2021051802) {

        // Define table local_assignsubm_download to be created.
        $table = new xmldb_table('local_assignsubm_download');

        // Adding fields to table local_assignsubm_download.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('cmid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('lastdownloaded', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table local_assignsubm_download.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_assignsubm_download.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Assignsubmission_download savepoint reached.
        upgrade_plugin_savepoint(true, 2021051802, 'local', 'assignsubmission_download');
    }
    return true;
}
