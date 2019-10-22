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
 * @package    qtype
 * @subpackage multichoice
 * @copyright  2009 The Open University
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
abstract class qtype_flashcard extends question_graded_automatically {
    const LAYOUT_DROPDOWN = 0;
    const LAYOUT_VERTICAL = 1;
    const LAYOUT_HORIZONTAL = 2;

    public $answer;
    public $layout = self::LAYOUT_VERTICAL;


    public function start_attempt(question_attempt_step $step, $variant) {
    }
    
    public function get_renderer(moodle_page $page) {
        return $page->get_renderer('qtype_flashcard');
    }

    public function get_expected_data() {
        return array('answer' => PARAM_BOOL);
    }

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

    public function get_question_summary() {
        $question = $this->html_to_text($this->questiontext, $this->questiontextformat);
        return $question;
    }

    public abstract function get_response(question_attempt $qa);

    public abstract function is_choice_selected($response, $value);

    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        if ($component == 'question' && in_array($filearea,
                array('correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback'))) {
            return $this->check_combined_feedback_file_access($qa, $options, $filearea, $args);

        } else if ($component == 'question' && $filearea == 'answer') {
            $answerid = reset($args); // Itemid is answer id.
            return  in_array($answerid, $this->order);

        } else if ($component == 'question' && $filearea == 'answerfeedback') {
            $answerid = reset($args); // Itemid is answer id.
            $response = $this->get_response($qa);
            $isselected = false;
            foreach ($this->order as $value => $ansid) {
                if ($ansid == $answerid) {
                    $isselected = $this->is_choice_selected($response, $value);
                    break;
                }
            }
            // Param $options->suppresschoicefeedback is a hack specific to the
            // oumultiresponse question type. It would be good to refactor to
            // avoid refering to it here.
            return $options->feedback && empty($options->suppresschoicefeedback) &&
                    $isselected;

        } else if ($component == 'question' && $filearea == 'hint') {
            return $this->check_hint_file_access($qa, $options, $args);

        } else {
            return parent::check_file_access($qa, $options, $component, $filearea,
                    $args, $forcedownload);
        }
    }
    
    public function get_min_fraction() {
        $minfraction = 0;
        return $minfraction;
    }
    
    public function summarise_response(array $response) {
        if (!$this->is_complete_response($response)) {
            return null;
        }
        $ansid = $this->order[$response['answer']];
        return $this->html_to_text($this->answers[$ansid]->answer,
            $this->answers[$ansid]->answerformat);
    }
    
    public function classify_response(array $response) {
        if (!$this->is_complete_response($response)) {
            return array($this->id => question_classified_response::no_response());
        }
        $choiceid = $this->order[$response['answer']];
        $ans = $this->answers[$choiceid];
        return array($this->id => new question_classified_response($choiceid,
            $this->html_to_text($ans->answer, $ans->answerformat), $ans->fraction));
    }
    
    public function get_correct_response() {
        foreach ($this->order as $key => $answerid) {
            if (question_state::graded_state_for_fraction(
                $this->answers[$answerid]->fraction)->is_correct()) {
                    return array('answer' => $key);
                }
        }
        return array();
    }
    
    public function prepare_simulated_post_data($simulatedresponse) {
        $ansid = 0;
        foreach ($this->answers as $answer) {
            if (clean_param($answer->answer, PARAM_NOTAGS) == $simulatedresponse['answer']) {
                $ansid = $answer->id;
            }
        }
        if ($ansid) {
            return array('answer' => array_search($ansid, $this->order));
        } else {
            return array();
        }
    }
    
    public function get_student_response_values_for_simulation($postdata) {
        if (!isset($postdata['answer'])) {
            return array();
        } else {
            $answer = $this->answers[$this->order[$postdata['answer']]];
            return array('answer' => clean_param($answer->answer, PARAM_NOTAGS));
        }
    }
    
    public function is_same_response(array $prevresponse, array $newresponse) {
        if (!$this->is_complete_response($prevresponse)) {
            $prevresponse = [];
        }
        if (!$this->is_complete_response($newresponse)) {
            $newresponse = [];
        }
        return question_utils::arrays_same_at_key($prevresponse, $newresponse, 'answer');
    }
    
    public function is_complete_response(array $response) {
        return array_key_exists('answer', $response) && $response['answer'] !== ''
            && (string) $response['answer'] !== '-1';
    }
    
    public function is_gradable_response(array $response) {
        return $this->is_complete_response($response);
    }
    
    public function grade_response(array $response) {
        if (array_key_exists('answer', $response) &&
            array_key_exists($response['answer'], $this->order)) {
                $fraction = $this->answers[$this->order[$response['answer']]]->fraction;
            } else {
                $fraction = 0;
            }
            return array($fraction, question_state::graded_state_for_fraction($fraction));
    }
    
    public function get_validation_error(array $response) {
        if ($this->is_gradable_response($response)) {
            return '';
        }
        return get_string('pleaseselectananswer', 'qtype_flashcard');
    }
    
    public function get_response(question_attempt $qa) {
        return $qa->get_last_qt_var('answer', -1);
    }
    
    public function is_choice_selected($response, $value) {
        return (string) $response === (string) $value;
    }
}
