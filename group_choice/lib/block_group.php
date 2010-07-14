<?php

/**
 * This class does the operations that are tied with
 * the concept of group.
 *
 * @author hrabia
 */
class block_group {

    private $_group_id;      // id of the group in the $DB
    private $_teamleader;    // cache the teamleader if possible

    /**
     * The constructor set the group's id
     * and the teamleader of the group
     *
     * @param <type> $group_id
     */
    private function  __construct($group_id) {
        $this->_group_id = $group_id;
        $this->_set_teamleader($group_id);
    }

    /**
     * This method act as an getInstance for a singleton,
     * but it returns a <block_group> wrapper around the concept
     * of groups.
     *
     * @param <type> $group_id
     */
    static public function get_group_instance($group_id) {
        global $DB;

        $group = $DB->get_record('groups', array('id' => $group_id));

        if ($group) { // if an object has been returned by the DB.
            return new block_group($group_id);

        } else { // return false as en error
            return false;
        }
    }
    
    /**
     * returns all the groups that is mapped with the
     * course's id.
     *
     *
     * @param <type> $course_id
     * @return <type>
     */
    static public function get_all_groups_from_course($course_id) {
            return groups_get_all_groups($course_id);
    }

    /**
     * returns all the groups in which the student is a member.
     *
     * @param <type> $course_id
     * @param <type> $student_id
     */
    static public function get_all_groups_concerned_by_student($course_id, $student_id) {
            return groups_get_all_groups($course_id, $student_id);
    }

    /**
     *
     * @global <type> $DB
     * @param <type> $student_id
     * @return <type> 
     */
    static public function is_student_part_of_atleast_one_group($student_id) {
        global $DB;

        $result = $DB->get_record('groups_members', array('userid' => $student_id));

        if ($result) { // if student is mapped with at least a group
            return true;

        } else { // student is not mapped to any group
            return false;
        }
    }

    /**
     * Sometimes it is useful to know if the student is a teamleader of any
     * groups for a certain course.
     */
    static public function is_student_a_teamleader($course_id, $student_id) {
        $groups = self::get_all_groups_concerned_by_student($course_id, $student_id);
        $results = self::get_all_groups_where_student_is_a_teamleader($groups, $student_id);

        if (empty($results)) { // if there is no group where the student is teamleader
            return false;

        } else { // if there is groups where the student is a teamleader.
            return true;
        }

    }

    /**
     * An easy way to know the id of the teamleader of a certain group.
     *
     * @param <type> $group_id
     * @return <type>
     */
    static public function get_group_teamleader_id($group_id) {
        $group_instance = self::get_group_instance($group_id);

        if ($group_instance != false) { // the group exists
            return $group_instance->_teamleader;
        }
    }

    /**
     * Return whether the group has a teamleader (true)
     * or of the group doesn't have a teamleader (false)
     *
     * @return <type> 
     */
    public function has_teamleader() {
        if (isset($this->_teamleader)) {
            return true;

        } else {
            return false;
        }
    }

    /**
     * Calls the moodle group_add_member
     *
     * @param <type> $student_id
     * @param <type> $group_id
     */
    public function add_student_to_group($student_id) {
        groups_add_member($this->_group_id, $student_id);

        echo 'has not failed';
        if ($this->has_teamleader() == false) {
            $this->_set_teamleader($this->_group_id);
        }
    }

    /**
     * Set the teamleader of the group
     */
    private function _set_teamleader($group_id) {
        $teamleader = $this->get_next_teamleader($group_id);

        if ($teamleader != false) { // if we got a teamleader
            $this->_teamleader = $teamleader;
        }
    }

    /**
     * This method return all the members of the group.
     *
     * @param <type> $group_id
     * @return <type>
     */
    static public function get_all_students_from_group($group_id) {
        return groups_get_members($group_id);
    }

    /**
     * This method is used to know who is the next teamleader
     * (or the first teamleader).
     */
    static public function get_next_teamleader($group_id) {
        global $DB;

        /**
         *  get the team leader from the the groups members table
         *  we fetch the table with the group id and check the member with
         *  the lowest timestamp. If many users share the same timestamp the first one
         *  is picked
         */
        $group_members      = $DB->get_records('groups_members', array('groupid' => $group_id));

        $min                = 999999999999999999; // hardcoded, to change the magic number!

        if (empty($group_members) == false ) {// if the group has members.
            $teamleader         = false; // initialize the variable
            
            foreach($group_members as $group_member)    {
                /**
                 * if the time the user joined the group is
                 * less than the actual min timestamp
                 */
                if($group_member->timeadded < $min) {
                    $min          = $group_member->timeadded;
                    $teamleader   = $group_member->userid;
                }
            }
            return $teamleader;

        } else { // if there is no group members in the group
            return false;
        }
    }


    /**
     * Compare a student with a group to know if that
     * student is already inside the group.
     *
     * the reason of this function is that the name
     * 'is_student_in_group' talks more than 'groups_is_member'
     *
     * @param <type> $student_id
     * @param <type> $group_id
     */
    static public function is_student_in_group($student_id, $group_id) {
        return groups_is_member($group_id, $student_id);
    }

    /**
     * It returns an array containing all the id of the group in which 
     * the student is a teamleader.
     *
     * @param <type> $groups
     * @param <type> $student_id
     */
    static public function get_all_groups_where_student_is_a_teamleader($groups, $student_id) {
        $groups_id = array(); // will contain the ids of the group in which the student is a teamleader

        foreach($groups as $group) {
            $group_instance = self::get_group_instance($group->id); // instanciate group
            
            if ($student_id == $group_instance->_teamleader) {
                $groups_id[] = $group_instance->_group_id;
            }
        }
        return $groups_id;
    }
}
?>
