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
 * Multiple choice question definition classes.
 *
 * @package    qtype_flashcard
 * @copyright  2020 University of vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/questionbase.php');

/**
 * Base class for multiple choice questions. The parts that are common to
 * single select and multiple select.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_flashcard_question extends question_graded_automatically {
    const LAYOUT_DROPDOWN = 0;
    const LAYOUT_VERTICAL = 1;
    const LAYOUT_HORIZONTAL = 2;

    public $answer;
    public $layout = self::LAYOUT_VERTICAL;

    /**
     * @param question_attempt_step $step
     * @param $variant
     */
    public function start_attempt(question_attempt_step $step, $variant) {
    }

    /**
     * @param moodle_page $page
     * @return qtype_renderer|renderer_base
     */
    public function get_renderer(moodle_page $page) {
        return $page->get_renderer('qtype_flashcard');
    }

    /**
     * @return array|string
     */
    public function get_expected_data() {
        return array('answer' => PARAM_BOOL);
    }

    /**
     * @param question_attempt_step $step
     * @throws coding_exception
     */
    public function apply_attempt_state(question_attempt_step $step) {
        if (isset($this->answer)) {
            return;
        }
        $a = new stdClass();
        $a->id = 0;
        $a->answer = html_writer::span(get_string('deletedchoice', 'qtype_flashcard'),
                'notifyproblem');
        $a->answerformat = FORMAT_HTML;
        $a->fraction = 0;
        $this->answer = $this->qtype->make_answer($a);

    }

    /**
     * @return string|null
     */
    public function get_question_summary() {
        $question = $this->html_to_text($this->questiontext, $this->questiontextformat);
        return $question;
    }

    /**
     * @param question_attempt $qa
     * @param question_display_options $options
     * @param string $component
     * @param string $filearea
     * @param array $args
     * @param bool $forcedownload
     * @return bool
     */
    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        
        if ($component == 'question' && in_array($filearea,
                array('correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback'))) {
            return $this->check_combined_feedback_file_access($qa, $options, $filearea, $args);

        } else if ($component == 'question' && $filearea == 'answer') {
            foreach ($this->answers as $correctanswerid => $correctanswer) {
                $tocheck = $correctanswerid;
                break;
            }
            $answerid = reset($args); // Itemid is answer id.
            return  $answerid == $tocheck;

        } else {
            return parent::check_file_access($qa, $options, $component, $filearea,
                    $args, $forcedownload);
        }
    }

    /**
     * @return float|int
     */
    public function get_min_fraction() {
        $minfraction = 0;
        return $minfraction;
    }

    /**
     * @param array $response
     * @return string|null
     */
    public function summarise_response(array $response) {
        if (!$this->is_complete_response($response)) {
            return null;
        }
        $ansid = $this->order[$response['answer']];
        return $this->html_to_text($this->answers[$ansid]->answer,
            $this->answers[$ansid]->answerformat);
    }

    /**
     * @param array $response
     * @return array
     */
    public function classify_response(array $response) {
        if (!$this->is_complete_response($response)) {
            return array($this->id => question_classified_response::no_response());
        }
        $choiceid = $this->order[$response['answer']];
        $ans = $this->answers[$choiceid];
        return array($this->id => new question_classified_response($choiceid,
            $this->html_to_text($ans->answer, $ans->answerformat), $ans->fraction));
    }

    /**
     * @return array|null
     */
    public function get_correct_response() {
        return array();
    }

    /**
     * @param array $simulatedresponse
     * @return array
     * @throws coding_exception
     */
    public function prepare_simulated_post_data($simulatedresponse) {
        $ansid = 0;
        if (clean_param($this->answer->answer, PARAM_NOTAGS) == $simulatedresponse['answer']) {
                $ansid = $this->answer->id;
            
        }
        if ($ansid) {
            return array('answer' => array_search($ansid, $this->order));
        } else {
            return array();
        }
    }

    /**
     * Get all answers from a simulation
     * @param string[] $postdata
     * @return array|string[]
     * @throws coding_exception
     */
    public function get_student_response_values_for_simulation($postdata) {
        if (!isset($postdata['answer'])) {
            return array();
        } else {
            $answer = $this->answers[$this->order[$postdata['answer']]];
            return array('answer' => clean_param($answer->answer, PARAM_NOTAGS));
        }
    }

    /**
     * Compare the new and old answeres against each other
     * @param array $prevresponse
     * @param array $newresponse
     * @return bool
     */
    public function is_same_response(array $prevresponse, array $newresponse) {
        if (!$this->is_complete_response($prevresponse)) {
            $prevresponse = [];
        }
        if (!$this->is_complete_response($newresponse)) {
            $newresponse = [];
        }
        return question_utils::arrays_same_at_key($prevresponse, $newresponse, 'answer');
    }

    /**
     * @param array $response
     * @return bool
     */
    public function is_complete_response(array $response) {
        return array_key_exists('answer', $response) && $response['answer'] !== ''
            && (string) $response['answer'] !== '-1';
    }

    /**
     * Test if the answer is complete
     * @param array $response
     * @return bool
     */
    public function is_gradable_response(array $response) {
        return $this->is_complete_response($response);
    }

    /**
     * Get the answer state of a question
     * @param array $response
     * @return array
     */
    public function grade_response(array $response) {
        if (array_key_exists('answer', $response) &&
            array_key_exists($response['answer'], $this->order)) {
                $fraction = $this->answers[$this->order[$response['answer']]]->fraction;
            } else {
                $fraction = 0;
            }
            return array($fraction, question_state::graded_state_for_fraction($fraction));
    }

    /**
     * Tests if an answer is formal correct
     * @param array $response
     * @return string
     * @throws coding_exception
     */
    public function get_validation_error(array $response) {
        if ($this->is_gradable_response($response)) {
            return '';
        }
        return get_string('pleaseselectananswer', 'qtype_flashcard');
    }

    /**
     * Get the last question attempt answer
     * @param question_attempt $qa
     * @return mixed
     */
    public function get_response(question_attempt $qa) {
        return $qa->get_last_qt_var('answer', -1);
    }

    /**
     * @param $response
     * @param $value
     * @return bool
     */
    public function is_choice_selected($response, $value) {
        return (string) $response === (string) $value;
    }
}
