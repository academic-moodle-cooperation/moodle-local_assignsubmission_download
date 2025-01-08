@local @local_assignsubmission_download
Feature: Testing download file feature in the export tab of local_assignsubmission_download.
  As a teacher
  I want to download the preview file of the submissions in the export tab

  Background:
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activity" exists:
      | activity                            | assign             |
      | course                              | C1                 |
      | name                                | Test assignment    |
      | assignsubmission_onlinetext_enabled | 1                  |
    And I log out
    And I log in as "student1"
    And I am on the "Test assignment" Activity page
    And I press "Add submission"
    And I set the following fields to these values:
      | Online text | This is a submission for Student One |
    And I press "Save changes"
    And I press "Submit assignment"
    And I press "Continue"

  @javascript
  Scenario: As a teacher I can navigate to the export tab and see the submission
    When I log in as "teacher1"
    And I am on the "Test assignment" Activity page
    And I navigate to "Export" in current page administration
    And I should see "This is a submission for Student One"

  @javascript
  Scenario: AS a teacher I can download the preview file of the submission
    When I log in as "teacher1"
    And I am on the "Test assignment" Activity page
    And I navigate to "Export" in current page administration
    And I press "Download file"
    Then I should see "This is a submission for Student One"
