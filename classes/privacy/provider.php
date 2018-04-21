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
 * The local_assignsubmission_download privacy metadata provider.
 *
 * @package       local
 * @subpackage    assignsubmission_download
 * @author        Andreas Krieger (andreas.krieger@tuwien.ac.at)
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_assignsubmission_download\privacy;

use core_privacy\local\metadata\collection;
 
class provider implements 
        // This plugin does store personal user data.
        \core_privacy\local\metadata\provider

        // This plugin is a subplugin of assign and must meet that contract.
        // \mod_assign\privacy\assign_provider 
                            {
 
    public static function get_metadata(collection $collection) : collection {
 
        // Here you will add more items into the collection.
        $collection->add_user_preference('asd_filerenamingpattern',
            get_string('filerenamingpattern', 'local_assignsubmission_download'));
        $collection->add_user_preference('asd_clean_filerenaming',
            get_string('clean_filerenaming', 'local_assignsubmission_download'));

        $collection->add_user_preference('asd_userfilter',
            get_string('userfilter', 'local_assignsubmission_download'));
        $collection->add_user_preference('asd_exportformat',
            get_string('exportformat', 'local_assignsubmission_download'));
        $collection->add_user_preference('asd_perpage',
            get_string('perpage', 'local_assignsubmission_download'));
        $collection->add_user_preference('asd_optimum',
            get_string('optimum', 'local_assignsubmission_download'));
        $collection->add_user_preference('asd_textsize',
            get_string('strtextsize', 'local_assignsubmission_download'));
        $collection->add_user_preference('asd_pageorientation',
            get_string('strpageorientation', 'local_assignsubmission_download', );
        $collection->add_user_preference('asd_printheader',
            get_string('strprintheader', 'local_assignsubmission_download'));
            
        return $collection;
    }
}
