<?php
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/backup/backuplib.php');
require_once('block_meta.php');

/**
 * @since       Moodle 2.0
 * @package     block/group_choice
 * @author      Ricky Marcelin
 * @author      Éric Comte Marois
 */
class settings_group_choice_export_class {


    protected $action;
    protected $mode;
    protected $id;
    protected $instance;
    protected $adminroot;
    protected $course;
    protected $groups;
    protected $outputs;
    public    $moodleform;


    public function __construct($id = false) {
        global $DB;

        $this->action   = optional_param('action', 'base', PARAM_ALPHA);
        $this->mode     = optional_param('mode', 'show', PARAM_ALPHAEXT);
        $this->id       = required_param('id');
        $this->instance = required_param('instance');
        $this->course   = $DB->get_record('course', array('id' => $this->id));
        $this->groups   = (object)groups_get_all_groups($this->id);
    }

    public function show() {

        global $CFG, $DB, $OUTPUT;

        $this->outputs = '';
        $url = $CFG->wwwroot.'/blocks/group_choice/index.php?action=export&mode=export_groups&id='.$this->id
               .'&instance='.$this->instance;
        $this->moodleform = & new settings_group_choice_export_form($url);
    }

    /**
     * Main display method
     *
     * Prints the block header and the common block outputs, the
     * selected action outputs, his form and the footer
     *
     * $outputs value depends on $mode and $action selected
     */
    public function display() {

        global $CFG, $OUTPUT;

        $this->_display_header();

        // Other outputs
        if (!empty($this->outputs)) {
            echo $this->outputs;
        }

        // Form
        if ($this->moodleform) {
            $this->moodleform->display();
        }

        // Footer
        echo $OUTPUT->footer();
    }


    /**
    * Displays the header
    */
    protected function _display_header() {
        global $CFG, $PAGE, $OUTPUT, $SITE;
        $course     =   block_meta::get_course_as_object($this->id);
        // Strings
        $actionstr = get_string('action'.$this->action, 'block_group_choice');
        $titlestr = get_string('settings_template', 'block_group_choice');

        // Header
        $PAGE->set_title($titlestr);
        $PAGE->set_heading($titlestr);
        //$redirect_url   = $CFG->wwwroot.'/course/view.php?id='.$this->id;

        $PAGE->navbar->add($SITE->shortname, new moodle_url($CFG->wwwroot));
        //$PAGE->navbar->add($course->shortname,
         //                  new moodle_url($redirect_url));

        $PAGE->navbar->add($actionstr);

        echo $OUTPUT->header();

        include(dirname(dirname(__FILE__)).'/tabs.php');

        echo $OUTPUT->heading($actionstr, 1);
    }

    /**
     * This method fetches the groups informations from a specific course.
     * And then calls different methods based on the user's inputs.
     *
     * @param       none
     * @return      none
     */
    public function export_groups()  {
        global $CFG, $PAGE, $OUTPUT, $SITE, $SESSION, $DB;

        $url = $CFG->wwwroot.'/blocks/group_choice/index.php?action=export&mode=export_groups&id='.$this->id
        .'&instance='.$this->instance;
        $this->moodleform = & new settings_group_choice_export_form($url);

        if($data    = $this->moodleform->get_data())    { // if there's data send back from the form POST/GET
            $export_settings                = new object();
            $export_settings->lastname      = (!$data->lastname)        ? 0     : $data->lastname;
            $export_settings->firstname     = (!$data->firstname)       ? 0     : $data->firstname;
            $export_settings->idnumber      = (!$data->idnumber)        ? 0     : $data->idnumber;
            $export_settings->email         = (!$data->email)           ? 0     : $data->email;
            $export_settings->exportformat  = $data->exportformattype;
            $export_settings->sendto        = (!$data->exportemaillist) ? false : $data->exportemaillist;
        }
        // return the groups in the course in a well formed array structure
        $groups_export_array = $this->_get_course_groups($export_settings);

        /*
         *  check if there's email(s) in the input box, if not then a download popup
         *  is displayed to force download the group list in the desired format
         */
        if(!$export_settings->sendto)   {
            $this->_export_groups_exec($groups_export_array, $export_settings->exportformat);
        }

        // we try to find wich delimiter is used by the admin/teacher to send the email
        $delimiter      = strpos($export_settings->sendto, ',');
        if($delimiter === FALSE)    {
            $delimiter      = strpos($export_settings->sendto, ';');
            $delim          = ';';
        }else   {
            $delim          = ',';
        }
        // we separate the emails and we put them in an array to be fetch/verified afterward
        $emails  = explode($delim,$export_settings->sendto);
        $this->_export_groups_email($groups_export_array, $export_settings->exportformat, $emails);
    }

    /**
     * Force the download of the groups list, in the specified format
     * @param array $groups_export_array
     * @param int   $format
     * @return      none
     */
    private function _export_groups_exec($groups_export_array, $format)    {
      $file     = $this->_choose_format_to_export($groups_export_array, $format);
      $filename = key($file);
      send_file($file[$filename], $filename, 0, 0, true, true);
      exit();

    }

    /**
     *
     * @param array $groups_export_array
     * @param int   $format
     * @param array $emails
     * @return      none
     */
    private function _export_groups_email($groups_export_array, $format, $emails)    {
        global $CFG, $SESSION;
        require_once($CFG->libdir.'/filelib.php');
        $SESSION->sent_email    = false;
        foreach($emails as $email)  {
            $email  = trim($email);
            if(!validate_email($email)) {
                redirect($redirect_url, get_string('invalid_email', 'block_group_choice'). $email);
            }
        }
        $from           = 'noreply@moodle.polymtl.ca';
        $subject        = get_string('groupsmailsubject', 'block_group_choice');
        $messagetext    = get_string('groupsmailmessage', 'block_group_choice');
        $messagehtml    = '';
        $file           = $this->_choose_format_to_export($groups_export_array, $format);
        $filename       = key($file);
        $file_body      = $file[$filename];
        // we send an email to each of the emails that was in the input field 'Send to...'
        foreach($emails as $email)  {
            $user               = new object(); // small hack, to make Moodle send an email to an non Moodle user
            $user->email        = $email;
            $user->enrolement   = 'nologin';
            $attachment         = $CFG->dataroot .'/'.$filename;
            $attachmentname     = $filename;
            // we temporarily create a file in the moodledata folder until the email is sent
            $fp                 =   fopen($attachment, 'w');
            fwrite($fp, $file_body);
            fclose($fp);
            if(email_to_user($user, $from, $subject, $messagetext, $messagehtml, $attachmentname, $attachmentname))    {
                // TODO: Add a sent message confirmation
            }
            unlink($attachment); // delete the newly created file
        }
    }

    /**
     * Get the students from each groups and form a new structure that can be
     * easily handled by loops. This serves us to create different type of files
     * with unique formats with not too much hassle.
     * 
     * @param object $data
     * @return array $info_groups_struct    well formed array of the groups
     */
    private function _get_course_groups($data)    {
        $students               = array();
        $info_groups_struct     = array();

        if($this->groups == false) // verify that there's atleast a group in the course
            return false;

        foreach($this->groups as $group)  {
            // we create an array for the group wich will contain students
            $info_groups_struct['group_'.$group->id]    = array();
            $students   = groups_get_members($group->id);

            if($students == false) // verify that there's atleast a student in the group (we don't want empty groups)
                return false;

            // we append each student's informations into the info_groups_struct array
            foreach($students as $student)  {
                $info_groups_struct['group_'.$group->id]['student_'.$student->id]    = array();
                if($data->lastname !== 0)   {
                    $info_groups_struct['group_'.$group->id]['student_'.$student->id]['lastname']   = $student->lastname;
                }
                if($data->firstname !== 0)  {
                    $info_groups_struct['group_'.$group->id]['student_'.$student->id]['firstname']  = $student->firstname;
                }
                if($data->idnumber !== 0)   {
                    $info_groups_struct['group_'.$group->id]['student_'.$student->id]['idnumber']   = $student->idnumber;
                }
                if($data->email    !== 0)   {
                    $info_groups_struct['group_'.$group->id]['student_'.$student->id]['email']      = $student->email;
                }
            }
        }
        return $info_groups_struct;
   }

   private function _export_groups_excel($groups_export_array)  {

   }

   /**
    * Exports the groups list in CSV
    * @param array      $groups_export_array
    * @return array     the filename and the file content
    */
   private function _export_groups_csv($groups_export_array)  {

//       $file_content      .= '"Group","Lastname","Firstname","ID number","Email"'."\n";
       /*
        *  for each groups in the array we get the student informations out of them.
        *  and create a CSV content style, that is ready to be exported.
        */
       foreach($groups_export_array as $key => $group) {
            foreach($group as $student) {
                $file_content .= '"'.$key.'",';
                $student       = (object)$student;
                $file_content .= isset($student->lastname)   ? '"'.$student->lastname.'",' :  '';
                $file_content .= isset($student->firstname)  ? '"'.$student->firstname.'",' :  '';
                $file_content .= isset($student->idnumber)   ? $student->idnumber.',' :  '';
                $file_content .= isset($student->email)      ? '"'.$student->email.'",'."\n" :  ''."\n";
            }
       }
       $filename = $this->course->shortname.'_Groups_'.date('d-m-Y',time()).'.csv';
       return array($filename => $file_content);
   }

   private function _export_groups_osd($groups_export_array)  {

   }

   /**
    * Exports the groups list in XML
    * @param array      $groups_export_array
    * @return array     the filename and the file content
    */
   private function _export_groups_xml($groups_export_array)  {

       $file_content  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
       $file_content .= start_tag('course', 0, true, array('shortname' => $this->course->shortname));

        foreach($groups_export_array as $key => $group) {
            $file_content .= start_tag('group', 1, true, array('name' => $key));

            foreach($group as $student) {
                $file_content .= start_tag('student', 2, false, $student);
                $file_content .= end_tag('student', 2, true); // end student
            }
            $file_content .= end_tag('group', 1, true); // end groups
        }
       $file_content .= end_tag('course', 0, true); // end course
       $filename = $this->course->shortname.'_Groups_'.time().'.xml';
       return array($filename => $file_content);
   }

   /**
    * Creates a file depending on the format selected by the user.
    * @param array      $groups_export_array
    * @param int        $format
    * @return array     containing the file's name and the file's content
    */
   private function _choose_format_to_export($groups_export_array, $format)  {
        if($groups_export_array != false)    {
            switch($format)  {
                case 1: return $this->_export_groups_excel($groups_export_array);
                case 2: return $this->_export_groups_csv($groups_export_array);
                case 3: return $this->_export_groups_osd($groups_export_array);
                case 4: return $this->_export_groups_xml($groups_export_array);
            }
        }
   }
}

?>
