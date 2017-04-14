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
 * Defines the editing form for the essaygnrquiz question type.
 *
 * @package    qtype
 * @subpackage essaygnrquiz
 * @copyright  2007 Jamie Pratt me@jamiep.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Essay question type editing form.
 *
 * @copyright  2007 Jamie Pratt me@jamiep.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_essaygnrquiz_edit_form extends question_edit_form {

    protected function definition_inner($mform) {
        $qtype = question_bank::get_qtype('essaygnrquiz');

        //question attributes
        $mform->addElement('header', 'questionattributes', get_string('questionattributes', 'qtype_essaygnrquiz'));

        $mform->addElement('duration', 'time', get_string('time', 'qtype_shortgnrquiz'),
                array('optional' => false));
        $mform->setType('time', PARAM_INT);
        $mform->addRule('time', null, 'required', null, 'client');
        $mform->addHelpButton('time', 'time', 'qtype_shortgnrquiz');

        $mform->addElement('text', 'difficulty', get_string('difficulty', 'qtype_shortgnrquiz'),
                array('size' => 7));
        $mform->setType('difficulty', PARAM_TEXT);
        $mform->setDefault('difficulty', 0.5);
        $mform->addRule('difficulty', null, 'required', null, 'client');
        $mform->addHelpButton('difficulty', 'difficulty', 'qtype_shortgnrquiz');

        $mform->addElement('text', 'distinguishingdegree', get_string('distinguishingdegree', 'qtype_shortgnrquiz'),
                array('size' => 7));
        $mform->setType('distinguishingdegree', PARAM_TEXT);
        $mform->setDefault('distinguishingdegree', 0.5);
        $mform->addRule('distinguishingdegree', null, 'required', null, 'client');
        $mform->addHelpButton('distinguishingdegree', 'distinguishingdegree', 'qtype_shortgnrquiz');


        $mform->addElement('header', 'responseoptions', get_string('responseoptions', 'qtype_essaygnrquiz'));
        $mform->setExpanded('responseoptions');

        $mform->addElement('select', 'responseformat',
                get_string('responseformat', 'qtype_essaygnrquiz'), $qtype->response_formats());
        $mform->setDefault('responseformat', 'editor');

        $mform->addElement('select', 'responserequired',
                get_string('responserequired', 'qtype_essaygnrquiz'), $qtype->response_required_options());
        $mform->setDefault('responserequired', 1);
        $mform->disabledIf('responserequired', 'responseformat', 'eq', 'noinline');

        $mform->addElement('select', 'responsefieldlines',
                get_string('responsefieldlines', 'qtype_essaygnrquiz'), $qtype->response_sizes());
        $mform->setDefault('responsefieldlines', 15);
        $mform->disabledIf('responsefieldlines', 'responseformat', 'eq', 'noinline');

        $mform->addElement('select', 'attachments',
                get_string('allowattachments', 'qtype_essaygnrquiz'), $qtype->attachment_options());
        $mform->setDefault('attachments', 0);

        $mform->addElement('select', 'attachmentsrequired',
                get_string('attachmentsrequired', 'qtype_essaygnrquiz'), $qtype->attachments_required_options());
        $mform->setDefault('attachmentsrequired', 0);
        $mform->addHelpButton('attachmentsrequired', 'attachmentsrequired', 'qtype_essaygnrquiz');
        $mform->disabledIf('attachmentsrequired', 'attachments', 'eq', 0);

        $mform->addElement('header', 'responsetemplateheader', get_string('responsetemplateheader', 'qtype_essaygnrquiz'));
        $mform->addElement('editor', 'responsetemplate', get_string('responsetemplate', 'qtype_essaygnrquiz'),
                array('rows' => 10),  array_merge($this->editoroptions, array('maxfiles' => 0)));
        $mform->addHelpButton('responsetemplate', 'responsetemplate', 'qtype_essaygnrquiz');

        $mform->addElement('header', 'graderinfoheader', get_string('graderinfoheader', 'qtype_essaygnrquiz'));
        $mform->setExpanded('graderinfoheader');
        $mform->addElement('editor', 'graderinfo', get_string('graderinfo', 'qtype_essaygnrquiz'),
                array('rows' => 10), $this->editoroptions);
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);

        if (empty($question->options)) {
            return $question;
        }

        $question->responseformat = $question->options->responseformat;
        $question->responserequired = $question->options->responserequired;
        $question->responsefieldlines = $question->options->responsefieldlines;
        $question->attachments = $question->options->attachments;
        $question->attachmentsrequired = $question->options->attachmentsrequired;
        $question->time = $question->options->time;
        $question->difficulty = $question->options->difficulty;
        $question->distinguishingdegree = $question->options->distinguishingdegree;

        $draftid = file_get_submitted_draft_itemid('graderinfo');
        $question->graderinfo = array();
        $question->graderinfo['text'] = file_prepare_draft_area(
            $draftid,           // Draftid
            $this->context->id, // context
            'qtype_essaygnrquiz',      // component
            'graderinfo',       // filarea
            !empty($question->id) ? (int) $question->id : null, // itemid
            $this->fileoptions, // options
            $question->options->graderinfo // text.
        );
        $question->graderinfo['format'] = $question->options->graderinfoformat;
        $question->graderinfo['itemid'] = $draftid;

        $question->responsetemplate = array(
            'text' => $question->options->responsetemplate,
            'format' => $question->options->responsetemplateformat,
        );

        return $question;
    }

    public function validation($fromform, $files) {
        $errors = parent::validation($fromform, $files);

        // Don't allow both 'no inline response' and 'no attachments' to be selected,
        // as these options would result in there being no input requested from the user.
        if ($fromform['responseformat'] == 'noinline' && !$fromform['attachments']) {
            $errors['attachments'] = get_string('mustattach', 'qtype_essaygnrquiz');
        }

        // If 'no inline response' is set, force the teacher to require attachments;
        // otherwise there will be nothing to grade.
        if ($fromform['responseformat'] == 'noinline' && !$fromform['attachmentsrequired']) {
            $errors['attachmentsrequired'] = get_string('mustrequire', 'qtype_essaygnrquiz');
        }

        // Don't allow the teacher to require more attachments than they allow; as this would
        // create a condition that it's impossible for the student to meet.
        if ($fromform['attachments'] != -1 && $fromform['attachments'] < $fromform['attachmentsrequired'] ) {
            $errors['attachmentsrequired']  = get_string('mustrequirefewer', 'qtype_essaygnrquiz');
        }

        if (($fromform['time']<=0)||(!is_numeric($fromform['time']))) {
            $errors['time'] = get_string('morethanzero', 'qtype_shortgnrquiz');
        }
        if (($fromform['difficulty']<0.0)||($fromform['difficulty']>1.0)||(!is_numeric($fromform['difficulty']))) {
            $errors['difficulty'] = get_string('betweenzeroandone', 'qtype_shortgnrquiz');
        }
        if (($fromform['distinguishingdegree']<0.0)||($fromform['distinguishingdegree']>1.0)||(!is_numeric($fromform['distinguishingdegree']))) {
            $errors['distinguishingdegree'] = get_string('betweenzeroandone', 'qtype_shortgnrquiz');
        }

        return $errors;
    }

    public function qtype() {
        return 'essaygnrquiz';
    }
}
