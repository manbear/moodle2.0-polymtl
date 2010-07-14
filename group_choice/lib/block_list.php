<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * This class implement the concepts of a list to be outputted
 *
 * @author hrabia
 */
class block_list {

    static public function format_groups_into_wellformed_array($groups) {
        $group_array_structure = array();

        foreach ($groups as $group) {
            $students           = block_group::get_all_students_from_group($group->id);
            $student_name_list  = self::_create_student_list_separated_by_comma($students);

            /**
             * we append the informations to the wellformed array
             * that simulates a table, which each group represents a row.
             */
            $group_array_structure['group_'.$group->id]['group']        = $group->id; // add group to array
            $group_array_structure['group_'.$group->id]['students']     = $student_name_list;
            $group_array_structure['group_'.$group->id]['members']      = count($students);
            $group_array_structure['group_'.$group->id]['modification'] = $group->timemodified;
        }
        return $group_array_structure;
    }

    /**
     *
     * @param <type> $groups
     * @return <type> 
     */
    static public function format_student_groups_into_wellformed_array($groups) {
        $group_array_structure = array();

        foreach ($groups as $group) {
            $students           = block_group::get_all_students_from_group($group->id);
            $student_name_list  = self::_create_student_list_separated_by_comma($students);

            /**
             * we append the informations to the wellformed array
             * that simulates a table, which each group represents a row.
             */
            $group_array_structure['group_'.$group->id]['students']     = $student_name_list;
            $group_array_structure['group_'.$group->id]['members']      = count($students);
        }
        return $group_array_structure;
    }

    /**
     * This method is called when we want to create a string
     * that is a list of student that are separated by commas (,)
     *
     * @param <type> $students
     */
    private function _create_student_list_separated_by_comma($students) {
        $str                    = '';
        $last_student           = false;
        $student_counter        = 0;
        $count_students_in_goup = count($students);

        // if the arrays is not empty.
        if ( empty($students) == false ) {
            
            foreach ($students as $student) {
                $str .= $student->firstname . ' '. $student->lastname;

                // let us know if it's the last student of the array.
                if(++$student_counter   == $count_students_in_goup)
                    $last_student = true;

                if(!$last_student) // if not the last student
                    $str .= ', ';
            }
            return $str; // return the string list of the student.

        } else { // return false as an error (no string list!)
            return false;
        }
    }
}
?>
