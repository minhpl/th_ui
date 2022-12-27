<?php

$functions = array(
	'local_th_course_status_check_status' => array(
		'classname' => 'local_th_check_status_external',
		'methodname' => 'check_course_status',
		'classpath' => 'blocks/th_course_status/externallib.php',
		'description' => 'check course status',
		'type' => 'write',
		'loginrequired' => true,
		'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
	),

	'local_th_course_status_published_course' => array(
		'classname' => 'local_th_check_status_external',
		'methodname' => 'th_published_course',
		'classpath' => 'blocks/th_course_status/externallib.php',
		'description' => 'published course',
		'type' => 'write',
		'loginrequired' => true,
		'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
	),

	'local_th_course_status_unpublished_course' => array(
		'classname' => 'local_th_check_status_external',
		'methodname' => 'th_unpublished_course',
		'classpath' => 'blocks/th_course_status/externallib.php',
		'description' => 'unpublished course',
		'type' => 'write',
		'loginrequired' => true,
		'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
	),
);
