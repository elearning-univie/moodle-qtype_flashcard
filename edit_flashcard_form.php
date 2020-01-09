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
 * Defines the editing form for the multiple choice question type.
 *
 * @package    qtype_flashcard
 * @copyright  2020 University of vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Multiple choice editing form definition.
 *
 * @copyright  2007 Jamie Pratt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_flashcard_edit_form extends question_edit_form {
    /**
     * Add question-type specific form fields.
     *
     * @param object $mform the form being built.
     */
    protected function definition_inner($mform) {


        $this->add_answer_field($mform, get_string('correctanswer', 'qtype_flashcard'),
                question_bank::fraction_options_full());

        $this->add_interactive_settings(false, false);
    }

    protected function add_answer_field($mform, $label, $gradeoptions) {
                $mform->addElement('header', 'answerhdr',
                    get_string('answers', 'question'), '');
                $mform->setExpanded('answerhdr', 1);
                $repeated = array();
                $mform->addElement('editor', 'answer',
                $label, array('rows' => 15), $this->editoroptions);
                $mform->setType('answer', PARAM_RAW);
                $mform->addRule('answer', null, 'required', null, 'client');
        return $repeated;
    }

    protected function get_hint_fields($withclearwrong = false, $withshownumpartscorrect = false) {
        list($repeated, $repeatedoptions) = parent::get_hint_fields($withclearwrong, $withshownumpartscorrect);
        return array($repeated, $repeatedoptions);
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_answers($question, true);
        return $question;
    }
    
    /**
     * Perform the necessary preprocessing for the fields added by
     * {@link add_per_answer_fields()}.
     * @param object $question the data being passed to the form.
     * @return object $question the modified data.
     */
    protected function data_preprocessing_answers($question, $withanswerfiles = false) {
        if (empty($question->options->answers)) {
            return $question;
        }
        if (empty($question->options->answers)) {
            return $question;
        }
        
        foreach ($question->options->answers as $answer) {
            if ($withanswerfiles) {
                // Prepare the feedback editor to display files in draft area.
                $draftitemid = file_get_submitted_draft_itemid('answer');
                $question->answer['text'] = file_prepare_draft_area(
                    $draftitemid,          // Draftid
                    $this->context->id,    // context
                    'question',            // component
                    'answer',              // filarea
                    !empty($answer->id) ? (int) $answer->id : null, // itemid
                    $this->fileoptions,    // options
                    $answer->answer        // text.
                    );
                $question->answer['itemid'] = $draftitemid;
                $question->answer['format'] = $answer->answerformat;
            } else {
                $question->answer = $answer->answer;
            }
            break;
        }
        return $question;
    }

    /**
     * @return string|the
     */
    public function qtype() {
        return 'flashcard';
    }
}
