<?php
require_once($CFG->dirroot.'/lib/formslib.php');


class settings_group_choice_formation_form extends moodleform {

    private $settingshtml;


    public function __construct($url) {
        parent::__construct($url);
    }

    public function setHtmlData($settingshtml = false)  {
            $this->settingshtml = $settingshtml;
    }


    function definition () {
        global $CFG, $PAGE, $SESSION, $DB, $OUTPUT, $USER;

        $id             = required_param('id');
        $instance       = required_param('instance');

        $groups                         =  groups_get_all_groups($id);
        $get_members_max                = $DB->get_record('block_group_choice', array('course_id' => $id));
        $maxmembers_per_group           = $get_members_max->maxmembers;
        $img_path                       = $CFG->wwwroot.'/blocks/group_choice/images/green.gif';
        $mform = & $this->_form;

        $mform->addElement('header', 'general', get_string('existinggroups', 'block_group_choice'));
        // IF there's some groups in the course
        if($groups)
        {
            $mform->addElement('html', '<table class="generaltable boxaligncenter" style="text-align:center">');
                $mform->addElement('html', '<thead>');
                    $mform->addElement('html','<tr><th>'.get_string('coursegroups', 'block_group_choice').'</th>');
                    $mform->addElement('html','<th>'.get_string('groupmembers', 'block_group_choice').'</th>');
                    $mform->addElement('html','<th>'.get_string('groupsmaxmembers', 'block_group_choice').'</th>');
                    $mform->addElement('html','<th>'.get_string('joingroup', 'block_group_choice').'</th></tr>');
                $mform->addElement('html', '</thead>');
                $mform->addElement('html', '<tbody>');
                foreach ($groups as $group) {
                    $mform->addElement('html', '<tr>');
                    $mform->addElement('html', '<td>Group '.$group->id.'</td>');
                    
                    $last_student               = false;
                    $student_counter            = 0;
                    $str                        = '';
                    $students                   = groups_get_members($group->id);
                    $count_students_in_goups    = count($students);

                    foreach($students as $student)  {
                        $str .= $student->firstname . ' '. $student->lastname;
                        if(++$student_counter   == $count_students_in_goups)
                            $last_student = true;
                        if(!$last_student)
                            $str .= ', ';
                    }
                    $mform->addElement('html', '<td>'.$str.'</td>');
                    $mform->addElement('html', '<td>'.($student_counter).'/'.$maxmembers_per_group.'</td>');
                    $mform->addElement('html', '<td>');
                        if(groups_is_member($group->id, $USER->id))
                            $mform->addElement('html', '<img src="'.$img_path.'" title="You\'re already in that group" />');
                        else
                            $mform->addElement('checkbox', 'checkbox_group_'.$group->id);
                    $mform->addElement('html', '</td></tr>');
                }
                $mform->addElement('html', '</tbody>');
                $mform->addElement('html', '</table>');
                $mform->addElement('submit', 'joingroup', get_string('joingroup', 'block_group_choice'));
          }
    }
}
?>
