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
 * @package    qtype_flashcard
 * @copyright  2020 University of vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class qtype_flashcard_renderer extends qtype_with_combined_feedback_renderer {

    /**
     * Renders the flashcard question with the flashcard template
     * @param question_attempt $qa
     * @param question_display_options $options
     * @return bool|string
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {
        global $PAGE;
        $question = $qa->get_question();
        foreach ($question->answers as $answer) {
            $ans = $answer;
        }

        $PAGE->requires->js_call_amd('qtype_flashcard/flipquestion','init');
        $qaid =  $qa->get_database_id();
        $renderer = $PAGE->get_renderer('core');

        $templatecontext['questiontext'] = $question->format_questiontext($qa);
        $templatecontext['answer'] = $question->format_text(
                $ans->answer, $ans->answerformat,
                $qa, 'question', 'answer', $ans->id);
        $templatecontext['fliptext'] = get_string('flipbutton', 'qtype_flashcard');
        $templatecontext['iwasrighttext'] = $this->pix_icon('t/check', '') . get_string('iwasrightbutton', 'qtype_flashcard');
        $templatecontext['iwaswrongtext'] = $this->pix_icon('e/cancel', '') . get_string('iwaswrongbutton', 'qtype_flashcard');
        $templatecontext['qaid'] = $qaid;

        $content = $renderer->render_from_template('qtype_flashcard/questionview', $templatecontext);

        $result = $content;
        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div',
                    $question->get_validation_error($qa->get_last_qt_data()),
                    array('class' => 'validationerror'));
        }

        return $result;
    }

    /**
     * adds a dot to a number
     * @param $qnum
     * @return string
     */
    protected function number_html($qnum) {
        return $qnum . '. ';
    }

}
