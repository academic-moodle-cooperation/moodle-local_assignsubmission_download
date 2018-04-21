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
 * The local_assignsubmission_download privacy preference provider.
 *
 * @package       local
 * @subpackage    assignsubmission_download
 * @author        Andreas Krieger (andreas.krieger@tuwien.ac.at)
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_assignsubmission_download\privacy;

class preference_provider implements 
        // This plugin does store personal user data.
        \core_privacy\local\metadata\preference_provider {
    
    /**
    * Export all user preferences for the plugin.
    *
    * @param   int         $userid The userid of the user whose data is to be exported.
    */
   public static function export_user_preferences(int $userid) {
       $filerenamingpattern = get_user_preferences('filerenamingpattern', null, $userid);
       if (null !== $filerenamingpattern) {
           $filerenamingpatterndescription =
                   get_string('filerenamingpattern', 'assignsubmission_download');
           writer::export_user_preference('local_assignsubmission_download', 'filerenamingpattern',
                   $filerenamingpattern, $filerenamingpatterndescription);
       }

       $clean_filerenaming = get_user_preferences('clean_filerenaming', null, $userid);
       if (null !== $clean_filerenaming) {
           $clean_filerenamingdescription =
                   get_string('clean_filerenaming', 'assignsubmission_download');
           writer::export_user_preference('local_assignsubmission_download', 'clean_filerenaming',
                   $clean_filerenaming, $clean_filerenamingdescription);
       }

       $assign_filter = get_user_preferences('assign_filter', null, $userid);
       if (null !== $assign_filter) {
           $assign_filterdescription =
                   get_string('userfilter', 'assignsubmission_download');
           writer::export_user_preference('local_assignsubmission_download', 'assign_filter',
                   $assign_filter, $assign_filterdescription);
       }

       $assign_exportformat = get_user_preferences('assign_exportformat', null, $userid);
       if (null !== $assign_exportformat) {
           $assign_exportformatdescription =
                   get_string('exportformat', 'assignsubmission_download');
           writer::export_user_preference('local_assignsubmission_download', 'assign_exportformat',
                   $assign_exportformat, $assign_exportformatdescription);
       }

       $assign_perpage = get_user_preferences('assign_perpage', null, $userid);
       if (null !== $assign_perpage) {
           $assign_perpagedescription =
                   get_string('perpage', 'assignsubmission_download');
           writer::export_user_preference('local_assignsubmission_download', 'assign_perpage',
                   $assign_perpage, $assign_perpagedescription);
       }

       $assign_optimum = get_user_preferences('assign_optimum', null, $userid);
       if (null !== $assign_optimum) {
           $assign_optimumdescription =
                   get_string('optimum', 'assignsubmission_download');
           writer::export_user_preference('local_assignsubmission_download', 'assign_optimum',
                   $assign_optimum, $assign_optimumdescription);
       }

       $assign_textsize = get_user_preferences('assign_textsize', null, $userid);
       if (null !== $assign_textsize) {
           $assign_textsizedescription =
                   get_string('strtextsize', 'assignsubmission_download');
           writer::export_user_preference('local_assignsubmission_download', 'assign_textsize',
                   $assign_textsize, $assign_textsizedescription);
       }

       $assign_pageorientation = get_user_preferences('assign_pageorientation', null, $userid);
       if (null !== $assign_pageorientation) {
           $assign_pageorientationdescription =
                   get_string('strpageorientation', 'assignsubmission_download');
           writer::export_user_preference('local_assignsubmission_download', 'assign_pageorientation',
                   $assign_pageorientation, $assign_pageorientationdescription);
       }

       $assign_printheader = get_user_preferences('assign_printheader', null, $userid);
       if (null !== $assign_printheader) {
           $assign_printheaderdescription =
                   get_string('strprintheader', 'assignsubmission_download');
           writer::export_user_preference('local_assignsubmission_download', 'assign_printheader',
                   $assign_printheader, $assign_printheaderdescription);
       }
   }
}
