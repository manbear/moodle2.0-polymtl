<?php

require_once($CFG->dirroot.'/lib/formslib.php');


class settings_group_choice_export_form extends moodleform {

    private $settingshtml;


    public function __construct($url) {
        parent::__construct($url);
    }

    public function setHtmlData($settingshtml = false)  {
            $this->settingshtml = $settingshtml;
    }


    function definition () {
        global $CFG, $PAGE, $SESSION, $DB, $OUTPUT;

        $id             = required_param('id');
        $instance       = required_param('instance');

        $mform = & $this->_form;

        $mform->addElement('header', 'general', get_string('exportsettings', 'block_group_choice'));

        $mform->addElement('html', '<label for="exportfields">'.get_string('selectexportfields', 'block_group_choice').'</label>');
            $checkbox_array    = array();
            $checkbox_array[]  = &MoodleQuickForm::createElement('checkbox', 'lastname' , '', get_string('lastname'     ,'block_group_choice'),1);
            $checkbox_array[]  = &MoodleQuickForm::createElement('checkbox', 'firstname', '', get_string('firstname'    ,'block_group_choice'),2);
            $checkbox_array[]  = &MoodleQuickForm::createElement('checkbox', 'idnumber' , '', get_string('idnumber'     ,'block_group_choice'),3);
            $checkbox_array[]  = &MoodleQuickForm::createElement('checkbox', 'email'    , '', get_string('email'        ,'block_group_choice'),4);
            $mform->addGroup($checkbox_array, 'checkar', '', array('<br/>'), false);
            $mform->addGroupRule('checkar', array('email' => array(array(get_string('fieldrequired', 'block_group_choice'), 'required', null, 'client', true))));
            $mform->setDefault('lastname'   , 1);
            $mform->setDefault('firstname'  , 1);
            $mform->setDefault('idnumber'   , 1);
            $mform->setDefault('email'      , 1);

        $mform->addElement('html', '<label for="exportformat">'.get_string('selectexportformat', 'block_group_choice').'</label>');
            $radio_array    = array();
            $radio_array[]  = &MoodleQuickForm::createElement('radio', 'exportformattype', '', get_string('excelformat' ,'block_group_choice'),1);
            $radio_array[]  = &MoodleQuickForm::createElement('radio', 'exportformattype', '', get_string('csvformat'   ,'block_group_choice'),2);
            $radio_array[]  = &MoodleQuickForm::createElement('radio', 'exportformattype', '', get_string('osdformat'   ,'block_group_choice'),3);
            $radio_array[]  = &MoodleQuickForm::createElement('radio', 'exportformattype', '', get_string('xmlformat'   ,'block_group_choice'),4);
            $mform->addGroup($radio_array, 'radioar', '', array('<br/>'), false);
            $mform->setDefault('exportformattype', 4);

        $mform->addElement('text', 'exportemaillist', get_string('exportemaillist', 'block_group_choice'), 'size="50"');
            $mform->setType('exportemaillist', PARAM_NOTAGS);
        // Submit
        if(ajaxenabled())   {
            $PAGE->requires->yui2_lib('yahoo-dom-event');
            $PAGE->requires->yui2_lib('element');
            $PAGE->requires->yui2_lib('yahoo');
            $PAGE->requires->yui2_lib('dom');
            $PAGE->requires->js('/blocks/group_choice/groupchoice.js');
        }
        $mform->addElement('submit', 'settings_templates_submit', get_string('exportfile', 'block_group_choice'));
    }
}
?>
