<?php
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/group/lib.php');
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
class settings_group_choice_formation_class {


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
        if(($time_limit = time_left($this->instance, $this->id)) !== 'timeup')    {
            $this->outputs .= $time_limit;
            $url = $CFG->wwwroot.'/blocks/group_choice/index.php?action=formation&mode=assign_groups&id='.$this->id
                   .'&instance='.$this->instance;
            $this->moodleform = & new settings_group_choice_formation_form($url);
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

   

    public function assign_groups()   {
        global $CFG, $DB, $USER;

        $url = $CFG->wwwroot.'/blocks/group_choice/index.php?action=formation&mode=assign_groups&id='.$this->id
        .'&instance='.$this->instance;


        /**
         * MoodleForm
         */
        $this->moodleform = & new settings_group_choice_formation_form($url);

        /**
         * Vérifie si le temps de formation des équipes est écoulée ou non.
         */
        if(($time_limit = time_left($this->instance, $this->id)) !== 'timeup')    
            $this->outputs .= $time_limit;

        $data = $this->moodleform->get_data(); // get POST/GET data.
        
        if($data)    { // if there's data send back from the form POST/GET

            if(isset($data->joingroup)) { // si il existe des groupes à joindre.
                /**
                 * foreach key in the array data:
                 *      get the id (integer) from the key
                 *      if id is valid:
                 *          send invitation.
                 */
                foreach($data as $key => $value)    { 
                    $group_id     = preg_replace("/[^0-9]/", '', $key); // rip the letters to get only the id.

                    if(empty($group_id) == false ) { // if we got the group id

                        // get the group
                        $group = block_group::get_group_instance($group_id);

                        echo "passé la requete de l'instance";

                        /**
                         * If the student is NOT in the group,
                         * we check if the group has a teamleader
                         *      if not:
                         *          add the student to the group
                         *      else:
                         *          send an invitation to the teamleader
                         */
                        if( $group->is_student_in_group($USER->id, $group_id) == false ) {

                            /**
                             * If the group doesn't have a teamleader
                             */
                            if ( $group->has_teamleader() == false ) {
                                $group->add_student_to_group( $USER->id );

                            } else { // if the group has already a teamleader
                                invitation::send_invite_to_group_teamleader($USER->id, $group_id, $this->id);
                            }

                        } else { // the student is already in the group
                            // TODO enlever... me semble (eric)
                            echo 'you\'re already in this group <br/>';
                        }
                        unset($group_id);
                    }
                }
            }
        }
    }
}

?>
