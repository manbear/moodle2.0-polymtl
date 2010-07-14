<?php
require_once($CFG->libdir.'/adminlib.php');
require_once('block_group.php');
require_once('block_meta.php');
require_once('invitation.php');
/**
 *
 * @since      Moodle 2.0
 * @package    block/group_choice
 * @author     Ricky Marcelin
 * @author     Éric Comte Marois
 */
class settings_group_choice_studentformation_class {


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
     * Method to list the templates avaiable on the system
     *
     * It allows users to access the different template
     * actions (preview, load, download and delete)
     */
    public function show() {
        global $CFG, $DB, $OUTPUT;
        $this->outputs  = '';
        if(block_group::is_student_a_teamleader($this->id, $USER->id) == false) {
            $this->outputs .= get_string('notteamleader', 'block_group_choice');
        }else if(($time_limit = time_left($this->instance, $this->id)) !== 'timeup'){
            $this->outputs .= $time_limit;
            $url = $CFG->wwwroot.'/blocks/group_choice/index.php?action=studentformation&mode=invite_students&id='.$this->id
                   .'&instance='.$this->instance;
            $this->moodleform = & new settings_group_choice_studentformation_form($url);
        }   else    {
            $this->outputs .= get_string('timeup', 'block_group_choice');
        }
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
        global $CFG, $PAGE, $OUTPUT, $SITE;
        $course     =   block_meta::get_course_as_object($this->id);
        // Strings
        $actionstr = get_string('action'.$this->action, 'block_group_choice');
        $titlestr = get_string('settings_template', 'block_group_choice');

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
    
    public function invite_students()   {
        global $CFG, $DB, $USER;

        $url = $CFG->wwwroot.'/blocks/group_choice/index.php?action=studentformation&mode=invite_students&id='.$this->id
        .'&instance='.$this->instance;

        /**
         * MoodleForm
         */
        $this->moodleform = & new settings_group_choice_studentformation_form($url);


        /**
         * Vérifie si le temps de formation des équipes est écoulée ou non.
         */
        if(($time_limit = time_left($this->instance, $this->id)) !== 'timeup')
            $this->outputs .= $time_limit; // <block's responsability!!>

        $data = $this->moodleform->get_data(); // get POST/GET data.

        // if data is not empty
        if($data)    { 
            /**
             * foreach key in the array data:
             *      get the id (integer) from the key
             *      if id is valid:
             *          send invitation.
             */
            foreach($data as $key => $value)   {
                $studentid = preg_replace("/[^0-9]/", '', $key); // rip the letters to get only the id.

                if(empty($studentid) == false)  { // if we got an number (int).
                    invitation::send_invite_to_student($studentid, $data->mygroup, $this->id); // group_id == $data->mygroup ??
                }
            }
        }
    }
}
?>