<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = array(

	'local/th_dashboard:viewthdashboard' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
			'manager' => CAP_ALLOW,
		),

		'clonepermissionsfrom' => 'moodle/my:manageblocks',
	),
);