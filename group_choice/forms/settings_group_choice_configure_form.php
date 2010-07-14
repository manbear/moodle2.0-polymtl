<?php

require_once($CFG->dirroot.'/lib/formslib.php');


class settings_group_choice_configure_form extends moodleform {

    private $settingshtml;


    public function __construct($url) {
        parent::__construct($url);
    }

    public function setHtmlData($settingshtml = false)  {
            $this->settingshtml = $settingshtml;
    }


    function definition () {
        global $CFG, $PAGE, $SESSION, $DB;

        $id             = required_param('id');
        $instance       = required_param('instance');

        $results                        = $DB->get_record('block_group_choice', array('instance_id' => $instance, 'course_id' => $id));
        if($results)    {
            $showgroups             = $results->showgroups;
            $maxmembers             = $results->maxmembers;
            $allowchangegroups      = $results->allowchangegroups;
            $allowstudentteams      = $results->allowstudentteams;
            $allowmultipleteams     = $results->allowmultipleteams;
            $timelimit              = $results->timelimit;
        }
        $mform = & $this->_form;
        
        $mform->addElement('header', 'general', get_string('templatesettings', 'block_group_choice'));

        $mform->addElement('checkbox', 'showhidegroups', get_string('showhidegroups', 'block_group_choice'));
            $mform->setDefault('showhidegroups', $showgroups);
            
        $mform->addElement('text', 'maxmembers', get_string('maxmembers', 'block_group_choice'), 'maxlength="11" size="5"');
            $mform->addRule('maxmembers', null, 'required', null, 'client', true);
            $mform->addRule('maxmembers', null, 'numeric', null, 'client', true);
            $mform->addRule('maxmembers', null, 'nonzero', null, 'client', true);
            $mform->setType('maxmembers', PARAM_INT);
            $mform->setDefault('maxmembers', $maxmembers);

        $mform->addElement('checkbox', 'allowgroupchange', get_string('allowgroupchange', 'block_group_choice'));
            $mform->setDefault('allowgroupchange', $allowchangegroups);
        $mform->addElement('checkbox', 'allowstudentteams', get_string('allowstudentteams', 'block_group_choice'));
            $mform->setDefault('allowstudentteams', $allowstudentteams);
        $mform->addElement('selectyesno', 'allowstudentmultiple', get_string('allowstudentmultiple', 'block_group_choice'));
        $mform->setDefault('allowstudentmultiple', $allowmultipleteams);
        
        $date_array = array(
                                'startyear'     => date('Y', strtotime("today")),
                                'stopyear'      => date('Y', strtotime("+ 1 year")),
                                'applydst'      => true,
                                'optional'      => false
                            );
        $mform->addElement('date_selector', 'timelimit', 'Time Limit For Groups', $date_array);
            $mform->setDefault('timelimit', $timelimit);
        // Submit
        $mform->addElement('submit', 'settings_templates_submit', get_string('savechanges'));
    }
}
?>
