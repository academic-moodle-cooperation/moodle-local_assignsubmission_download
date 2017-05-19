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
 * Initials bar form element
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

/**
 * Assignment grading table initialsbar
 *
 * @package   local_assignsubmission_download
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class moodlequickform_initialsbar extends HTML_QuickForm_element {

    public function moodlequickform_initialsbar($elementname = null,
                                                $elementlabel = null,
                                                $options = null,
                                                $attributes = null) {
        parent::__construct($elementname, $elementlabel, $attributes);
    }

    public function getvalue() {
        return null;
    }

    public function tohtml() {
        global $SESSION, $PAGE;
        $o = '';
        $alpha  = explode(',', get_string('alphabet', 'langconfig'));

        $urlparams = array('id' => $PAGE->cm->id);

        $url = new moodle_url('/local/assignsubmission_download/view_printpreview.php', $urlparams);
        $PAGE->set_url($url);
        
        if ($this->getLabel() === get_string('firstname')) {
            // Bar of first initials.
            $current = (!empty($SESSION->assignment->i_first)) ? $SESSION->assignment->i_first : '';
            $urlvar = 'tifirst';

        } else if ($this->getLabel() === get_string('lastname')) {
            // Bar of last initials.
            $current = (!empty($SESSION->assignment->i_last)) ? $SESSION->assignment->i_last : '';
            $urlvar = 'tilast';
        }

        if ($current) {
            $o .= html_writer::link($PAGE->url->out(false,
                    array($urlvar => '')), get_string('all'));
        } else {
            $o .= html_writer::tag('strong', get_string('all'));
        }


        foreach ($alpha as $letter) {
            if ($letter === $current) {
                $o .= html_writer::tag('strong', $letter);
            } else {
                $o .= html_writer::link($PAGE->url->out(false,
                        array($urlvar => $letter)), $letter);
            }
        }
        return $o;
    }
}