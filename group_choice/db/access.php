<?php
//
// Capability definitions for the group_choice block.
//
// The capabilities are loaded into the database table when the block is
// installed or updated. Whenever the capability definitions are updated,
// the module version number should be bumped up.
//
// The system has four possible values for a capability:
// CAP_ALLOW, CAP_PREVENT, CAP_PROHIBIT, and inherit (not set).
//
//
// CAPABILITY NAMING CONVENTION
//
// It is important that capability names are unique. The naming convention
// for capabilities that are specific to modules and blocks is as follows:
//   [mod/block]/<plugin_name>:<capabilityname>
//
// component_name should be the same as the directory name of the mod or block.
//
// Core moodle capabilities are defined thus:
//    moodle/<capabilityclass>:<capabilityname>
//
// Examples: mod/forum:viewpost
//           block/recent_activity:view
//           moodle/site:deleteuser
//
// The variable name for the capability definitions array is $capabilities

$block_group_choice_capabilities = array(
    'block/group_choice:admin_view' => array(
        'captype'               => 'write',
        'contextlevel'          => CONTEXT_BLOCK,
        'legacy'                => array(
            'teacher'           => CAP_ALLOW,
            'editingteacher'    => CAP_ALLOW,
            'manager'           => CAP_ALLOW,
            'student'           => CAP_PREVENT,
            'guest'             => CAP_PREVENT,
            'admin'             => CAP_ALLOW
        )
    ),
    'block/group_choice:student_view' => array(
        'captype'               => 'write',
        'contextlevel'          => CONTEXT_BLOCK,
        'legacy'                => array(
            'teacher'           => CAP_ALLOW,
            'editingteacher'    => CAP_ALLOW,
            'manager'           => CAP_ALLOW,
            'student'           => CAP_ALLOW,
            'guest'             => CAP_ALLOW,
            'admin'             => CAP_ALLOW
        )
    ),
    'block/group_choice:student_teams'    => array(
        'captype'               => 'write',
        'contextlevel'          => CONTEXT_BLOCK,
        'legacy'                => array(
            'student'           => CAP_ALLOW,
            'guest'             => CAP_ALLOW
        )
    )
);
?>
