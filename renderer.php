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
 * Multiple choice question renderer classes.
 *
 * @package    qtype
 * @subpackage multichoice
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Base class for generating the bits of output common to multiple choice
 * single and multiple questions.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_flashcard_renderer extends qtype_with_combined_feedback_renderer {

    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {
        global $PAGE;
        $question = $qa->get_question();
        foreach($question->answers as $answer) {
            $ans = $answer;
        }
        $flipcontainercontent = '';
        $flipcontainercontent .= html_writer::tag('div', $question->format_questiontext($qa),
                array('class' => 'qtext qflashcard-question'));

        $flipcontainercontent .= html_writer::start_tag('div', array('class' => 'ablock qflashcard-ablock'));

        $flipcontainercontent .= html_writer::start_tag('div', array('class' => 'answer'));
        $flipcontainercontent .= html_writer::tag('div',$question->format_text(
            $ans->answer, $ans->answerformat,
            $qa, 'question', 'answer', $ans->id),
                array('class' => 'qanswer qflashcard-answer')) . "\n";
        $flipcontainercontent .= html_writer::end_tag('div'); // Answer.

        $flipcontainercontent .= html_writer::end_tag('div'); // Ablock.
        $result = '';
        $flipper = html_writer::div($flipcontainercontent,'qflashcard-flipper');
        $flipper .= html_writer::tag('button', get_string('flipbutton', 'qtype_flashcard'),['class' => 'qflashcard-flipbutton btn btn-primary', 'id' => 'qflashcard-flipbutton-' . $qa->get_database_id()]);
        $result = html_writer::tag('div', $flipper,
            array('id' => 'qflashcard-flipcontainer-' . $qa->get_database_id(), 'class' => 'qflashcard-flipcontainer'));
        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div',
                    $question->get_validation_error($qa->get_last_qt_data()),
                    array('class' => 'validationerror'));
        }
        $context = ['qaid' => $qa->get_database_id()];
        $PAGE->requires->js_call_amd('qtype_flashcard/flipquestion', 'init', $context);
        return $result;
    }

    protected function number_html($qnum) {
        return $qnum . '. ';
    }

}
