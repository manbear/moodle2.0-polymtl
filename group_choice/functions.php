<?php
# This function page is used for functions that are used on many pages
# of this block
define('DAYS', 86400);
define('HOURS', 3600);
define('MINUTES', 60);

/*
 * This function gets the teamleader from a group. A student is by default a
 * teamleader if he/she was the first one added to the group (timestamp comparaison)
 */
function get_team_leader($groupid) {
    global $DB;

    /**
     *  get the team leader from the the groups members table
     *  we fetch the table with the group id and check the member with
     *  the lowest timestamp. If many users share the same timestamp the first one
     *  is picked
     */
    $group_members      = $DB->get_records('groups_members', array('groupid' => $groupid));
    $min                = 999999999999999999;
    foreach($group_members as $group_member)    {
        if($group_member->timeadded < $min) {
            $min          = $group_member->timeadded;
            $teamleader   = $group_member->userid;
        }
    }

    return $teamleader;
}
/*
 * This function evalutes the time left for students to choose their teams
 */
function time_left($block_instance, $courseid) {
    global $DB;
    
    $block_info = $DB->get_record('block_group_choice', array('course_id' => $courseid, 'instance_id' => $block_instance));
    if(!$block_info) {
        return;
    }
    $timelimit  = $block_info->timelimit;
    if(time() >= $timelimit)  {
        return 'timeup';
    }
        
    $now            = time();
    $time_left      = $timelimit - $now;
    $days_left      = intval($time_left/DAYS);
    $remaining_time = $time_left % DAYS;

    $hours_left     = intval($remaining_time / HOURS);
    $remaining_time = $time_left % HOURS;

    $minutes_left   = intval($remaining_time / MINUTES);
    if($minutes_left < 10)
        $minutes_left   = '0'.$minutes_left;
    $secs_left      = $remaining_time % MINUTES;

    $time_str       = '<div style="border: 2px solid #0000FF; background-color:#F0F0FF; text-align:center; margin-bottom: 5px;
                                                font-family: Verdana, Arial, sans-serif; font-weight: normal;">';
    $time_str      .= get_string('timeleft', 'block_group_choice');
    $time_str      .= '<strong>'.$days_left.' '.(($days_left < 2) ? get_string('day') : get_string('days'))
                                .' '.$hours_left.':'.$minutes_left.' '.get_string('hours').'</strong>';
    $time_str      .= '</div>';
    return $time_str;
}

/*
 * This function is used to ouput an anchor the must classes in this block use
 */
function anchor($action,$method, array $params)    {
    if(is_array($params))   {
        for($i = 0; $i < count($params); $i++)  {
            $key    = key($params);
            $value  = $params[$key];
        }
    }
}
?>
