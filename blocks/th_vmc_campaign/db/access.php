<?php
$capabilities = array(

	'block/th_vmc_campaign:myaddinstance' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
			'manager' => CAP_ALLOW,
		),

		'clonepermissionsfrom' => 'moodle/my:manageblocks',
	),

	'block/th_vmc_campaign:addinstance' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,

		'captype' => 'write',
		'contextlevel' => CONTEXT_BLOCK,
		'archetypes' => array(
			'manager' => CAP_ALLOW,
		),

		'clonepermissionsfrom' => 'moodle/site:manageblocks',
	),
	'block/th_vmc_campaign:view' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'write',
		'contextlevel' => CONTEXT_BLOCK,
		'archetypes' => array(
			'manager' => CAP_ALLOW,
		),
	),
);