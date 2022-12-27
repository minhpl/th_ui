<?php
$capabilities = array(

	'block/th_clone_course:myaddinstance' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
			'teacher' => CAP_ALLOW,
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),

		'clonepermissionsfrom' => 'moodle/my:manageblocks',
	),

	'block/th_clone_course:addinstance' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'write',
		'contextlevel' => CONTEXT_BLOCK,
		'archetypes' => array(
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),

		'clonepermissionsfrom' => 'moodle/site:manageblocks',
	),

	// 'block/th_clone_course:views' => array(
	// 	'captype' => 'write',
	// 	'contextlevel' => CONTEXT_SYSTEM,
	// 	'archetypes' => array(
	// 		'manager' => CAP_ALLOW,
	// 	),
	// ),

	'block/th_clone_course:view' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'write',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
			'manager' => CAP_ALLOW,
		),
	),

	'block/th_clone_course:managepages' => array(
		'captype' => 'read',
		'contextlevel' => CONTEXT_COURSE,
		'legacy' => array(
			'manager' => CAP_ALLOW,
		),
	),
);