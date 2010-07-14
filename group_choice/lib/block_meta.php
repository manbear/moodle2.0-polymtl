<?php
/**
 * This class is supposed to facilitate the
 * management of the block.
 *
 * @author hrabia
 */
class block_meta {

    /**
     * Return an object of the block instance
     *
     * @global <type> $DB
     * @param <type> $block_instance_id
     * @param <type> $course_id
     * @return <type>
     */
    static public function get_block_instance($block_instance_id, $course_id) {
        global $DB;

        // return an object of the block (empty if false?)
        $block_instance = $DB->get_record('block_group_choice', array('instance_id' => $block_instance_id, 'course_id' => $course_id));

        if ($block_instance) { // if the block has been found in the $DB
            return $block_instance;

        } else { // if block instance has not been found.
            return false;
        }
    }

    /**
     * This method updates the preferences of the block's instance
     * 
     * @global <type> $DB
     * @param <type> $update_fields
     * @return <type>
     */
    static public function update_preferences($update_fields) {
        global $DB;

        /**
         * try to update the $DB,
         * return the result of the operation.
         */
        $DB->update_record('block_group_choice', $update_fields);
        return 1; // savedparams hardcodé
    }

    /**
     * This method returns the number of maximum members
     * allowed by groups for one course.
     *
     * @global <type> $DB
     * @param <type> $course_id
     * @return <type>
     */
    static public function get_max_members_allowed_by_groups($block_instance_id, $course_id) {
        global $DB;
        $block_instance = self::get_block_instance($block_instance_id, $course_id);

        if ($block_instance) { // if a row has been returned
            return $block_instance->maxmembers; // return the max members allowed in the group.

        } else { // if no row has been returned
            return false;
        }
    }

    /**
     * Get the block's course as an object
     *
     * @global <type> $DB
     * @param <type> $course_id
     * @return <type>
     */
    static public function get_course_as_object($course_id) {
        global $DB;

        $course = $DB->get_record('course', array('id' => $course_id));

        if ($course) { // if the course has been found
            return $course;

        } else { // the course hasn't been found in the $DB
            return false;
        }
    }
}
?>
