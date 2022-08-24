@qtype @qtype_flashcard
Feature: Test creating a Flashcard question
  As a teacher

  Background:
    Given the following "users" exist:
      | username |
      | teacher  |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |

  @javascript
  Scenario: Create a Flashcard question
    When I am on the "Course 1" "core_question > course question bank" page logged in as teacher
    And I add a "Flashcard" question filling the form with:
      | Question name            | Flashcard-01                               |
      | Question text            | This is the question text of Flashcard-01. |
      | General feedback         | General feedback of Flashcard-01.          |
      | Solution                 | This is the answer text of Flashcard-01.   |
    Then I should see "Flashcard-01"
