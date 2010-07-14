<?php

require_once($CFG->dirroot.'/lib/formslib.php');


class settings_group_choice_summary_form extends moodleform {

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


        $mform = & $this->_form;
    }
}
?>
