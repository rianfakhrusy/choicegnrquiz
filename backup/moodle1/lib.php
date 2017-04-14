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
 * @package    qtype
 * @subpackage choicegnrquiz
 * @copyright  2011 David Mudrak <david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Multichoice question type conversion handler
 */
class moodle1_qtype_choicegnrquiz_handler extends moodle1_qtype_handler {

    /**
     * @return array
     */
    public function get_question_subpaths() {
        return array(
            'ANSWERS/ANSWER',
            'MULTICHOICE',
        );
    }

    /**
     * Appends the choicegnrquiz specific information to the question
     */
    public function process_question(array $data, array $raw) {

        // Convert and write the answers first.
        if (isset($data['answers'])) {
            $this->write_answers($data['answers'], $this->pluginname);
        }

        // Convert and write the choicegnrquiz.
        if (!isset($data['choicegnrquiz'])) {
            // This should never happen, but it can do if the 1.9 site contained
            // corrupt data.
            $data['choicegnrquiz'] = array(array(
                'single'                         => 1,
                'shuffleanswers'                 => 1,
                'correctfeedback'                => '',
                'correctfeedbackformat'          => FORMAT_HTML,
                'partiallycorrectfeedback'       => '',
                'partiallycorrectfeedbackformat' => FORMAT_HTML,
                'incorrectfeedback'              => '',
                'incorrectfeedbackformat'        => FORMAT_HTML,
                'answernumbering'                => 'abc',
            ));
        }
        $this->write_choicegnrquiz($data['choicegnrquiz'], $data['oldquestiontextformat'], $data['id']);
    }

    /**
     * Converts the choicegnrquiz info and writes it into the question.xml
     *
     * @param array $choicegnrquizs the grouped structure
     * @param int $oldquestiontextformat - {@see moodle1_question_bank_handler::process_question()}
     * @param int $questionid question id
     */
    protected function write_choicegnrquiz(array $choicegnrquizs, $oldquestiontextformat, $questionid) {
        global $CFG;

        // The grouped array is supposed to have just one element - let us use foreach anyway
        // just to be sure we do not loose anything.
        foreach ($choicegnrquizs as $choicegnrquiz) {
            // Append an artificial 'id' attribute (is not included in moodle.xml).
            $choicegnrquiz['id'] = $this->converter->get_nextid();

            // Replay the upgrade step 2009021801.
            $choicegnrquiz['correctfeedbackformat']               = 0;
            $choicegnrquiz['partiallycorrectfeedbackformat']      = 0;
            $choicegnrquiz['incorrectfeedbackformat']             = 0;

            if ($CFG->texteditors !== 'textarea' and $oldquestiontextformat == FORMAT_MOODLE) {
                $choicegnrquiz['correctfeedback']                 = text_to_html($choicegnrquiz['correctfeedback'], false, false, true);
                $choicegnrquiz['correctfeedbackformat']           = FORMAT_HTML;
                $choicegnrquiz['partiallycorrectfeedback']        = text_to_html($choicegnrquiz['partiallycorrectfeedback'], false, false, true);
                $choicegnrquiz['partiallycorrectfeedbackformat']  = FORMAT_HTML;
                $choicegnrquiz['incorrectfeedback']               = text_to_html($choicegnrquiz['incorrectfeedback'], false, false, true);
                $choicegnrquiz['incorrectfeedbackformat']         = FORMAT_HTML;
            } else {
                $choicegnrquiz['correctfeedbackformat']           = $oldquestiontextformat;
                $choicegnrquiz['partiallycorrectfeedbackformat']  = $oldquestiontextformat;
                $choicegnrquiz['incorrectfeedbackformat']         = $oldquestiontextformat;
            }

            $choicegnrquiz['correctfeedback'] = $this->migrate_files(
                    $choicegnrquiz['correctfeedback'], 'question', 'correctfeedback', $questionid);
            $choicegnrquiz['partiallycorrectfeedback'] = $this->migrate_files(
                    $choicegnrquiz['partiallycorrectfeedback'], 'question', 'partiallycorrectfeedback', $questionid);
            $choicegnrquiz['incorrectfeedback'] = $this->migrate_files(
                    $choicegnrquiz['incorrectfeedback'], 'question', 'incorrectfeedback', $questionid);

            $this->write_xml('choicegnrquiz', $choicegnrquiz, array('/choicegnrquiz/id'));
        }
    }
}
