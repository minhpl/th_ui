<?php

defined('MOODLE_INTERNAL') || die;
global $COURSE;
$context = context_course::instance($COURSE->id);

if (has_capability('block/th_clone_course:managepages', $context)) {

	$parent = $ADMIN->locate('courses');
	if ($parent) {
		$parent->add('courses', new admin_externalpage('th_clone_course', get_string('pluginname', 'block_th_clone_course'), "$CFG->wwwroot/blocks/th_clone_course/view.php", 'block/th_clone_course:managepages'));
	}
}

?>