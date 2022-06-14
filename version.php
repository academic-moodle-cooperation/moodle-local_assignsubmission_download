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
 * Version information
 *
 * @package       local
 * @subpackage    assignsubmission_download
 * @author        Andreas Krieger, Daniel Binder
 * @copyright     2020 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2022040501;
$plugin->requires  = 2022030300;           // Requires this Moodle version!
$plugin->component = 'local_assignsubmission_download';    // To check on upgrade, that module sits in correct place.
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = 'v4.0.1';            // User-friendly version number.

$plugin->dependencies = array(
    'mod_assign' => 2021110900
);
