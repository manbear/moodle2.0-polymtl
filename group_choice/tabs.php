<?php
require_once(dirname(__FILE__).'/../../config.php');
global $SESSION, $CFG, $PAGE, $USER, $DB;

$id             = $SESSION->id; // course's id
$instance       = $SESSION->instance; // block's instance
$preferences    = $DB->get_record('block_group_choice', array('course_id' => $id, 'instance_id' => $instance));

$context    = get_context_instance(CONTEXT_BLOCK, $instance);
if(has_capability('block/group_choice:admin_view', $context)){
    // Tabs that appear in the configuration page of the block
    $row[] = new tabobject('configure', $CFG->wwwroot.'/blocks/group_choice/index.php?action=configure'.'&id='.$id.'&instance='.$instance,
                            get_string('configure','block_group_choice'));
    $row[] = new tabobject('list', $CFG->wwwroot.'/blocks/group_choice/index.php?action=list'.'&id='.$id.'&instance='.$instance,
                            get_string('groupslist','block_group_choice'));
    $row[] = new tabobject('export', $CFG->wwwroot.'/blocks/group_choice/index.php?action=export'.'&id='.$id.'&instance='.$instance,
                            get_string('export','block_group_choice'));
}
else    {
    // Tabs that appear in the configuration page of the block
    $row[] = new tabobject('summary', $CFG->wwwroot.'/blocks/group_choice/index.php?action=summary'.'&id='.$id.'&instance='.$instance,
                            get_string('summary','block_group_choice'));
    $row[] = new tabobject('formation', $CFG->wwwroot.'/blocks/group_choice/index.php?action=formation'.'&id='.$id.'&instance='.$instance,
                            get_string('formation','block_group_choice'));
   if($preferences->allowstudentteams)  {
       $row[] = new tabobject('studentformation', $CFG->wwwroot.'/blocks/group_choice/index.php?action=studentformation'.'&id='.$id.'&instance='.$instance,
                            get_string('studentformation','block_group_choice'));
   }
}
print_tabs(array($row), $this->action);
?>
