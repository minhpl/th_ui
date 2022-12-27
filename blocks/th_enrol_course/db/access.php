<?php
$capabilities = array(

	'block/th_enrol_course:myaddinstance' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
			'teacher' => CAP_ALLOW,
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),

		'clonepermissionsfrom' => 'moodle/my:manageblocks',
	),

	'block/th_enrol_course:addinstance' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'write',
		'contextlevel' => CONTEXT_BLOCK,
		'archetypes' => array(
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW
		),

		'clonepermissionsfrom' => 'moodle/site:manageblocks',
	),

	'block/th_enrol_course:view' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSE,
		'archetypes' => array(
			'manager' => CAP_ALLOW
		),
	),

	'block/th_enrol_course:managepages' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'legacy' => array(
            'manager' => CAP_ALLOW
        )
    )
);