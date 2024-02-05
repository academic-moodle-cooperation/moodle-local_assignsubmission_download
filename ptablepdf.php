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
 * This file contains an adaptor from printpreview table to table export class
 *
 * @package       local
 * @subpackage    assignsubmission_download
 * @author        Günther Bernsteiner
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once('mtablepdf.php');
require_once('printpreviewsettingsform.php');


/**
 * Adaptor to hand over the information from printpreview table to table export class
 *
 * @package       local
 * @subpackage    assignsubmission_download
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class table_pdf_export_format extends table_default_export_format_parent {

    private $pdf;
    private $filename;
    private $columns;
    private $widths;
    private $rownum;

    public function start_document($filename) {
        $this->filename = $filename;
    }

    public function start_table($sheettitle) {
        $this->documentstarted = true;
        $this->rownum = 0;
    }

    public function output_headers($headers) {
        $this->rownum++;
    }

    public function add_seperator() {
        $this->rownum++;
    }

    public function setup_table($coursename, $coursemodule, $assignment, $columns, $titles) {
        global $CFG, $USER, $SESSION;

        $this->columns = $columns;
        $this->widths = [];
        // Column width calculation.
        $this->widths['recordid']      = 10;  // ID.
        $this->widths['fullname']      = 10;   // Name.
        $this->widths['idnumber']      = 6;   // Matrikelnr.
        $this->widths['email']         = 6;   // Email.
        $this->widths['phone1']        = 3;   // Telefon Festnetz.
        $this->widths['phone2']        = 3;   // Telefon Mobil.
        $this->widths['department']    = 3;   // Abteilung.
        $this->widths['institution']   = 3;   // Institution.
        $this->widths['grade']         = 6;   // Bewertung.
        $this->widths['timesubmitted'] = 10;  // Zuletzt geaendert (Abgabe).
        $this->widths['timemarked']    = 10;  // Zuletzt geaendert (Bewertung).
        $this->widths['plugin0']       = 10;  // Dateiabgabe.
        $this->widths['plugin1']       = 10;  // Kommentar.
        $this->widths['plugin2']       = 10;  // Abgabe Kommentar.

        // Hide columns.
        $pdftitles = [];
        $pdfwidths = [];
        $sum = 0;

        foreach ($this->columns as $key => $field) {
            if (empty($SESSION->flextable['mod_assign_grading']->collapse[$field])) {
                $w = (array_key_exists($field, $this->widths)) ? $this->widths[$field] : 0;
                $pdftitles[] = $titles[$key];
                $pdfwidths[] = ['mode' => 'Relativ', 'value' => $w];
                $sum += $w;
            }
        }

        // Generate pdf.
        $orientation = get_user_preferences('assign_pageorientation', 0) ? MTablePDF::LANDSCAPE : MTablePDF::PORTRAIT;
        $this->pdf = new MTablePDF($orientation, $pdfwidths);

        // Set document information.
        $this->pdf->SetCreator('TUWEL');
        $this->pdf->SetAuthor($USER->firstname . ' ' . $USER->lastname);
        $this->pdf->settitles($pdftitles);

        // Set pdf header.
        $headercourse = get_string('course') . ':';

        $headerassignment = get_string('pdf_assignment', 'local_assignsubmission_download') . ':';
        $assignmentname = $assignment->name; // Aufgabe.

        $headerallowsubmissionfromdate = get_string('pdf_availablefrom', 'local_assignsubmission_download') . ':';
        $allowsubmissionsfromdate = ($assignment->allowsubmissionsfromdate == 0) ? get_string(
                'pdf_notactive', 'local_assignsubmission_download') : userdate($assignment->allowsubmissionsfromdate);

        $headerduedate = get_string('pdf_duedate', 'local_assignsubmission_download') . ':';
        $duedate = ($assignment->duedate == 0) ? get_string(
                'pdf_notactive', 'local_assignsubmission_download') : userdate($assignment->duedate);

        $headerview = get_string('pdf_view', 'local_assignsubmission_download');
        $viewname = get_string('submissions', 'local_assignsubmission_download');

        $context = context_module::instance($coursemodule->id);
        $aag = has_capability('moodle/site:accessallgroups', $context);
        $groupmode = groups_get_activity_groupmode($coursemodule);
        $group = groups_get_activity_group($coursemodule);

        if (!$groupmode) {
            $headergroup = '';
            $groupname = '';
        } else {
            if ($groupmode == VISIBLEGROUPS || $aag) {
                $headergroup = get_string('groupsvisible');
                $allowedgroups = groups_get_all_groups($coursemodule->course, 0, $coursemodule->groupingid);
            } else {
                $headergroup = get_string('groupsseparate');
                $allowedgroups = groups_get_all_groups($coursemodule->course, $USER->id, $coursemodule->groupingid);
            }
            $groupname = ($group == 0) ? get_string('allparticipants') : format_string($allowedgroups[$group]->name);
        }

        $this->pdf->setheadertext($headercourse, $coursename,
                            $headerallowsubmissionfromdate, $allowsubmissionsfromdate,
                            $headerview, $viewname,
                            $headerassignment, $assignmentname,
                            $headerduedate, $duedate,
                            $headergroup, $groupname);

        $printheader = get_user_preferences('assign_printheader', 0);
        $this->pdf->showheaderfooter($printheader);

        $exportformat = get_user_preferences('assign_exportformat', 0);
        $this->pdf->setoutputformat($exportformat);

        $optimum = get_user_preferences('assign_optimum', 0);
        $perpage = get_user_preferences('assign_perpage', get_config('local_assignsubmission_download', 'assignmentpatch_perpage'));
        $perpage = ($optimum) ? get_config('local_assignsubmission_download', 'assignmentpatch_perpage') : $perpage;
        $this->pdf->setrowsperpage($perpage);

        $textsize = get_user_preferences('assign_textsize', 0);
        switch ($textsize) {
            case "0":
                $this->pdf->setfontsize(MTablePDF::FONTSIZE_SMALL);
                break;
            case "1":
                $this->pdf->setfontsize(MTablePDF::FONTSIZE_MEDIUM);
                break;
            case "2":
                $this->pdf->setfontsize(MTablePDF::FONTSIZE_LARGE);
                break;
        }
    }

    public function add_data($row) {
        global $SESSION;

        // Hide columns.
        $pdfrow = [];
        foreach ($this->columns as $key => $field) {
            if (empty($SESSION->flextable['mod_assign_grading']->collapse[$field])) {
                $pdfrow[] = strip_tags($row[$key]);
            }
        }
        $this->pdf->addrow($pdfrow);
        $this->rownum++;
        return true;
    }

    public function finish_table() {

    }

    public function finish_document() {
        $this->pdf->generate($this->filename);
        exit;
    }
}
