@qtype @qtype_flashcard
Feature: Test editing a Flashcard question
  As a teacher
  In order to be able to update my Flashcard question
  I need to edit them

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
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype       | name                     | template    |
      | Test questions   | flashcard | Flashcard question plain | plain       |

  Scenario: Edit a Flashcard question
    When I am on the "Flashcard question plain" "core_question > edit" page logged in as teacher
    And I set the following fields to these values:
      | Question name | |
    And I press "id_submitbutton"
    And I should see "You must supply a value here."
    And I set the following fields to these values:
      | Question name | Edited Flashcard name |
    And I press "id_submitbutton"
    Then I should see "Edited Flashcard name"
