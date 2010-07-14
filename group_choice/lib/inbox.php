<?php
require_once('block_group.php');

/**
 * The inbox is where the students and teamleader
 * are able to get the invitation that have been sent to them.
 *
 * @author hrabia
 */
class inbox {

    /**
     *
     * @param <type> $course_id
     * @param <type> $teamleader_id
     */
    static public function get_invitations_to_teamleader_of_group_by_student($course_id, $teamleader_id) {
        global $DB;

        /**
         * we get all the groups of the student, and retrieve the
         * group which the student is the teamleader
         */
        $groups = block_group::get_all_groups_concerned_by_student($course_id, $teamleader_id);

        if ($groups) { // if the student is member of at least one group
            $groups_id = block_group::get_all_groups_where_student_is_a_teamleader($groups, $teamleader_id);
            
            if (empty($groups_id) == false) { // if the student is a teamleader
                $invitations = array();

                /**
                 * foreach groups we get the invitations that have been sent to
                 * the teamleader.
                 */
                foreach($groups_id as $group_id) {
                    $invites = self::_get_invitation_of_group_for_teamleader($course_id, $group_id);

                    if ($invites != false) { // if invitations have been found for this group.
                        $invitations[] = $invites;
                    }

                }

                return $invitations;
            }
            
        } else { // the student is not a member of any group
            return false;
        }
    }

    /**
     *
     * @param <type> $course_id
     * @param <type> $student_id 
     */
    static public function get_invitations_to_student_by_teamleader($course_id, $student_id) {
        global $DB;

        return $DB->get_records('block_group_choice_invite', array('user_id' => $student_id, 'course_id' => $course_id, 'status' => 'pending', 'action_from' => 'teamleader'));
    }

    /**
     *
     * @global <type> $DB
     * @param <type> $course_id
     * @param <type> $group_id
     * @return <type> 
     */
     private function _get_invitation_of_group_for_teamleader($course_id, $group_id) {
        global $DB;

        $invitation = $DB->get_records('block_group_choice_invite', array('course_id' => $course_id, 'group_id' => $group_id, 'status' => 'pending', 'action_from' => 'student'));

        if (!empty($invitation)) { // found invitations in the $DB for this group teamleader
            return $invitation;

        } else { // no invitations found in the $DB
            return false;
        }
    }
}
?>
