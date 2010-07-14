<?php
require_once(dirname(__FILE__).'/../../config.php');
global $DB;

$courseid    = required_param('courseid', PARAM_INT);
$groupid     = required_param('groupid' , PARAM_INT);
$studentid   = required_param('studentid'  , PARAM_INT);
$return_link    = @$httpreferer;
if($DB->delete_records('block_group_choice_invite', array('user_id' => $studentid, 'group_id' => $groupid, 'course_id' => $courseid)))    {
    redirect($return_link);
}

?>
