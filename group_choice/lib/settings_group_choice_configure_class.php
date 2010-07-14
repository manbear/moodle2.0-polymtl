<?php
require_once($CFG->libdir.'/adminlib.php');
require_once('block_meta.php');

/**
 * @since       Moodle 2.0
 * @package     block/group_choice
 * @author      Ricky Marcelin
 * @author      Éric Comte Marois
 */
class settings_group_choice_configure_class {
    protected $action;
    protected $mode;
    protected $id;
    protected $instance;
    protected $adminroot;

    protected $outputs;
    public    $moodleform;


    public function __construct($id = false) {

        $this->action   = optional_param('action', 'base', PARAM_ALPHA);
        $this->mode     = optional_param('mode', 'show', PARAM_ALPHAEXT);
        $this->id       = optional_param('id', false, PARAM_INT);
        $this->instance = required_param('instance');
    }


    /**
     * Method to list the preferences
     *
     * Those preferences can change the look and feel of the block.
     * The admin can choose to display the groups or not in the block's content.
     * He can also choose to set the maximum of student per groups and many more
     * features.
     */
    public function show() {
        global $CFG, $DB, $OUTPUT;

        $url = $CFG->wwwroot.'/blocks/group_choice/index.php?action=configure&mode=save_preferences&id='.$this->id
               .'&instance='.$this->instance;
        $this->moodleform = & new settings_group_choice_configure_form($url);

    }

    /**
     * Main display method
     *
     * Prints the block header and the common block outputs, the
     * selected action outputs, his form and the footer
     *
     * $outputs value depends on $mode and $action selected
     */
    public function display() {

        global $CFG, $OUTPUT;

        $this->_display_header();

        // Other outputs
        if (!empty($this->outputs)) {
            echo $this->outputs;
        }

        // Form
        if ($this->moodleform) {
            $this->moodleform->display();
        }

        // Footer
        echo $OUTPUT->footer();
    }


    /**
    * Displays the header
    */
    protected function _display_header() {
        global $DB, $CFG, $PAGE, $OUTPUT, $SITE;
        
        $course     =   block_meta::get_course_as_object($this->id);
        
        // Strings
        $actionstr  = get_string('action'.$this->action, 'block_group_choice');
        $titlestr   = get_string('settings_template', 'block_group_choice');

        // Header
        $PAGE->set_title($titlestr);
        $PAGE->set_heading($titlestr);
        $redirect_url   = $CFG->wwwroot.'/course/view.php?id='.$this->id;

        $PAGE->navbar->add($SITE->shortname, new moodle_url($CFG->wwwroot));
        $PAGE->navbar->add($course->shortname,
                           new moodle_url($redirect_url));

        $PAGE->navbar->add($actionstr);

        echo $OUTPUT->header();

        include(dirname(dirname(__FILE__)).'/tabs.php');

        echo $OUTPUT->heading($actionstr, 1);
    }

    /*
     * Save the configurations that the teacher/admin wants in the block's instance DB
     */
    public function save_preferences()  {
        global $CFG, $PAGE, $OUTPUT, $SITE, $SESSION, $DB;
       
        confirm_sesskey(); // à koi sa sert?
        $url = $CFG->wwwroot.'/blocks/group_choice/index.php?action=configure&mode=save_preferences&id='.$this->id
                .'&instance='.$this->instance;

        /**
         * MoodleForm
         */
        $this->moodleform = & new settings_group_choice_configure_form($url);

        $data = $this->moodleform->get_data();

        if($data)    { // if there's data send back from the form POST/GET

           $timelimit   = $data->timelimit; // the timelimit set by the teacher

           /**
            * If the date of the timelimit is not valid
            */
           if($timelimit < strtotime("today") && $timelimit != 0)  {

                /**
                 * À retirer, mettre les outputs dans un fichier xml (view)
                 */
               $this->outputs   .= '<div style="border: 2px solid #CC0000; background-color:#FFFFCC; text-align:center; margin-bottom: 5px;
                                                font-family: Verdana, Arial, sans-serif; font-weight: normal;">
                                        ERROR: The date you entered is not correct; verify the date and submit again thank you... </div>' ;


           } else { // if the date timelimit is valid
               $block_instance = block_meta::get_block_instance($this->instance, $this->id);

               /**
                * We update the fields and update the $DB
                */
               if($block_instance != false) {
                   $updated_fields = $this->_set_preferences($block_instance->id, $data, $timelimit);
                   $SESSION->savedparams = block_meta::update_preferences($updated_fields);
               }
           }
        }

        /**
         * À retirer, mettre les outputs dans un fichier xml (view)
         */
        if($SESSION->savedparams == 1)  {
            $this->outputs   .= '<div style="border: 2px solid #00FF00; background-color:#F0FFF0; text-align:center; margin-bottom: 5px;
                                                font-family: Verdana, Arial, sans-serif; font-weight: normal;">
                                        SUCCESS: Your preferences have been saved ... </div>' ;
            unset($SESSION->savedparams);
            
        }   else if ($SESSION->savedparams == 2)    {
            $this->outputs   .= '<div style="border: 2px solid #CC0000; background-color:#FFFFCC; text-align:center; margin-bottom: 5px;
                                                font-family: Verdana, Arial, sans-serif; font-weight: normal;">
                                        ERROR: Your preferences were not saved; please re-try again thank you... </div>' ;
            unset($SESSION->savedparams);
        }
    }

    /**
     *
     * @param <type> $block_id
     * @param <type> $data
     * @param <type> $timelimit
     * @return <type> 
     */
    private function _set_preferences($block_id, $data, $timelimit) {
        $fields                           = new object();
        $fields->id                       = $block_id;
        $fields->showgroups               = (!$data->showhidegroups)     ? 0 : $data->showhidegroups;
        $fields->maxmembers               = (!$data->maxmembers || $data->maxmembers < 2) ? 2 : $data->maxmembers;
        $fields->allowchangegroups        = (!$data->allowgroupchange)   ? 0 : $data->allowgroupchange;
        $fields->allowstudentteams        = (!$data->allowstudentteams)  ? 0 : $data->allowstudentteams;
        $fields->allowmultipleteams       = $data->allowstudentmultiple;
        $fields->timelimit                = $timelimit;

        return $fields;
        
    }
}

?>
