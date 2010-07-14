<?php
require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.'/group/lib.php');
global $DB;

$courseid    = required_param('courseid', PARAM_INT);
$groupid     = required_param('groupid' , PARAM_INT);
$studentid   = required_param('studentid'  , PARAM_INT);
$return_link    = @$httpreferer;
if(groups_add_member($groupid, $studentid)) {
   if($DB->delete_records('block_group_choice_invite', array('user_id' => $studentid, 'group_id' => $groupid, 'course_id' => $courseid))) {
        $group  = new object();
        $group->id              = $groupid;
        $group->timemodified    = time();
        if($DB->update_record('groups', $group)) {
            redirect($return_link);
        }
   }
}

?>
