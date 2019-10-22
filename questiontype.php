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
 * The questiontype class for the multiple choice question type.
 *
 * @package    qtype
 * @subpackage multichoice
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/questionlib.php');


/**
 * The multiple choice question type.
 *
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_flashcard extends question_type {
    public function get_question_options($question) {
        global $DB;

        $question->options = $this->create_default_options($question);

        parent::get_question_options($question);
    }

    /**
     * Create a default options object for the provided question.
     *
     * @param object $question The queston we are working with.
     * @return object The options object.
     */
    protected function create_default_options($question) {
        // Create a default question options record.
        $options = new stdClass();
        $options->questionid = $question->id;
        return $options;
    }

    public function save_question_options($question) {
        global $DB;
        $context = $question->context;
        $result = new stdClass();

        $oldanswer = $DB->get_field('question_answers', 'id',
                array('question' => $question->id));

        // Following hack to check at least two answers exist.

        if (!$question->answer) {
            $result->error = get_string('notenoughanswers', 'qtype_flashcard', '2');
            return $result;
        }
        if(!$oldanswer) {
            $answer = new stdClass();
            $answer->question = $question->id;
            $answer->answer = '';
            $answer->feedback = '';
            $answer->id = $DB->insert_record('question_answers', $answer);
        } else {
            $answer = $oldanswer;
        }
        $answer->answerformat = $question->answer['format'];
        $answer->questionid = $question->id;
        $answer->feedback = '';

        $answer->answer = $this->import_or_save_files($question->answer,
            $context, 'question', 'answer', $oldanswer);
        $DB->update_record('question_answers', $answer);
    }

    protected function make_question_instance($questiondata) {
        question_bank::load_question_definition_classes($this->name());
            $class = 'qtype_flashcard';
        return new $class();
    }

    protected function make_hint($hint) {
        return question_hint_with_parts::load_from_record($hint);
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $question->shuffleanswers = $questiondata->options->shuffleanswers;
        $question->answernumbering = $questiondata->options->answernumbering;
        if (!empty($questiondata->options->layout)) {
            $question->layout = $questiondata->options->layout;
        } else {
            $question->layout = qtype_flashcard::LAYOUT_VERTICAL;
        }
        $this->initialise_combined_feedback($question, $questiondata, true);

        $this->initialise_question_answers($question, $questiondata, false);
    }

    public function make_answer($answer) {
        // Overridden just so we can make it public for use by question.php.
        return parent::make_answer($answer);
    }

    public function get_random_guess_score($questiondata) {
        return null;
    }

    public function get_possible_responses($questiondata) {
        return array(
            $questiondata->id => array(
                0 => new question_possible_response(get_string('false', 'qtype_flashcard'),
                    $questiondata->options->answers[
                        $questiondata->options->falseanswer]->fraction),
                1 => new question_possible_response(get_string('true', 'qtype_flashcard'),
                    $questiondata->options->answers[
                        $questiondata->options->trueanswer]->fraction),
                null => question_possible_response::no_response()
            )
        );
    }


    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_answers($questionid, $oldcontextid, $newcontextid, true);
        $this->move_files_in_combined_feedback($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_answers($questionid, $contextid, true);
        $this->delete_files_in_combined_feedback($questionid, $contextid);
        $this->delete_files_in_hints($questionid, $contextid);
    }
}
