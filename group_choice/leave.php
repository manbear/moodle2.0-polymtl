<?php
require_once(dirname(__FILE__).'/../../config.php');
global $DB;

$groupid     = required_param('groupid' , PARAM_INT);
$studentid   = required_param('studentid'  , PARAM_INT);
$return_link    = @$httpreferer;
if($DB->delete_records('groups_members', array('userid' => $studentid, 'groupid' => $groupid)))    {
    $group  = new object();
    $group->id              = $groupid;
    $group->timemodified    = time();
    if($DB->update_record('groups', $group)) {
        redirect($return_link);
    }
}

?>