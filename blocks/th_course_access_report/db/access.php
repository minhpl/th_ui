<?php
$capabilities = array(

	'block/th_course_access_report:myaddinstance' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
			'teacher' => CAP_ALLOW,
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),

		'clonepermissionsfrom' => 'moodle/my:manageblocks',
	),

	'block/th_course_access_report:addinstance' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,

		'captype' => 'write',
		'contextlevel' => CONTEXT_BLOCK,
		'archetypes' => array(
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),

		'clonepermissionsfrom' => 'moodle/site:manageblocks',
	),
	'block/th_course_access_report:view' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'write',
		'contextlevel' => CONTEXT_BLOCK,
		'archetypes' => array(
			'teacher' => CAP_ALLOW,
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),
	),

	'block/th_course_access_report:managepages' => array(
		'captype' => 'read',
		'contextlevel' => CONTEXT_COURSE,
		'legacy' => array(
			'guest' => CAP_PREVENT,
			'student' => CAP_PREVENT,
			'teacher' => CAP_PREVENT,
			'editingteacher' => CAP_ALLOW,
			'coursecreator' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),
	),
);
