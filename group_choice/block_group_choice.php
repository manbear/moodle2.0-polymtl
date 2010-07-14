<?php
/**
 *  @author Ricky Marcelin
 *  @author Éric Comte Marois
 */
class block_group_choice extends block_base {
    
    // Makes sure that we don't insert the block's instance preferences more than once in the DB
    private $_found;

    function init() {
        $this->title        = get_string('group_choice','block_group_choice');
        $this->version      = 2010060101;
        $this->_found       = false;
    }

    function get_content()  {
        global $CFG, $COURSE, $PAGE, $USER, $DB;
        // cache the course's id
        $courseid           = $COURSE->id;        
        if($this->content !== NULL)  {
            return $this->content;
        }
        $this->content              = new object();
        // get the groups from this specific course
        $groups_in_course           = $DB->get_records('groups', array('courseid' => $courseid));
        // count the number of groups in this course
        $num_groups                 = count($groups_in_course);
        // get the preferences from the DB for this block instance
        $preferences                = $DB->get_record('block_group_choice', array('course_id' => $courseid, 'instance_id' => $this->instance->id));
        // get the maximum members that a group may have
        $maxmembers_per_groups      = $preferences->maxmembers;
        // checks if the preferences allow the groups to be displayed
        $display_groups             = $preferences->showgroups;

        if($display_groups) { // makes sure that the teachers want the groups to be displayed
            if($num_groups > 0) { // makes sure that there's atleast one group in that course
                /**
                 *  for each groups in the course we display the group's name, the amount of members
                 *  and the maximum of members allowed per groups.
                 */
                foreach($groups_in_course as $group_in_course)  { 
                    $num_students_in_group       = count($DB->get_records('groups_members', array('groupid' => $group_in_course->id)));
                    $this->content->text        .= $group_in_course->name;
                    $this->content->text        .= ' ('. $num_students_in_group . '/'. $maxmembers_per_groups .')';
                    $this->content->text        .= '<br/>';
                }
            }
            else { // if there's no groups, we display a message
                $this->content->text        .= get_string('nogroupsfound', 'block_group_choice');
            }
        }
        else    { // if the preferences disallow groups to be displayed
            $this->content->text        .= get_string('nogroupsdisplayed', 'block_group_choice');
        }
        /**
         *  Link to the configuration page for this block instance. Where 3 tabs can be found
         *      Preferences: The block's configuration
         *      Groups List: The list of groups and students without a group
         *      Export     : The exportation options of the group for a specific course
         */
        $context    = get_context_instance(CONTEXT_BLOCK, $this->instance->id);
        if(has_capability('block/group_choice:admin_view', $context)){ //if this user is the admin/teacher
        $this->content->footer      = '<a title="'.get_string('configexport', 'block_group_choice').'"
                                      href="'.$CFG->wwwroot.'/blocks/group_choice/index.php?action=configure&id='.$courseid.'&instance='.$this->instance->id.'">'
                                      .get_string('configexport', 'block_group_choice').'</a>';
        }
        else{ // this is a student or a guest
            $this->content->footer      = '<a title="'.get_string('configexport', 'block_group_choice').'"
                                      href="'.$CFG->wwwroot.'/blocks/group_choice/index.php?action=summary&id='.$courseid.'&instance='.$this->instance->id.'">'
                                      .get_string('configexport', 'block_group_choice').'</a>';
        }
        return $this->content;
    }
    
    function specialization()   {
        global $COURSE, $SESSION;
        /**
         * put in the session variable the course's id and the block's instance,
         * so that they can be available in other pages. It is a must for the DB request system
         */
        $SESSION->id            = $COURSE->id;
        $SESSION->instance      = $this->instance->id;
        $this->_after_install();
    }
    
    /*
     * This method is used to set the fields of this block in the database
     * the other operations will only update that field. Since Moodle throws an
     * exception when the indexes are the same in the table. We have verify that
     * the instance id of the block wasn't already in the DB.
     *
     * @name        _after_install()
     * @param       none
     * @return      none
     */
    private function _after_install()    {
        global $COURSE, $DB;
       
        if(!$this->_found)  {   // if the block's instance hasn't been cached
            // lookup the DB to check if this block instance already exists
            $result                        = $DB->get_record('block_group_choice', array('course_id' => $COURSE->id, 'instance_id' => $this->instance->id));
            if($result)    { // if the block's instance already exist in the DB
                $this->_found = true;
            }
            else { // if this block instance doesn't exist we insert a first set of preferences
                $entries                        = new stdClass();
                $entries->instance_id           = $this->instance->id;
                $entries->course_id             = $COURSE->id;
                $entries->showgroups            = 1;
                $entries->maxmembers            = 2;
                $entries->allowchangegroups     = 0;
                $entries->allowstudentteams     = 1;
                $entries->allowmultipleteams    = 0;
                $DB->insert_record('block_group_choice', $entries);
            }
        }
    }

    /*
     * This method is overriden from the parent block_base.
     * When the instance of the block is deleted, we drop the
     * column of this block instance from the DB.
     *
     * @name        instance_delete()
     * @param       none
     * @return      TRUE
     */
    function instance_delete()  {
        global $COURSE, $DB;

        // we seek the DB to find if the block's instance exists
        $result                        = $DB->get_record('block_group_choice', array('course_id' => $COURSE->id, 'instance_id' => $this->instance->id));
        if($result)    { // if the block's instance has been found ...
            // ... the block's instance row is deleted from the DB table 
            $DB->delete_records_list('block_group_choice','id', array($result->id));
        }
        return true;
    }
}
?>
