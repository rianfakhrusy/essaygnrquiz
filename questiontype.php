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
 * Question type class for the essaygnrquiz question type.
 *
 * @package    qtype
 * @subpackage essaygnrquiz
 * @copyright  2005 Mark Nielsen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');


/**
 * The essaygnrquiz question type.
 *
 * @copyright  2005 Mark Nielsen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_essaygnrquiz extends question_type {
    public function is_manual_graded() {
        return true;
    }

    public function response_file_areas() {
        return array('attachments', 'answer');
    }

    public function get_question_options($question) {
        global $DB;
        $question->options = $DB->get_record('qtype_essaygnrquiz_options',
                array('questionid' => $question->id), '*', MUST_EXIST);
        parent::get_question_options($question);
    }

    public function save_question_options($formdata) {
        global $DB;
        $context = $formdata->context;

        $options = $DB->get_record('qtype_essaygnrquiz_options', array('questionid' => $formdata->id));
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $formdata->id;
            $options->id = $DB->insert_record('qtype_essaygnrquiz_options', $options);
        }

        $options->time = $formdata->time;
        $options->difficulty = $formdata->difficulty;
        $options->distinguishingdegree = $formdata->distinguishingdegree;

        $options->responseformat = $formdata->responseformat;
        $options->responserequired = $formdata->responserequired;
        $options->responsefieldlines = $formdata->responsefieldlines;
        $options->attachments = $formdata->attachments;
        $options->attachmentsrequired = $formdata->attachmentsrequired;
        $options->graderinfo = $this->import_or_save_files($formdata->graderinfo,
                $context, 'qtype_essaygnrquiz', 'graderinfo', $formdata->id);
        $options->graderinfoformat = $formdata->graderinfo['format'];
        $options->responsetemplate = $formdata->responsetemplate['text'];
        $options->responsetemplateformat = $formdata->responsetemplate['format'];
        $DB->update_record('qtype_essaygnrquiz_options', $options);
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $question->responseformat = $questiondata->options->responseformat;
        $question->responserequired = $questiondata->options->responserequired;
        $question->responsefieldlines = $questiondata->options->responsefieldlines;
        $question->attachments = $questiondata->options->attachments;
        $question->attachmentsrequired = $questiondata->options->attachmentsrequired;
        $question->graderinfo = $questiondata->options->graderinfo;
        $question->graderinfoformat = $questiondata->options->graderinfoformat;
        $question->responsetemplate = $questiondata->options->responsetemplate;
        $question->responsetemplateformat = $questiondata->options->responsetemplateformat;

        $question->time = $questiondata->options->time;
        $question->difficulty = $questiondata->options->difficulty;
        $question->distinguishingdegree = $questiondata->options->distinguishingdegree;
    }

    public function delete_question($questionid, $contextid) {
        global $DB;

        $DB->delete_records('qtype_essaygnrquiz_options', array('questionid' => $questionid));
        parent::delete_question($questionid, $contextid);
    }

    /**
     * @return array the different response formats that the question type supports.
     * internal name => human-readable name.
     */
    public function response_formats() {
        return array(
            'editor' => get_string('formateditor', 'qtype_essaygnrquiz'),
            'editorfilepicker' => get_string('formateditorfilepicker', 'qtype_essaygnrquiz'),
            'plain' => get_string('formatplain', 'qtype_essaygnrquiz'),
            'monospaced' => get_string('formatmonospaced', 'qtype_essaygnrquiz'),
            'noinline' => get_string('formatnoinline', 'qtype_essaygnrquiz'),
        );
    }

    /**
     * @return array the choices that should be offerd when asking if a response is required
     */
    public function response_required_options() {
        return array(
            1 => get_string('responseisrequired', 'qtype_essaygnrquiz'),
            0 => get_string('responsenotrequired', 'qtype_essaygnrquiz'),
        );
    }

    /**
     * @return array the choices that should be offered for the input box size.
     */
    public function response_sizes() {
        $choices = array();
        for ($lines = 5; $lines <= 40; $lines += 5) {
            $choices[$lines] = get_string('nlines', 'qtype_essaygnrquiz', $lines);
        }
        return $choices;
    }

    /**
     * @return array the choices that should be offered for the number of attachments.
     */
    public function attachment_options() {
        return array(
            0 => get_string('no'),
            1 => '1',
            2 => '2',
            3 => '3',
            -1 => get_string('unlimited'),
        );
    }

    /**
     * @return array the choices that should be offered for the number of required attachments.
     */
    public function attachments_required_options() {
        return array(
            0 => get_string('attachmentsoptional', 'qtype_essaygnrquiz'),
            1 => '1',
            2 => '2',
            3 => '3'
        );
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $fs = get_file_storage();
        $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, 'qtype_essaygnrquiz', 'graderinfo', $questionid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $fs = get_file_storage();
        $fs->delete_area_files($contextid, 'qtype_essaygnrquiz', 'graderinfo', $questionid);
    }


    /**
     * If your question type has a table that extends the question table, and
     * you want the base class to automatically save, backup and restore the extra fields,
     * override this method to return an array where the first element is the table name,
     * and the subsequent entries are the column names (apart from id and questionid).
     *
     * @return mixed array as above, or null to tell the base class to do nothing.
     */
    
    public function extra_question_field() {
        return array('mdl_question_essaygnrquiz', //table name
            'time', 
            'difficulty',
            'distinguishingdegree'
        );
    }

    // IMPORT/EXPORT FUNCTIONS --------------------------------- .

    /*
     * Imports question from the Moodle XML format
     *
     * Imports question using information from extra_question_fields function
     * If some of you fields contains id's you'll need to reimplement this
     */
    public function import_from_xml($data, $question, qformat_xml $format, $extra=null) {
        $question_type = $data['@']['type'];
        if ($question_type != $this->name()) {
            return false;
        }

        $extraquestionfields = $this->extra_question_field();
        if (!is_array($extraquestionfields)) {
            return false;
        }

        // Omit table name.
        array_shift($extraquestionfields);
        $qo = $format->import_headers($data);

        $qo->responseformat = $format->getpath($question,
                array('#', 'responseformat', 0, '#'), 'editor');
        $qo->responsefieldlines = $format->getpath($question,
                array('#', 'responsefieldlines', 0, '#'), 15);
        $qo->responserequired = $format->getpath($question,
                array('#', 'responserequired', 0, '#'), 1);
        $qo->attachments = $format->getpath($question,
                array('#', 'attachments', 0, '#'), 0);
        $qo->attachmentsrequired = $format->getpath($question,
                array('#', 'attachmentsrequired', 0, '#'), 0);
        $qo->graderinfo = $format->import_text_with_files($question,
                array('#', 'graderinfo', 0), '', $format->get_format($qo->questiontextformat));
        $qo->responsetemplate['text'] = $format->getpath($question,
                array('#', 'responsetemplate', 0, '#', 'text', 0, '#'), '', true);
        $qo->responsetemplate['format'] = $format->trans_format($format->getpath($question,
                array('#', 'responsetemplate', 0, '@', 'format'), $format->get_format($qo->questiontextformat)));
        $qo->qtype = $question_type;

        foreach ($extraquestionfields as $field) {
            $qo->$field = $format->getpath($data, array('#', $field, 0, '#'), '');
        }

        return $qo;
    }

    /*
     * Export question to the Moodle XML format
     *
     * Export question using information from extra_question_fields function
     * If some of you fields contains id's you'll need to reimplement this
     */
    public function export_to_xml($question, qformat_xml $format, $extra=null) {
        $extraquestionfields = $this->extra_question_field();
        if (!is_array($extraquestionfields)) {
            return false;
        }

        $fs = get_file_storage();
        $contextid = $question->contextid;
        // Get files used by the questiontext.
        $question->questiontextfiles = $fs->get_area_files(
                $contextid, 'question', 'questiontext', $question->id);
        // Get files used by the generalfeedback.
        $question->generalfeedbackfiles = $fs->get_area_files(
                $contextid, 'question', 'generalfeedback', $question->id);
        if (!empty($question->options->answers)) {
            foreach ($question->options->answers as $answer) {
                $answer->answerfiles = $fs->get_area_files(
                        $contextid, 'question', 'answer', $answer->id);
                $answer->feedbackfiles = $fs->get_area_files(
                        $contextid, 'question', 'answerfeedback', $answer->id);
            }
        }

        // Omit table name.
        array_shift($extraquestionfields);
        $expout='';
        $expout .= "    <responseformat>" . $question->options->responseformat .
                        "</responseformat>\n";
        $expout .= "    <responserequired>" . $question->options->responserequired .
                "</responserequired>\n";
        $expout .= "    <responsefieldlines>" . $question->options->responsefieldlines .
                "</responsefieldlines>\n";
        $expout .= "    <attachments>" . $question->options->attachments .
                "</attachments>\n";
        $expout .= "    <attachmentsrequired>" . $question->options->attachmentsrequired .
                "</attachmentsrequired>\n";
        $expout .= "    <graderinfo " .
                $format->format($question->options->graderinfoformat) . ">\n";
        $expout .= $format->writetext($question->options->graderinfo, 3);
        $expout .= $format->write_files($fs->get_area_files($contextid, 'qtype_essay',
                'graderinfo', $question->id));
        $expout .= "    </graderinfo>\n";
        $expout .= "    <responsetemplate " .
                $format->format($question->options->responsetemplateformat) . ">\n";
        $expout .= $format->writetext($question->options->responsetemplate, 3);
        $expout .= "    </responsetemplate>\n";

        foreach ($extraquestionfields as $field) {
            $exportedvalue = $format->xml_escape($question->options->$field);
            $expout .= "    <{$field}>{$exportedvalue}</{$field}>\n";
        }

        $extraanswersfields = $this->extra_answer_fields();
        if (is_array($extraanswersfields)) {
            array_shift($extraanswersfields);
        }
        foreach ($question->options->answers as $answer) {
            $extra = '';
            if (is_array($extraanswersfields)) {
                foreach ($extraanswersfields as $field) {
                    $exportedvalue = $format->xml_escape($answer->$field);
                    $extra .= "      <{$field}>{$exportedvalue}</{$field}>\n";
                }
            }

            $expout .= $format->write_answer($answer, $extra);
        }
        return $expout;
    }
}
