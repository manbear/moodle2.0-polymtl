<?php
require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/blocks/group_choice/functions.php');

class settings_group_choice_studentformation_form extends moodleform {

    private $settingshtml;


    public function __construct($url) {
        parent::__construct($url);
    }

    public function setHtmlData($settingshtml = false)  {
            $this->settingshtml = $settingshtml;
    }

    /**
     *
     *  This method displays the form with the list of the currently available
     *  students that can join a group. This list depends on the grouping of the
     *  teamleader's group.
     *
     * @return      void
     *
     */
    function definition () {
        global $CFG, $PAGE, $SESSION, $DB, $OUTPUT, $USER;

        $id             = required_param('id');
        $instance       = required_param('instance');

        $groups                         =  groups_get_all_groups($id);
        $get_members_max                = $DB->get_record('block_group_choice', array('course_id' => $id));
        $maxmembers_per_group           = $get_members_max->maxmembers;
        $context    = get_context_instance(CONTEXT_BLOCK, $instance);
        $capability = 'block/group_choice:student_teams';
        $students   = get_users_by_capability($context, $capability);
        // Student choice form
        $mformer = & $this->_form;
        $mformer->addElement('header', 'general', get_string('lonestudents', 'block_group_choice'));
        $groups_in       = groups_get_user_groups($id, $USER->id);

        if($students)   {
            $mformer->addElement('html', '<table class="generaltable boxaligncenter" style="text-align:center">');
                $mformer->addElement('html', '<thead>');
                    $mformer->addElement('html','<tr><th>'.get_string('userpic').'</th>');
                    $mformer->addElement('html','<th>'.get_string('name').'</th>');
                    $mformer->addElement('html','<th>'.get_string('email').'</th>');
                    $mformer->addElement('html','<th>'.get_string('addtogroup', 'block_group_choice').'</th></tr>');
                $mformer->addElement('html', '</thead>');

            $mformer->addElement('html', '<tbody>');

            foreach($students as $student)  {
                if($student->id !== $USER->id /*&& $this->_get_lone_student_in_grouping($groups_in[0], $student->id)*/) {
                    $mformer->addElement('html', '<tr>');
                    $mformer->addElement('html', '<td>'.$OUTPUT->user_picture($student, array('courseid'=>$id)).'</td>');
                    $mformer->addElement('html', '<td>'.strtoupper($student->firstname).', '.$student->lastname.'</td>');
                    $mformer->addElement('html', '<td>'.$student->email.'</td>');
                    $mformer->addElement('html', '<td>');
                        $mformer->addElement('checkbox', 'student_id_'.$student->id);
                    $mformer->addElement('html', '</td></tr>');
                }
            }
            
            $mformer->addElement('html', '</tbody>');
            $mformer->addElement('html', '</table>');
            
            $mformer->addElement('html', '<label for="choosegroup">'.get_string('choosegroup', 'block_group_choice').'</label>');
            $radio_array    = array();
            foreach($groups_in as $your_groups)  {
                for($i = 0; $i < count($your_groups); $i++)   {
                    $teamleader = get_team_leader($your_groups[$i]);
                    if($teamleader == $USER->id)    {
                        $mygroup    = isset($mygroup) ? $mygroup : $i;
                        $group_name     = $DB->get_record('groups', array('id' => $your_groups[$i]));
                        if($group_name) {
                            $group_name     = $group_name->name;
                        }
                        $radio_array[]  = &MoodleQuickForm::createElement('radio', 'mygroup', '', $group_name, $your_groups[$i]);
                    }
                }
            }
            $mformer->addGroup($radio_array, 'radioar', '', array('<br/>'), false);
            $mformer->setDefault('mygroup', $your_groups[$mygroup]);
            

            $mformer->addElement('submit', 'addtogroup', get_string('addtogroup', 'block_group_choice'));
        }
    }

    /**
     *
     * This method is used to verify that a certain student is not part of the
     * teamleader's grouping. That way we can fetch a list of students not part
     * of any team depending of a grouping in a course.
     * 
     * @return bool     FALSE   If the student is part of the grouping
     *                    TRUE    If the student is not part of the grouping
     */
    private function _get_lone_student_in_grouping($groupid, $studentid)  {
        global $CFG, $DB, $OUTPUT, $USER;

        $groupid    = intval($groupid);

        if (groups_group_exists($groupid)) {

            $groupingid = $DB->get_record('groupings_groups', array('groupid' => $groupid));
            $members    = groups_get_grouping_members($groupingid->groupingid);

            foreach($members as $member)    {
                if($member->id == $studentid)   {
                    // the student is part of the same grouping as the teamleader
                    return false;
                }   else    {
                    // the student is not part of the same grouping as the teamleader
                    return true;
                }
            }
        }
    }

}
?>
