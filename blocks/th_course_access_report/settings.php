<?php
$settings->add(new admin_setting_heading(
	'block_th_course_access_report/heading',
	get_string('headerconfig', 'block_th_course_access_report'),
	''
));

$settings->add(new admin_setting_configtext(
	'block_th_course_access_report/date',
	get_string('labelallowhtml', 'block_th_course_access_report'),
	get_string('descallowhtml', 'block_th_course_access_report'),
	100,
	PARAM_INT
));
