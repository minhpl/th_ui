<?php

$capabilities = array(

	'block/th_bulk_enrol_groups:myaddinstance' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
			'teacher' => CAP_ALLOW,
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),

		'overridepermissionsfrom' => 'moodle/my:manageblocks',
	),

	'block/th_bulk_enrol_groups:addinstance' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'write',
		'contextlevel' => CONTEXT_BLOCK,
		'archetypes' => array(
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),

		'overridepermissionsfrom' => 'moodle/site:manageblocks',
	),

	'block/th_bulk_enrol_groups:view' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSE,
		'archetypes' => array(
			'teacher' => CAP_ALLOW,
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),
	),

	'block/th_bulk_enrol_groups:managepages' => array(
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
