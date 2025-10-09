@local @local_assignsubmission_download
Feature: Testing the download of renamed submissions.
  As a teacher
  I want to download the renamed submissions.

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
      | assignfeedback_comments_enabled     | 1                  |
    And I log out
    And I log in as "student1"
    And I am on the "Test assignment" Activity page
    And I press "Add submission"
    And I set the following fields to these values:
      | Online text | This is a submission for Student One |
    And I press "Save changes"
    And I press "Submit assignment"
    And I press "Continue"

  @javascript @submissions
  Scenario: Submissions settings are saved and shown in last submissions download settings
    When I log in as "teacher1"
    And I am on the "Test assignment" Activity page
    And I navigate to "Download renamed submissions" in current page administration
    Then I should see "no downloads yet"
    # Configure form for submissions only.
    And I set the field "downloadtype_submissions" to "1"
    And I set the field "downloadtype_feedbacks" to "0"
    And I set the field "filerenamingpattern" to "[firstname]_[lastname]_[assignmentname]"
    And I set the field "nameofziparchive" to "ZIP_[assignmentname]_[currentdate]"
    And I set the field "id_submissionneweras_enabled" to "1"
    And I set the field "submissionneweras[day]" to "1"
    And I set the field "submissionneweras[month]" to "10"
    And I set the field "submissionneweras[year]" to "2025"
    And I set the field "submissionneweras[hour]" to "18"
    And I set the field "submissionneweras[minute]" to "00"
    And I press "Download submissions"
    And I reload the page
    Then I should see "Download renamed submissions"
    And I expand all fieldsets
    And I should not see "no downloads yet" in the "#id_lastdownloadsettings" "css_element"
    And I should see "[firstname]_[lastname]_[assignmentname]" in the "#id_lastdownloadsettings" "css_element"
    And I should see "ZIP_[assignmentname]_[currentdate]" in the "#id_lastdownloadsettings" "css_element"
    And I should see "2025" in the "#id_lastdownloadsettings" "css_element"
    And I should see "6:00 PM" in the "#id_lastdownloadsettings" "css_element"
    And I should see "no downloads yet" in the "#id_lastfeedbacksettings" "css_element"

  @javascript @feedback
  Scenario: Feedback settings are saved and shown in last feedback file download settings
    When I log in as "teacher1"
    And I am on the "Test assignment" Activity page
    And I click on "Grade" "link" in the ".tertiary-navigation" "css_element"
    And I set the field "id_grade" to "70"
    And I set the field "id_assignfeedbackcomments_editor" to "Good work"
    And I click on "Save changes" "button"
    And I am on the "Test assignment" Activity page
    And I navigate to "Download renamed submissions" in current page administration
    Then I should see "no downloads yet"
    # Configure form for feedback only (turn off submissions, enable feedbacks).
    And I set the field "downloadtype_submissions" to "0"
    And I set the field "downloadtype_feedbacks" to "1"
    And I set the field "filerenamingpattern" to "FB_[lastname]_[filenumber]"
    And I set the field "nameofziparchive" to "FBZIP_[assignmentname]_[currenttime]"
    And I set the field "id_submissionneweras_enabled" to "1"
    And I set the field "submissionneweras[day]" to "1"
    And I set the field "submissionneweras[month]" to "10"
    And I set the field "submissionneweras[year]" to "2025"
    And I set the field "submissionneweras[hour]" to "18"
    And I set the field "submissionneweras[minute]" to "00"
    And I press "Download submissions"
    And I reload the page
    Then I should see "Download renamed submissions"
    And I expand all fieldsets
    And I should not see "no downloads yet" in the "#id_lastfeedbacksettings" "css_element"
    And I should see "FB_[lastname]_[filenumber]" in the "#id_lastfeedbacksettings" "css_element"
    And I should see "FBZIP_[assignmentname]_[currenttime]" in the "#id_lastfeedbacksettings" "css_element"
    And I should see "no downloads yet" in the "#id_lastdownloadsettings" "css_element"

  @javascript @feedback @error
  Scenario: Error message shown when no feedback files are there for download
    When I log in as "teacher1"
    And I am on the "Test assignment" Activity page
    And I navigate to "Download renamed submissions" in current page administration
    And I set the field "downloadtype_submissions" to "0"
    And I set the field "downloadtype_feedbacks" to "1"
    And I press "Download submissions"
    Then I should see "No feedback files available"
    And I navigate to "Download renamed submissions" in current page administration
    And I set the field "downloadtype_submissions" to "0"
    And I set the field "downloadtype_feedbacks" to "1"
    And I set the field "id_submissionneweras_enabled" to "1"
    And I set the field "submissionneweras[day]" to "1"
    And I set the field "submissionneweras[month]" to "10"
    And I set the field "submissionneweras[year]" to "2025"
    And I set the field "submissionneweras[hour]" to "18"
    And I set the field "submissionneweras[minute]" to "00"
    And I press "Download submissions"
    Then I should see "No feedback was given after Wednesday, 1 October 2025, 6:00 PM"
