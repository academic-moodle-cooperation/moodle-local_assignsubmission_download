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
 * Unit tests for locallib.php
 *
 * @package     local_assignsubmission_download
 * @author      Clemens Marx
 * @copyright   2025 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class locallib_test extends \advanced_testcase {
    /**
     * Basic smoke test for filerenaming_rename_file.
     * Ensures common placeholders are replaced correctly for non-blind marking.
     */
    public function test_filerenaming_rename_file_basic(): void {
        global $CFG;

        $this->resetAfterTest(true);

        // Require needed libs.
        require_once($CFG->dirroot . '/mod/assign/locallib.php');
        // Load the plugin locallib relative to this test file to adapt to this repository layout.
        require_once(dirname(__DIR__) . '/locallib.php');

        // Create a user and set it as the current user (preferences are per-user).
        $user = $this->getDataGenerator()->create_user([
            'idnumber' => 'TestIdNumber123',
            'lastname' => 'TestLastName',
            'firstname' => 'TestFirstName',
            'username' => 'testusername',
            'alternatename' => 'TestAlternateName',
            'firstnamephonetic' => 'TestFirstNamePhonetic',
            'lastnamephonetic' => 'TestLastNamePhonetic',
        ]);
        $this->setUser($user);

        // Use a simple pattern that exercises the most common tags.
        set_user_preference('filerenamingpattern', '[lastname]_[firstname]_[idnumber]_[filename]');
        // Keep cleaning default off to only rely on built-in replacements (spaces->underscores always apply).

        // Create course and assignment (non-blind marking to use real names).
        $course = $this->getDataGenerator()->create_course();
        $assignrecord = $this->getDataGenerator()->create_module('assign', [
            'course' => $course->id,
            'name' => 'Sample Assignment',
            'blindmarking' => 0,
        ]);
        $cm = get_coursemodule_from_instance('assign', $assignrecord->id, $course->id, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        $assign = new \assign($context, $cm, $course);

        // Inputs to the function under test.
        $prefixedfilename = '';
        $original = 'My Report.docx';
        $submission = new \stdClass(); // Not used by the function currently.
        $groupname = '-'; // Explicitly disable group replacement logic.
        $sequence = 1;
        $zipfiles = null;
        $preventprefix = true; // Only use pattern, no auto-prefix.

        $result = filerenaming_rename_file(
            $prefixedfilename,
            $original,
            $user,
            $assign,
            $submission,
            $groupname,
            $sequence,
            $zipfiles,
            $preventprefix
        );

        // Expect spaces to be converted to underscores and tags replaced accordingly.
        $this->assertSame('TestLastName_TestFirstName_TestIdNumber123_My_Report.docx', $result);
    }

    /**
     * Japanese-specific fields test.
     * Ensures username, phonetic names, and alternatename are correctly substituted.
     */
    public function test_filerenaming_rename_file_japanese_fields(): void {
        global $CFG;

        $this->resetAfterTest(true);

        // Require needed libs.
        require_once($CFG->dirroot . '/mod/assign/locallib.php');
        require_once(dirname(__DIR__) . '/locallib.php');

        // Create a Japanese student profile.
        $user = $this->getDataGenerator()->create_user([
            'username' => 'z23001', // Student ID number style username.
            'firstname' => '太郎',
            'lastname' => '山田',
            'firstnamephonetic' => 'タロウ', // Katakana.
            'lastnamephonetic' => 'ヤマダ', // Katakana.
            'alternatename' => 'YAMADA Taro', // Latin, used by non-Japanese teachers.
        ]);
        $this->setUser($user);

        // Pattern focusing on the requested fields.
        set_user_preference('filerenamingpattern', '[username]_[lastnamephonetic]_[firstnamephonetic]_[alternatename]_[filename]');

        // Create course and assignment (non-blind marking to use real names).
        $course = $this->getDataGenerator()->create_course();
        $assignrecord = $this->getDataGenerator()->create_module('assign', [
            'course' => $course->id,
            'name' => '日本語課題',
            'blindmarking' => 0,
        ]);
        $cm = get_coursemodule_from_instance('assign', $assignrecord->id, $course->id, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        $assign = new \assign($context, $cm, $course);

        // Inputs to the function under test.
        $prefixedfilename = '';
        $original = 'report.pdf';
        $submission = new \stdClass();
        $groupname = '-';
        $sequence = 1;
        $zipfiles = null;
        $preventprefix = true;

        $result = filerenaming_rename_file(
            $prefixedfilename,
            $original,
            $user,
            $assign,
            $submission,
            $groupname,
            $sequence,
            $zipfiles,
            $preventprefix
        );

        // Expect alternatename space to be converted to underscore by clean_custom.
        $this->assertSame('z23001_ヤマダ_タロウ_YAMADA_Taro_report.pdf', $result);
    }

    /**
     * Comprehensive pattern test covering all supported tags.
     * Verifies replacement for id/name fields, group, file number, assignment/course, and date/time.
     */
    public function test_filerenaming_rename_file_all_tags(): void {
        global $CFG;

        $this->resetAfterTest(true);

        // Require needed libs.
        require_once($CFG->dirroot . '/mod/assign/locallib.php');
        require_once(dirname(__DIR__) . '/locallib.php');

        // Create course with a specific shortname for predictable output.
        $course = $this->getDataGenerator()->create_course([
            'shortname' => 'CS101',
        ]);

        // Create an assignment with spaces to validate cleaning.
        $assignrecord = $this->getDataGenerator()->create_module('assign', [
            'course' => $course->id,
            'name' => 'Big Assign',
            'blindmarking' => 0,
        ]);
        $cm = get_coursemodule_from_instance('assign', $assignrecord->id, $course->id, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        $assign = new \assign($context, $cm, $course);

        // Create a group with idnumber.
        $groupname = 'TeamA';
        $groupidnumber = 'G-42';
        $this->getDataGenerator()->create_group([
            'courseid' => $course->id,
            'name' => $groupname,
            'idnumber' => $groupidnumber,
        ]);

        // Create a user with all relevant fields and become that user.
        $user = $this->getDataGenerator()->create_user([
            'idnumber' => 'ID123',
            'lastname' => 'Last',
            'firstname' => 'First',
            'username' => 'u0001',
            'alternatename' => 'ALT NAME',
            'firstnamephonetic' => 'FirstPh',
            'lastnamephonetic' => 'LastPh',
        ]);
        $this->setUser($user);

        // Pattern includes ALL supported tags in a fixed order.
        $pattern = '[idnumber]-[lastname]-[firstname]-[fullname]-[group]-[groupid]-[filename]-[filenumber]-'
            . '[assignmentname]-[courseshortname]-[currentdate]-[currenttime]-[lastnamephonetic]-[firstnamephonetic]-'
            . '[username]-[alternatename]';
        set_user_preference('filerenamingpattern', $pattern);

        // Compute current date/time expected values close to invocation time.
        $expecteddate = date('Ymd');
        $expectedtime = userdate(time(), '%H%M', 99, false, false);

        // Inputs to the function under test.
        $prefixedfilename = '';
        $original = 'archive.tar.gz'; // Exercises double-extension handling.
        $submission = new \stdClass();
        // Pass a groupname with one trailing char so substr(..., 0, -1) logic yields the actual name.
        $groupnameparam = $groupname . '#';
        $sequence = 7; // Should format to 2 digits => 07.
        $zipfiles = null;
        $preventprefix = true;

        $result = filerenaming_rename_file(
            $prefixedfilename,
            $original,
            $user,
            $assign,
            $submission,
            $groupnameparam,
            $sequence,
            $zipfiles,
            $preventprefix
        );

        // Build expected output considering cleaning (spaces -> underscores) and derived pieces.
        $expectedfullname = str_replace(' ', '_', fullname($user));
        $expectedassign = str_replace(' ', '_', $assign->get_instance()->name);
        $expectedalt = str_replace(' ', '_', $user->alternatename);
        $expected = implode('-', [
            'ID123',
            'Last',
            'First',
            $expectedfullname,
            $groupname,
            $groupidnumber,
            'archive',
            sprintf('%02d', $sequence),
            $expectedassign,
            'CS101',
            $expecteddate,
            $expectedtime,
            'LastPh',
            'FirstPh',
            'u0001',
            $expectedalt,
        ]);
        $expected .= '.tar.gz';

        $this->assertSame($expected, $result);
    }
}
