<?php

    defined('MOODLE_INTERNAL') || die;
    global $COURSE;
	$context = context_course::instance($COURSE->id);

    if (has_capability('block/th_bulk_enrol_groups:managepages', $context)) {

        $parent = $ADMIN->locate('courses');
        if ($parent) {
            $parent->add('courses', new admin_externalpage('th_bulk_enrol_groups', get_string('pluginname', 'block_th_bulk_enrol_groups'), "$CFG->wwwroot/blocks/th_bulk_enrol_groups/view.php", 'block/th_bulk_enrol_groups:managepages'));
        }
    }
            
?>