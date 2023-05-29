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
 * Groupsactivity menu form element
 *
 * @package       local
 * @subpackage    assignsubmission_download
 * @author        Günther Bernsteiner
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once('HTML/QuickForm/select.php');

/**
 * Assignment grading table groupsactivitymenu
 *
 * @package       local
 * @subpackage    assignsubmission_download
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class moodlequickform_groupsactivitymenu extends HTML_QuickForm_element {

    private $cm;
    private $url;
    private $aag;
    private $groupmode;

    public function __construct($elementname = null,
                                                       $elementlabel = null,
                                                       $options = null,
                                                       $attributes = null) {
        parent::__construct($elementname, $elementlabel, $attributes);
    }

    public function getvalue() {
        return null;
    }

    /**
     * Sets the input field name
     *
     * @param     string    $name   Input field name attribute
     * @since     1.0
     * @access    public
     * @return    void
     */
    public function setName($name) {
        $this->updateAttributes(array('name' => $name));
    }


    /**
     * Returns the element name
     *
     * @since     1.0
     * @access    public
     * @return    string
     */
    public function getName() {
        return $this->getAttribute('name');
    }
    public function set_data($cm, $url) {
        $this->cm = $cm;
        $this->url = $url;

        if (!$this->groupmode = groups_get_activity_groupmode($this->cm)) {
            $this->render = false;
            return;
        }

        $context = context_module::instance($this->cm->id);
        $this->aag = has_capability('moodle/site:accessallgroups', $context);

        if ($this->groupmode == VISIBLEGROUPS) {
            $this->setLabel(get_string('groupsvisible'));
        } else {
            $this->setLabel(get_string('groupsseparate'));
        }

        if ($this->aag and $this->cm->groupingid) {
            if ($grouping = groups_get_grouping($this->cm->groupingid)) {
                $this->setLabel($this->getLabel() . ' (' . format_string($grouping->name) . ')');
            }
        }
    }

    public function tohtml() {
        global $USER, $OUTPUT;

        if (!($this->url instanceof moodle_url)) {
            if (strpos($this->url, 'http') !== 0) { // Will also work for https
                // Display error if urlroot is not absolute (this causes the non-JS version to break).
                debugging('groups_print_activity_menu requires absolute URL for ' .
                          '$currenturl, not <tt>' . s($this->url) . '</tt>. Example: ' .
                          'groups_print_activity_menu($cm, $CFG->wwwroot . \'/mod/mymodule/view.php?id=13\');',
                          DEBUG_DEVELOPER);
            }
            $this->url = new moodle_url($this->url);
        }

        if (!$groupmode = groups_get_activity_groupmode($this->cm)) {
            return '';
        }

        $context = context_module::instance($this->cm->id);

        if ($groupmode == VISIBLEGROUPS or $this->aag) {
            $allowedgroups = groups_get_all_groups($this->cm->course, 0, $this->cm->groupingid); // Any group in grouping.
        } else {
            $allowedgroups = groups_get_all_groups($this->cm->course, $USER->id, $this->cm->groupingid); // Only assigned groups.
        }

        $activegroup = groups_get_activity_group($this->cm, true, $allowedgroups);

        $groupsmenu = array();
        if ((!$allowedgroups or $groupmode == VISIBLEGROUPS or $this->aag)) {
            $groupsmenu[0] = get_string('allparticipants');
        }

        if ($allowedgroups) {
            foreach ($allowedgroups as $group) {
                $groupsmenu[$group->id] = format_string($group->name);
            }
        }

        if (count($groupsmenu) == 1) {
            $groupname = reset($groupsmenu);
            $output = $groupname;
        } else {
            $select = new single_select($this->url, 'group', $groupsmenu, $activegroup, null, 'selectgroup');
            $output = $this->render_single_select($select);
        }

        return '<div class="groupselector">'.$output.'</div>';

    }

    public function render_single_select(single_select $select) {
        global $PAGE;

        $select = clone($select);
        if (empty($select->formid)) {
            $select->formid = html_writer::random_id('single_select_f');
        }

        $output = '';
        $params = $select->url->params();
        if ($select->method === 'post') {
            $params['sesskey'] = sesskey();
        }
        foreach ($params as $name => $value) {
            $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $name, 'value' => $value));
        }

        if (empty($select->attributes['id'])) {
            $select->attributes['id'] = 'id_group';
        }

        if ($select->disabled) {
            $select->attributes['disabled'] = 'disabled';
        }

        if ($select->tooltip) {
            $select->attributes['title'] = $select->tooltip;
        }

        $select->attributes['class'] = $select->class;

        if ($select->label) {
            $output .= html_writer::label($select->label, $select->attributes['id'], false, $select->labelattributes);
        }

        if ($select->helpicon instanceof help_icon) {
            $output .= $this->render($select->helpicon);
        }
        $output .= html_writer::select($select->options, $select->name, $select->selected, $select->nothing, $select->attributes);

        $go = html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('go')));
        $output .= html_writer::tag('noscript', html_writer::tag('div', $go), array('class' => 'inline'));

        // And finally one more wrapper with class.
        return html_writer::tag('div', $output, array('class' => $select->class));
    }
}
