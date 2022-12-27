<?php

defined('MOODLE_INTERNAL') || die();

$functions = array(
	'block_th_activatecourses_load_registeredcourses_by_userid' => array(
		'classname' => 'block_th_activatecourses_external',
		'methodname' => 'load_registeredcourses_by_userid',
		'classpath' => 'blocks/th_activatecourses/classes/external.php',
		'description' => 'fetch registered courses by userid in the specified format',
		'type' => 'write',
		'ajax' => true,
		'loginrequired' => true,
	),

	'block_th_activatecourses_get_users_courses3' => array(
		'classname' => 'block_th_activatecourses_external',
		'methodname' => 'get_users_courses3',
		'classpath' => 'blocks/th_activatecourses/classes/external.php',
		'description' => 'Get the list of courses where a user is enrolled in',
		'type' => 'read',
		'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
	),

	'block_th_activatecourses_activate_course' => array(
		'classname' => 'block_th_activatecourses_external',
		'methodname' => 'activate_course',
		'classpath' => 'blocks/th_activatecourses/classes/external.php',
		'description' => 'Get the list of courses where a user is enrolled in',
		'type' => 'read',
		'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
	),
);
