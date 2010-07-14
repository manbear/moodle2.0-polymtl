<?php
require_once($CFG->libdir.'/adminlib.php');
require_once('block_group.php');
require_once('block_list.php');
require_once('block_meta.php');

/**
 *
 * @since      Moodle 2.0
 * @package    block/group_choice
 * @author     Ricky Marcelin
 * @author     Éric Comte Marois
 */
class settings_group_choice_list_class {


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
        if($this->_display_groups() == false) {
            $this->outputs  = $OUTPUT->box_start('generalbox', 'id_notemplates');
            $this->outputs .= '<ul>'.get_string('notemplates', 'block_settings_templates');
            $this->outputs .= '</ul>';
            $this->outputs .= $OUTPUT->box_end();
        }
        $this->_display_single_students();
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

    /**
     * Displays all the groups in the course in a table.
     * @return bool     TRUE    -> there's a group to be displayed
     *                    FALSE   -> there's no group to be displayed
     */
    private function _display_groups()  {
        global $CFG, $DB, $OUTPUT;

        // get all the groups concerned
        $groups = block_group::get_all_groups_from_course($this->id);

        // If there is some groups in the course
        if($groups)
        {
            /**
             * for the view (BEGINNING)
             */
            $this->outputs  .= $OUTPUT->box_start('generalbox', 'groups_lists');
            $this->outputs  .= '<br />';
            $this->outputs  .= $OUTPUT->heading(get_string('existinggroups', 'block_group_choice'));

            /**
             * the table head
             */
            $table = new html_table();
            $table->attributes['class'] = 'generaltable boxaligncenter'; // css class
            $table->head  = array(get_string('coursegroups', 'block_group_choice'), get_string('groupmembers', 'block_group_choice'),
                                  get_string('groupsmaxmembers', 'block_group_choice'),get_string('exceedmaxmembers', 'block_group_choice'),
                                  get_string('lastmodification', 'block_group_choice'));

            $table->align = array('center', 'center', 'center', 'center', 'center'); // align text to the center

            /**
             * we format the groups in a wellformed array that will be easier to parse
             * to append the information into the $table->data
             */
            $groups_list = block_list::format_groups_into_wellformed_array($groups);
            $table->data = $this->_groups_display_set_table_data($groups_list); // set the $table->data

            /**
             * for the view (END)
             */
            $this->outputs .= html_writer::table($table);
            $this->outputs .= $OUTPUT->box_end();

            return true;

        } else { // if there is no group for this course
            return false;
        }
    }

    /**
     * This method set the $table->data
     * for the method "$this->_display_groups"
     *
     * @param <type> $groups_list
     * @return <type>
     */
    private function _groups_display_set_table_data($groups_list) {
        if ( empty($groups_list) != false ) { // if the array is not empty
            $data = array(); // contains what $table->data will receive
            $maxmembers_per_group = block_meta::get_max_members_allowed_by_groups($this->id);

            foreach ($groups_list as $list) {
                $data[] = array(format_text(get_string('groupsname', 'block_group_choice').$list['group'], FORMAT_PLAIN),
                                format_text($list['students'], FORMAT_PLAIN),
                                format_text(count(split(',',$list['students'])).'/'.$maxmembers_per_group, FORMAT_PLAIN),
                                ($list['members'] > $maxmembers_per_group) ? strtoupper(get_string('yes')) : strtoupper(get_string('no')),
                                date('d-m-Y', $list['modification']));
            }
            return $data; // return the data to add to the table.

        } else { // if the array is empty
            return false;
        }
    }

    /**
     * Displays all the single students in the course.
     */
    private function _display_single_students() {
        global $DB, $OUTPUT;

        /**
         * TODO ERIC ET RICKY!!
         *
         * Changer ce code SQL par des méthodes officielles de moodle.
         */
        // <TO CHANGE!!!>
        $select_students    = "SELECT  u.id, u.firstname, u.lastname, u.email
                                FROM
                                 mdl_user as u,
                                 mdl_role_assignments as ra,
                                 mdl_context as con,
                                 mdl_role as r
                                WHERE
                                 u.id = ra.userid AND
                                 ra.contextid = con.id AND
                                 con.contextlevel = 50 AND
                                 con.instanceid = {$this->id}  AND
                                 ra.roleid = r.id AND
                                 (r.shortname = 'student' OR
                                 r.shortname = 'guest')";
                                 
        $students           = $DB->get_records_sql($select_students);
        // </TO CHANGE!!!>

        if($students)   {
            /**
             * for the view (BEGINNING)
             */
            $this->outputs  .= $OUTPUT->box_start('generalbox', 'groups_lists');
            $this->outputs  .= '<br />';
            $this->outputs  .= $OUTPUT->heading(get_string('lonestudents', 'block_group_choice'));

            /**
             * the table head
             */
            $table = new html_table();
            $table->attributes['class'] = 'generaltable boxaligncenter';
            $table->head  = array(get_string('firstname'), get_string('lastname'),
                                    get_string('email'));
            $table->align = array('center', 'center', 'center', 'center');

            /**
             * set the table->data with the students that are not in any groups.
             */
            $table->data = $this->_display_single_students_set_table_data($students);

            /**
             * for the view (END)
             */
            $this->outputs .= html_writer::table($table);
            $this->outputs .= $OUTPUT->box_end();
        }
    }

    /**
     *
     * @param <type> $students
     * @return <type> 
     */
    private function _display_single_students_set_table_data($students) {
        $data = array(); // contains what $table->data will receive
        
        foreach($students as $student)  {

            /**
             * if the student is not part of any groups, we can tell that he/she
             * is a lonely student.
             */
            if(block_group::is_student_part_of_atleast_one_group($student->id) == false) {

                $data[] = array(format_text($student->firstname, FORMAT_PLAIN),
                                format_text($student->lastname, FORMAT_PLAIN),
                                format_text($student->email, FORMAT_PLAIN));
            }
        }
        return $data;
    }
}

?>
