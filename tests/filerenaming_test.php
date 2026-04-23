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
 * Unit tests for the filerenaming class.
 *
 * @package     local_assignsubmission_download
 * @copyright   2026 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class filerenaming_test extends \advanced_testcase {
    /**
     * Ensure the saved last-download settings use the current form values.
     */
    public function test_handle_download_settings_uses_current_download_settings(): void {
        global $CFG, $DB;

        $this->resetAfterTest(true);

        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $this->setUser($teacher);

        $assignrecord = $this->getDataGenerator()->create_module('assign', [
            'course' => $course->id,
            'name' => 'Test assignment',
            'assignsubmission_file_enabled' => 1,
            'assignsubmission_file_maxfiles' => 1,
            'assignsubmission_file_maxsizebytes' => 0,
        ]);
        $cm = get_coursemodule_from_instance('assign', $assignrecord->id, $course->id, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);

        $filerenaming = new \local_assignsubmission_download\local\filerenaming($context, $cm, $course);
        $handlemethod = new \ReflectionMethod(
            \local_assignsubmission_download\local\filerenaming::class,
            'handle_download_settings'
        );
        $handlemethod->setAccessible(true);
        $displaymethod = new \ReflectionMethod(
            \local_assignsubmission_download\local\filerenaming::class,
            'get_submission_neweras_date'
        );
        $displaymethod->setAccessible(true);

        set_user_preference('submissionneweras', 0);
        $timestamp = make_timestamp(2026, 1, 1, 0, 0, 0);
        $downloadsettings = (object) [
            'filenamingscheme' => '[firstname]_[lastname]',
            'preventnameextension' => 1,
            'cleanfilenames' => 0,
            'submissionneweras' => $timestamp,
            'zipnamingscheme' => 'ZIP_[assignmentname]',
        ];

        $handlemethod->invoke(
            $filerenaming,
            'local_assignsubmission_download',
            $cm->id,
            $teacher->id,
            0,
            0,
            $downloadsettings
        );

        $record = $DB->get_record(
            'local_assignsubmission_download',
            ['userid' => $teacher->id, 'cmid' => $cm->id],
            '*',
            MUST_EXIST
        );
        $this->assertSame($timestamp, (int) $record->lastsubneweras);
        $this->assertSame(
            userdate($timestamp),
            $displaymethod->invoke($filerenaming, $cm->id, $teacher->id, 'local_assignsubmission_download')
        );

        $downloadsettings->submissionneweras = null;
        $handlemethod->invoke(
            $filerenaming,
            'local_assignsubmission_download',
            $cm->id,
            $teacher->id,
            0,
            0,
            $downloadsettings
        );

        $record = $DB->get_record(
            'local_assignsubmission_download',
            ['userid' => $teacher->id, 'cmid' => $cm->id],
            '*',
            MUST_EXIST
        );
        $this->assertNull($record->lastsubneweras);
        $this->assertSame(
            get_string('functionnotused', 'local_assignsubmission_download'),
            $displaymethod->invoke($filerenaming, $cm->id, $teacher->id, 'local_assignsubmission_download')
        );
    }
}
