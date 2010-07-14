<?php
/**
 * Settings templates block main controller
 *
 * @package blocks/group_choice
 * @author  Ricky Marcelin
 * @author  Éric Comte Marois
 */

require_once(dirname(__FILE__).'/../../config.php');
global $CFG, $SESSION;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

// Parameters that are used by moodle to access the block's configuration page
$action         = optional_param('action', 'configure', PARAM_ALPHA);
$mode           = optional_param('mode', 'show', PARAM_ALPHAEXT);
$id             = optional_param('id', false, PARAM_INT); // course's id
$instance       = optional_param('instance', false, PARAM_INT); // the block's instance
// Check access
require_login($id);

// Loads the required action class and form
$classname = 'settings_group_choice_'.$action.'_class';
$formname = str_replace($action.'_class', $action.'_form', $classname);
require_once($CFG->dirroot.'/blocks/group_choice/functions.php');
require_once($CFG->dirroot.'/blocks/group_choice/lib/'.$classname.'.php');
if ($action != 'base') {
    require_once($CFG->dirroot.'/blocks/group_choice/forms/'.$formname.'.php');
}

if (!class_exists($classname)) {
    print_error('falseaction', 'block_group_choice', $action);
}

$PAGE->set_url('/blocks/group_choice/index.php', array('action'=>$action, 'mode'=>$mode, 'id'=>$id, 'instance' => $instance));
$baseurl = $PAGE->url->out();

// Executes the required action
$instance = new $classname();
if (!method_exists($instance, $mode)) {
    print_error('falsemode', 'block_group_choice', $mode);
}
// Executes the required method and displays output
$instance->$mode();
$instance->display();
?>
