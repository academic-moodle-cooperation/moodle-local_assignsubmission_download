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
 * This file contains the privacy preference provider class.
 *
 * @package       local_assignsubmission_download
 * @author        Andreas Krieger (andreas.krieger@tuwien.ac.at)
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_assignsubmission_download\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\user_preference_provider;
use core_privacy\local\request\writer;
use core_privacy\local\request\approved_contextlist;

/**
 * The privacy preference provider.
 *
 * @package       local_assignsubmission_download
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    user_preference_provider,
    \core_privacy\local\metadata\provider {
    // This plugin does store personal user data, even if its just user preferences.

    /**
     * Get the list of user preferences used by this plugin.
     *
     * @param   collection  $collection The initialised collection to add items to.
     * @return  collection  The updated collection.
     */
    public static function get_metadata(collection $collection): collection {

        // Add all user preferences into the collection.
        $collection->add_user_preference('filerenamingpattern', 'privacy:metadata:preference:filerenamingpattern');
        $collection->add_user_preference('clean_filerenaming',  'privacy:metadata:preference:clean_filerenaming');
        $collection->add_user_preference('userfilter', 'privacy:metadata:preference:userfilter');
        $collection->add_user_preference('exportformat', 'privacy:metadata:preference:exportformat');
        $collection->add_user_preference('perpage', 'privacy:metadata:preference:perpage');
        $collection->add_user_preference('optimum',  'privacy:metadata:preference:optimum');
        $collection->add_user_preference('textsize', 'privacy:metadata:preference:textsize');
        $collection->add_user_preference('pageorientation', 'privacy:metadata:preference:pageorientation');
        $collection->add_user_preference('printheader', 'privacy:metadata:preference:printheader');
        $collection->add_user_preference('prevent_nameextension', 'privacy:metadata:preference:prevent_nameextension');
        $collection->add_user_preference('nameofziparchive', 'privacy:metadata:preference:nameofziparchive');
        $collection->add_user_preference('downloadtype_submissions', 'privacy:metadata:preference:downloadtype_submissions');
        $collection->add_user_preference('downloadtype_feedbacks', 'privacy:metadata:preference:downloadtype_feedbacks');

        $collection->add_database_table(
            'local_assignsubm_download', 
            [
                'id' => 'privacy:metadata:local_assignsubm_download:id',
                'cmid' => 'privacy:metadata:local_assignsubm_download:cmid',
                'userid' => 'privacy:metadata:local_assignsubm_download:userid',
                'lastdownloaded' => 'privacy:metadata:local_assignsubm_download:lastdownloaded',
                'filenamingscheme' => 'privacy:metadata:local_assignsubm_download:filenamingscheme',
                'preventnameextension' => 'privacy:metadata:local_assignsubm_download:preventnameextension',
                'cleanfilenames' => 'privacy:metadata:local_assignsubm_download:cleanfilenames',
                'choosegrouping' => 'privacy:metadata:local_assignsubm_download:choosegrouping',
                'choosegroup' => 'privacy:metadata:local_assignsubm_download:choosegroup',
                'zipnamingscheme' => 'privacy:metadata:local_assignsubm_download:zipnamingscheme',
            ],
            'privacy:metadata:local_assignsubm_download',
        );

        $collection->add_database_table(
            'local_assignsubm_feedback',
            [
                'id' => 'privacy:metadata:local_assignsubm_feedback:id',
                'cmid' => 'privacy:metadata:local_assignsubm_feedback:cmid',
                'userid' => 'privacy:metadata:local_assignsubm_feedback:userid',
                'lastdownloaded' => 'privacy:metadata:local_assignsubm_feedback:lastdownloaded',
                'filenamingscheme' => 'privacy:metadata:local_assignsubm_feedback:filenamingscheme',
                'preventnameextension' => 'privacy:metadata:local_assignsubm_feedback:preventnameextension',
                'cleanfilenames' => 'privacy:metadata:local_assignsubm_feedback:cleanfilenames',
                'choosegrouping' => 'privacy:metadata:local_assignsubm_feedback:choosegrouping',
                'choosegroup' => 'privacy:metadata:local_assignsubm_feedback:choosegroup',
                'zipnamingscheme' => 'privacy:metadata:local_assignsubm_feedback:zipnamingscheme',
            ],
            'privacy:metadata:local_assignsubm_feedback',
        );

        return $collection;
    }

    /**
     * Export all user preferences for the plugin.
     *
     * @param int $userid The userid of the user whose data is to be exported.
     */
    public static function export_user_preferences(int $userid) {
        $filerenamingpattern = get_user_preferences('filerenamingpattern', null, $userid);
        if (null !== $filerenamingpattern) {
            $filerenamingpatterndescription = get_string('filerenamingpattern', 'local_assignsubmission_download');
            writer::export_user_preference('local_assignsubmission_download', 'filerenamingpattern',
                    $filerenamingpattern, $filerenamingpatterndescription);
        }

        $cleanfilerenameing = get_user_preferences('clean_filerenaming', null, $userid);
        if (null !== $cleanfilerenameing) {
            $cleanfilerenamingdescription = get_string('clean_filerenaming', 'local_assignsubmission_download');
            writer::export_user_preference('local_assignsubmission_download', 'clean_filerenaming',
                    $cleanfilerenameing, $cleanfilerenamingdescription);
        }

        $userfilter = get_user_preferences('assign_filter', null, $userid);
        if (null !== $userfilter) {
            $userfilterdescription = get_string('userfilter', 'local_assignsubmission_download');
            writer::export_user_preference('local_assignsubmission_download', 'assign_filter',
                    $userfilter, $userfilterdescription);
        }

        $exportformat = get_user_preferences('assign_exportformat', null, $userid);
        if (null !== $exportformat) {
            $exportformatdescription = get_string('exportformat', 'local_assignsubmission_download');
            writer::export_user_preference('local_assignsubmission_download', 'assign_exportformat',
                    $exportformat, $exportformatdescription);
        }

        $perpage = get_user_preferences('assign_perpage', null, $userid);
        if (null !== $perpage) {
            $perpagedescription = get_string('perpage', 'local_assignsubmission_download');
            writer::export_user_preference('local_assignsubmission_download', 'assign_perpage',
                    $perpage, $perpagedescription);
        }

        $optimum = get_user_preferences('assign_optimum', null, $userid);
        if (null !== $optimum) {
            $optimumdescription = get_string('optimum', 'local_assignsubmission_download');
            writer::export_user_preference('local_assignsubmission_download', 'assign_optimum',
                    $optimum, $optimumdescription);
        }

        $textsize = get_user_preferences('assign_textsize', null, $userid);
        if (null !== $textsize) {
            $textsizedescription = get_string('strtextsize', 'local_assignsubmission_download');
            writer::export_user_preference('local_assignsubmission_download', 'assign_textsize',
                    $textsize, $textsizedescription);
        }

        $pageorientation = get_user_preferences('assign_pageorientation', null, $userid);
        if (null !== $pageorientation) {
            $pageorientationdescription = get_string('strpageorientation', 'local_assignsubmission_download');
            writer::export_user_preference('local_assignsubmission_download', 'assign_pageorientation',
                    $pageorientation, $pageorientationdescription);
        }

        $printheader = get_user_preferences('assign_printheader', null, $userid);
        if (null !== $printheader) {
            $printheaderdescription = get_string('strprintheader', 'local_assignsubmission_download');
            writer::export_user_preference('local_assignsubmission_download', 'assign_printheader',
                    $printheader, $printheaderdescription);
        }

        $prevent_nameextension = get_user_preferences('assign_prevent_nameextension', null, $userid);
        if (null !== $prevent_nameextension) {
            $prevent_nameextensiondescription = get_string('prevent_nameextension', 'local_assignsubmission_download');
            writer::export_user_preference('local_assignsubmission_download', 'assign_prevent_nameextension',
                    $prevent_nameextension, $prevent_nameextensiondescription);
        }

        $nameofziparchive = get_user_preferences('assign_nameofziparchive', null, $userid);
        if (null !== $nameofziparchive) {
            $nameofziparchivedescription = get_string('nameofziparchive', 'local_assignsubmission_download');
            writer::export_user_preference('local_assignsubmission_download', 'assign_nameofziparchive',
                    $nameofziparchive, $nameofziparchivedescription);
        }

        $downloadtype_submissions = get_user_preferences('assign_downloadtype_submissions', null, $userid);
        if (null !== $downloadtype_submissions) {
            $downloadtype_submissionsdescription = get_string('downloadtype_submissions', 'local_assignsubmission_download');
            writer::export_user_preference('local_assignsubmission_download', 'assign_downloadtype_submissions',
                    $downloadtype_submissions, $downloadtype_submissionsdescription);
        }

        $downloadtype_feedbacks = get_user_preferences('assign_downloadtype_feedbacks', null, $userid);
        if (null !== $downloadtype_feedbacks) {
            $downloadtype_feedbacksdescription = get_string('downloadtype_feedbacks', 'local_assignsubmission_download');
            writer::export_user_preference('local_assignsubmission_download', 'assign_downloadtype_feedbacks',
                    $downloadtype_feedbacks, $downloadtype_feedbacksdescription);
        }
    }


    /**
     * Delete all personal data for all users in the specified context.
     * 
     * @param \context $context Context to delete data from.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        $DB->delete_records('local_assignsubm_download', ['cmid' => $context->instanceid]);
        $DB->delete_records('local_assignsubm_feedback', ['cmid' => $context->instanceid]);
    }

    /**
     * Delete personal data for the specified user in the specified context.
     * 
     * @param approved_contextlist $contextlist List of contexts to delete data from.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist)
    {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            $DB->delete_records('local_assignsubm_download', ['cmid' => $context->instanceid, 'userid' => $userid]);
            $DB->delete_records('local_assignsubm_feedback', ['cmid' => $context->instanceid, 'userid' => $userid]);
        }
    }
}
