<?php

    defined('MOODLE_INTERNAL') || die;
    global $COURSE;
	$context = context_course::instance($COURSE->id);

    if (has_capability('block/th_error_course:managepages', $context)) {

        $parent = $ADMIN->locate('courses');
        if ($parent) {
            $parent->add('courses', new admin_externalpage('th_error_course', get_string('pluginname', 'block_th_error_course'), "$CFG->wwwroot/blocks/th_error_course/view.php", 'block/th_error_course:managepages'));
        }
    }
            
?>