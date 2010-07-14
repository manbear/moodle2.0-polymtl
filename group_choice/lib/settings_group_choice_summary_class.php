<?php
require_once($CFG->libdir.'/adminlib.php');
require_once('block_group.php');
require_once('block_list.php');
require_once('block_meta.php');
require_once('inbox.php');
/**
 * @since       Moodle 2.0
 * @package     block/group_choice
 * @author      Ricky Marcelin
 * @author      Éric Comte Marois
 */
class settings_group_choice_summary_class {
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

        $this->outputs  = '';
        if(($time_limit = time_left($this->instance, $this->id)) !== 'timeup')
            $this->outputs .= $time_limit;

        if($this->_display_groups() == false) {
            $this->outputs  = $OUTPUT->box_start('generalbox', 'id_notemplates');
            $this->outputs .= get_string('noteams', 'block_group_choice');
            $this->outputs .= $OUTPUT->box_end();
        }
        $this->_show_invites();
        anchor('summuray', 'display', array('id' => '5'));
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
        global $CFG, $PAGE, $OUTPUT, $SITE, $DB;
        $course     =   block_meta::get_course_as_object($this->id);
        // Strings
        $actionstr = get_string('action'.$this->action, 'block_group_choice').$course->shortname;
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
     /**
     * Displays all the groups in the course in a table.
     * @return bool     TRUE    -> there's a group to be displayed
     *                    FALSE   -> there's no group to be displayed
     */
    private function _display_groups()  {
        global $CFG, $DB, $OUTPUT, $USER;

        /**
         * we get the groups in which the student is a member
         */
        $groups = block_group::get_all_groups_concerned_by_student($this->id, $USER->id);

        // if the student is member of at least 1 group.
        if($groups) {

            /**
             * for the view (BEGINNING)
             */
            $this->outputs  .= $OUTPUT->box_start('generalbox', 'groups_lists');
            $this->outputs  .= '<br />';
            $this->outputs  .= $OUTPUT->heading(get_string('groupsin', 'block_group_choice'));

            /**
             * the table head
             */
            $table = new html_table();
            $table->attributes['class'] = 'generaltable boxaligncenter';
            $table->head  = array(get_string('coursegroups', 'block_group_choice'), get_string('groupmembers', 'block_group_choice'),
                                  get_string('groupsmaxmembers', 'block_group_choice'),
                                  (time_left($this->instance, $this->id) != 'timeup') ? get_string('action') : '');

            $table->align = array('center', 'center', 'center', 'center', 'center');

            /**
             * Get a wellformed array containing the informations that we want to output to the browser.
             */
            $groups_list = block_list::format_student_groups_into_wellformed_array($groups);
            $maxmembers_per_group = block_meta::get_max_members_allowed_by_groups($this->instance, $this->id);

            foreach($groups_list as $list)   {
                $quit_link    = $CFG->wwwroot.'/blocks/group_choice/leave.php?&groupid='.$list['group'].'&studentid='.$USER->id;
                $table->data[] = array(format_text(get_string('groupsname', 'block_group_choice').$list['group'], FORMAT_PLAIN),
                                       format_text($list['students'], FORMAT_PLAIN),
                                       format_text(count(explode(',',$list['students'])).'/'.$maxmembers_per_group, FORMAT_PLAIN),
                                       (time_left($this->instance, $this->id) != 'timeup') ? '<a href="'.$quit_link.'">'.get_string('leaveteam', 'block_group_choice').'</a>' : '');
            }


            /**
             * for the view (END)
             */
            $this->outputs .= html_writer::table($table);
            $this->outputs .= $OUTPUT->box_end();
            
            return true;

        } else { // the student is not a member of any group.
            return false;
        }
    }

    private function _show_invites()    {
        global $CFG, $DB, $USER, $OUTPUT;

        $teamleader_invitations = inbox::get_invitations_to_teamleader_of_group_by_student($this->id, $USER->id);
        $student_invitations = inbox::get_invitations_to_student_by_teamleader($this->id, $USER->id);

        /**
         * Section of the teamleader's invitations
         */
        if(!empty($teamleader_invitations))  { // if some invitations for the teamleader exists

            /**
             * for the view (BEGINNING)
             */
            $this->outputs  .= $OUTPUT->box_start('generalbox', 'groups_invite');
            $this->outputs  .= '<br />';
            $this->outputs  .= $OUTPUT->heading(get_string('groupsinvite', 'block_group_choice'));

            /**
             * the table head
             */
            $table = new html_table();
            $table->attributes['class'] = 'generaltable boxaligncenter';
            $table->head  = array(get_string('invitemessage', 'block_group_choice'), get_string('action', 'block_group_choice'));

            $table->align = array('center', 'center', 'center');

            /**
             * Append the invitations informations into the $table->data
             */
            foreach ($teamleader_invitations as $group_invitations) { // the teamleader might have several group in which there is invitations.
                foreach($group_invitations as $invitation) { // foreach group there might be several invitations
                    $user          = $DB->get_record('user', array('id'=> $invitation->user_id));
                    $refuse_link    = $CFG->wwwroot.'/blocks/group_choice/refuse.php?courseid='.$this->id.'&groupid='.$invitation->group_id.'&studentid='.$user->id.'$instance='.$this->instance;
                    $accept_link    = $CFG->wwwroot.'/blocks/group_choice/accept.php?courseid='.$this->id.'&groupid='.$invitation->group_id.'&studentid='.$user->id.'$instance='.$this->instance;
                    $table->data[] = array($OUTPUT->user_picture($user, array('courseid'=>$this->id))
                                           .$user->firstname . ' '. $user->lastname.format_text(get_string('invitemessage', 'block_group_choice') . $invitation->group_id, FORMAT_PLAIN),
                                           '<a href="'.$accept_link.'">Accept</a> | <a href="'.$refuse_link.'"> Refuse </a>');
                }
            }
            /**
             * for the view (END)
             */
            $this->outputs .= html_writer::table($table);
            $this->outputs .= $OUTPUT->box_end();
        }


        /**
         * Section of the student's invitations
         */
        if(!empty($student_invitations))  { // if some invitations for the student exists

            /**
             * for the view (BEGINNING)
             */
            $this->outputs  .= $OUTPUT->box_start('generalbox', 'groups_invite');
            $this->outputs  .= '<br />';
            $this->outputs  .= $OUTPUT->heading(get_string('groupsinvite', 'block_group_choice'));

            /**
             * the table head
             */
            $table = new html_table();
            $table->attributes['class'] = 'generaltable boxaligncenter';
            $table->head  = array(get_string('invitemessage', 'block_group_choice'), get_string('action', 'block_group_choice'));

            $table->align = array('center', 'center', 'center');

            /**
             * Append the invitations informations into the $table->data
             */
            foreach ($student_invitations as $invitation) {
                $user          = $DB->get_record('user', array('id'=> block_group::get_group_teamleader_id($invitation->group_id)));
                $refuse_link    = $CFG->wwwroot.'/blocks/group_choice/refuse.php?courseid='.$this->id.'&groupid='.$invitation->group_id.'&studentid='.$USER->id.'$instance='.$this->instance;
                $accept_link    = $CFG->wwwroot.'/blocks/group_choice/accept.php?courseid='.$this->id.'&groupid='.$invitation->group_id.'&studentid='.$USER->id.'$instance='.$this->instance;
                $table->data[] = array($OUTPUT->user_picture($user, array('courseid'=>$this->id))
                                       .$user->firstname . ' '. $user->lastname.format_text(get_string('invitemessage', 'block_group_choice') . $invitation->group_id, FORMAT_PLAIN),
                                       '<a href="'.$accept_link.'">Accept</a> | <a href="'.$refuse_link.'"> Refuse </a>');
            }
            
            /**
             * for the view (END)
             */
            $this->outputs .= html_writer::table($table);
            $this->outputs .= $OUTPUT->box_end();
        }
        
    }
}
?>
