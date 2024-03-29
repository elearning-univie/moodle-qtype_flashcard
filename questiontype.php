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
 * The questiontype class for the flashcard question type.
 *
 * @package    qtype_flashcard
 * @copyright  2020 University of vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/questionlib.php');


/**
 * The flashcard question type.
 *
 * @copyright  2020 University of vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_flashcard extends question_type {

    /**
     * get the options for a question
     * @param object $question
     * @return bool|void
     */
    public function get_question_options($question) {
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

    /**
     * save the options for a question
     * @param object $question
     * @return object|stdClass
     * @throws coding_exception
     * @throws dml_exception
     */
    public function save_question_options($question) {
        global $DB;
        $context = $question->context;
        $result = new stdClass();

        $oldanswer = $DB->get_record('question_answers',
            array('question' => $question->id));

        if (!$question->answer) {
            $result->error = get_string('notenoughanswers', 'qtype_flashcard');
            return $result;
        }

        if (!$oldanswer) {
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
        $answer->answer = $this->import_or_save_files($question->answer,
            $context, 'question', 'answer', $answer->id);
        $DB->update_record('question_answers', $answer);
    }

    /**
     * create an instance of a question
     * @param object $questiondata
     * @return mixed|question_definition
     * @throws coding_exception
     */
    protected function make_question_instance($questiondata) {
        question_bank::load_question_definition_classes($this->name());
            $class = 'qtype_flashcard_question';
        return new $class();
    }

    /**
     * initialise an instance of a question
     * @param question_definition $question
     * @param object $questiondata
     */
    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        if (!empty($questiondata->options->layout)) {
            $question->layout = $questiondata->options->layout;
        } else {
            $question->layout = qtype_flashcard_question::LAYOUT_VERTICAL;
        }
        $this->initialise_question_answers($question, $questiondata, false);
    }

    /**
     * Create a question_answer, or an appropriate subclass for this question,
     * from a row loaded from the database.
     * @param object $answer the DB row from the question_answers table plus extra answer fields.
     * @return question_answer
     */
    public function make_answer($answer) {
        $qa = new question_answer($answer->id, $answer->answer,
            $answer->fraction, '', 0);
        return $qa;
    }

    /**
     * get a random guess score
     * @param object $questiondata
     * @return number|null
     */
    public function get_random_guess_score($questiondata) {
        return null;
    }

    /**
     * get all possible responses for a question
     * @param object $questiondata
     * @return array
     * @throws coding_exception
     */
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

    /**
     * get question information from xml file
     * @param object $data
     * @param object $question
     * @param qformat_xml $format
     * @param null $extra
     * @return false|object
     */
    public function import_from_xml($data, $question, qformat_xml $format, $extra=null) {

        if (!isset($data['@']['type']) || $data['@']['type'] != 'flashcard') {
            return false;
        }

        $question = $format->import_headers($data);
        $question->qtype = 'flashcard';

        $answer = $data['#']['answer'][0];
        $ans = $format->import_answer($answer, true,
            $format->get_format($question->questiontextformat));
        $question->answer = $ans->answer;
        $question->fraction = $answer['@']['fraction'];

        return $question;
    }

    /**
     * creates xml data for the xml export
     * @param object $question
     * @param qformat_xml $format
     * @param null $extra
     * @return false|string
     */
    public function export_to_xml($question, qformat_xml $format, $extra = null) {
        $output = '';
        $output .= $format->write_answers($question->options->answers);

        return $output;
    }

    /**
     * move files
     * @param int $questionid
     * @param int $oldcontextid
     * @param int $newcontextid
     */
    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_answers($questionid, $oldcontextid, $newcontextid, true);
        $this->move_files_in_combined_feedback($questionid, $oldcontextid, $newcontextid);
    }

    /**
     * delete files
     * @param int $questionid
     * @param int $contextid
     */
    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_answers($questionid, $contextid, true);
        $this->delete_files_in_combined_feedback($questionid, $contextid);
    }
}
