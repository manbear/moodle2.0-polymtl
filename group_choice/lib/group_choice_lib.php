<?php
require_once('block_meta.php');

/**
 * Description of group_choice_lib
 *
 * @author hrabia
 */
class group_choice_lib {
    /**
     *
     */
    public function  __construct() {
        $this->action   = optional_param('action', 'base', PARAM_ALPHA);
        $this->mode     = optional_param('mode', 'show', PARAM_ALPHAEXT);
        $this->id       = optional_param('id', false, PARAM_INT);
        $this->instance = required_param('instance');
    }

    /**
     * Method to list the preferences
     *
     * Those preferences can change the look and feel of the block.
     * The admin can choose to display the groups or not in the block's content.
     * He can also choose to set the maximum of student per groups and many more
     * features.
     */
    public function show() {
        
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
        global $DB, $CFG, $PAGE, $OUTPUT, $SITE;

        $course     =   block_meta::get_course_as_object($this->id);

        // Strings
        $actionstr  = get_string('action'.$this->action, 'block_group_choice');
        $titlestr   = get_string('settings_template', 'block_group_choice');

        // Header
        $PAGE->set_title($titlestr);
        $PAGE->set_heading($titlestr);
        $redirect_url   = $CFG->wwwroot.'/course/view.php?id='.$this->id;

        $PAGE->navbar->add($SITE->shortname, new moodle_url($CFG->wwwroot));
        $PAGE->navbar->add($course->shortname,
                           new moodle_url($redirect_url));

        $PAGE->navbar->add($actionstr);

        echo $OUTPUT->header();

        include(dirname(dirname(__FILE__)).'/tabs.php');

        echo $OUTPUT->heading($actionstr, 1);
    }
}
?>
