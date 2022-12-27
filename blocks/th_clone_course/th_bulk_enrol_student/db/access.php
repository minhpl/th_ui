<?php
$capabilities = array(

	'block/th_bulk_enrol_student:myaddinstance' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
			'teacher' => CAP_ALLOW,
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),

		'clonepermissionsfrom' => 'moodle/my:manageblocks',
	),

	'block/th_bulk_enrol_student:addinstance' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'write',
		'contextlevel' => CONTEXT_BLOCK,
		'archetypes' => array(
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),

		'clonepermissionsfrom' => 'moodle/site:manageblocks',
	),

	'block/th_bulk_enrol_student:view' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSE,
		'archetypes' => array(
			'teacher' => CAP_ALLOW,
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),
	),

	'block/th_bulk_enrol_student:managepages' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'legacy' => array(
            'guest' => CAP_PREVENT,
            'student' => CAP_PREVENT,
            'teacher' => CAP_PREVENT,
            'editingteacher' => CAP_ALLOW,
            'coursecreator' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    )
);