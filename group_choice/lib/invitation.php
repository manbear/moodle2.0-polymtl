<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of invitation
 *
 * This class is a Facade to the invitation concept
 *
 * @author hrabia
 */
class invitation {

    /**
     * This method is called when a student wants to join
     * a group. The invitation is sent to the teamleader.
     * 
     * @param <type> $student_id
     * @param <type> $group_to_join_id
     * @param <type> $course_id
     */
    static public function send_invite_to_group_teamleader($student_id, $group_to_join_id, $course_id, $teamleader_id) {
        $invitation = self::_create_invitation( $student_id, $group_to_join_id, $course_id );
        self::_notice_user($invitation, $teamleader_id);
    }

    /**
     * This method is called when a teamleader wants to
     * invite another student (not within the group) to join
     * his group.
     * 
     * @param <type> $student_id
     * @param <type> $group_to_join_id
     * @param <type> $course_id
     */
    static  public function send_invite_to_student($student_id, $group_to_join_id, $course_id) {
        $invitation = self::_create_invitation( $student_id, $group_to_join_id, $course_id, 'teamleader' );
        self::_notice_user($invitation, $student_id);
    }

    /**
     * This method save the invitation to the $DB
     * and send an email to the user to let him/her know
     * there is an invitation pending.
     *
     * @param <type> $invitation
     * @param <type> $user_id
     */
    private function _notice_user($invitation, $user_id) {
        /**
         * Try to save the invitation in the $DB
         * if it passes we send an email to the user.
         */
        if (self::_send_invitation_to_DB( $invitation )) {
            $result = self::_send_email_to_user($user_id);

            if ($result == 'true') { // the email has been sent correctly
                return true;

            } else { // error while sending the email.
                return $result; // it should return "emailstop"
            }

        } else { // fail $DB, return false as an error
            return false;
        }
    }

    /**
     * This method creates an invitation to a user.
     *
     * @param <type> $user_id
     * @param <type> $group_to_join
     * @param <type> $course_id
     * @param <type> $action_from
     * @return <type> 
     */
    private function _create_invitation($user_id, $group_to_join, $course_id, $action_from='student') {
        $user_to_invite = new object();
        $user_to_invite->user_id = $user_id;
        $user_to_invite->group_id = $group_to_join;
        $user_to_invite->course_id = $course_id;
        $user_to_invite->status = 'pending';
        $user_to_invite->teamleader = 0; // TODO retirer ce champs de la BD.
        $user_to_invite->action_from = $action_from;

        return $user_to_invite;
    }

    /**
     * Send the invitation to the DB (as a record).
     *
     * @param <type> $user_to_invite
     * @return <type>
     */
    private function _send_invitation_to_DB($user_to_invite) {
        global $DB;
        
        if($DB->insert_record('block_group_choice_invite', $user_to_invite, false))  {
            return true;
        } else {
            return false; // retourner l'output d'erreur à afficher.
        }
    }

    /**
     * Send an Email to a student or a teamleader to let them know
     * that they have an invitation in pending
     *
     * @global <type> $DB
     * @param <type> $user_id
     */
    private function _send_email_to_user($user_id) {
        global $DB;

        $user_obj               = $DB->get_record('user', array('id' => $user_id) );
        $user_obj->enrolement   = 'nologin';
        $from                   = 'noreply@moodle.polymtl.ca';
        $subject                = get_string('invitetogroupsubject', 'block_group_choice');
        $messagetext            = get_string('invitetogroupmessage', 'block_group_choice');

        return email_to_user($user_obj, $from, $subject, $messagetext);// string "true" if successful, "emailstop" if error
    }
}
?>
