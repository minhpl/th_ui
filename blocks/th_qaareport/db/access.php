<?php
$capabilities = array(

	'block/th_qaareport:myaddinstance' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
			'teacher' => CAP_ALLOW,
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),

		'clonepermissionsfrom' => 'moodle/my:manageblocks',
	),

	'block/th_qaareport:addinstance' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'write',
		'contextlevel' => CONTEXT_BLOCK,
		'archetypes' => array(
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),

		'clonepermissionsfrom' => 'moodle/site:manageblocks',
	),

	'block/th_qaareport:view' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSE,
		'archetypes' => array(
			'teacher' => CAP_ALLOW,
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),
	),
);