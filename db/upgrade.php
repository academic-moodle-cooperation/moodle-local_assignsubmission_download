<?php

/**
 *
 * @package       moodle311
 * @author        Simeon Naydenov (moniNaydenov@gmail.com)
 * @copyright     2021
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

function xmldb_local_assignsubmission_download_upgrade($oldversion) {

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
}
