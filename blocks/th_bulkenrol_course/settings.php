<?php

use \block_th_bulkenrol_course\libs;

$libs = new libs();
$list_role = $libs->get_list_role();
$settings->add(
	new admin_setting_configselect(
		'block_th_bulkenrol_course/role1',
		get_string('role1', 'block_th_bulkenrol_course'),
		get_string('role_description', 'block_th_bulkenrol_course'),
		'',
		$list_role)
);

$settings->add(
	new admin_setting_configselect(
		'block_th_bulkenrol_course/role2',
		get_string('role2', 'block_th_bulkenrol_course'),
		get_string('role_description', 'block_th_bulkenrol_course'),
		'',
		$list_role)
);

$settings->add(
	new admin_setting_configselect(
		'block_th_bulkenrol_course/role3',
		get_string('role3', 'block_th_bulkenrol_course'),
		get_string('role_description', 'block_th_bulkenrol_course'),
		'',
		$list_role)
);

$settings->add(
	new admin_setting_configselect(
		'block_th_bulkenrol_course/role4',
		get_string('role4', 'block_th_bulkenrol_course'),
		get_string('role_description', 'block_th_bulkenrol_course'),
		'',
		$list_role)
);
unset($list_role);