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
 * Test helper code for the flashcard question type.
 *
 * @package    qtype_flashcard
 * @copyright  2022 University of vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Test helper class for the multiple choice question type.
 *
 * @copyright  2022 University of vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_flashcard_test_helper extends question_test_helper {
    /**
     * Get the test questions.
     * @return string[]
     */
    public function get_test_questions() {
        return array('plain');
    }

    /**
     * Get the question data, as it would be loaded by get_question_options.
     * @return object
     */
    public static function get_flashcard_question_data_plain() {
        global $USER;

        $qdata = new stdClass();

        $qdata->createdby = $USER->id;
        $qdata->modifiedby = $USER->id;
        $qdata->qtype = 'flashcard';
        $qdata->name = 'Flashcard question';
        $qdata->questiontext = 'This is the question text of Flashcard-01.';
        $qdata->questiontextformat = FORMAT_HTML;
        $qdata->generalfeedback = 'General feedback of Flashcard-01.';
        $qdata->generalfeedbackformat = FORMAT_HTML;
        $qdata->defaultmark = 1;
        $qdata->answer = 'This is the answer text of Flashcard-01.';
        $qdata->status = \core_question\local\bank\question_version_status::QUESTION_STATUS_READY;
        $qdata->versionid = 0;
        $qdata->version = 1;

        return $qdata;
    }
    /**
     * Get the question data, as it would be loaded by get_question_options.
     * @return object
     */
    public static function get_flashcard_question_form_data_plain() {
        $qdata = new stdClass();

        $qdata->name = 'Flashcard question';
        $qdata->questiontext = array('text' => 'This is the question text of Flashcard-01.', 'format' => FORMAT_HTML);
        $qdata->defaultmark = 1.0;
        $qdata->generalfeedback = array('text' => 'General feedback of Flashcard-01.', 'format' => FORMAT_HTML);
        $qdata->answer = array('text' => 'This is the answer text of Flashcard-01.', 'format' => FORMAT_HTML);
        $qdata->status = \core_question\local\bank\question_version_status::QUESTION_STATUS_READY;

        return $qdata;
    }
}
