<?php

require_once $CFG->libdir . '/gradelib.php';
require_once $CFG->dirroot . '/user/renderer.php';
require_once $CFG->dirroot . '/grade/lib.php';
require_once $CFG->dirroot . '/grade/report/grader/lib.php';

function get_scheduled_task_bycourseid($course_id) {
	global $DB;
	$customdata = json_encode(array('courseid' => (int) ($course_id)));
	$sql_where = 'classname = ? AND ' .
	$DB->sql_compare_text('customdata', \core_text::strlen($customdata) + 1) . ' = ?';

	return $DB->get_records_select('task_adhoc', $sql_where, ['\block_th_course_status\task\change_course_status_adhoc_task',
		$customdata]);
}

function delete_scheduled_task_bycourseid($course_id) {
	global $DB;
	$customdata = json_encode(array('courseid' => (int) ($course_id)));
	$sql_where = 'classname = ? AND ' .
	$DB->sql_compare_text('customdata', \core_text::strlen($customdata) + 1) . ' = ?';
	// $DB->set_debug(true);
	$DB->delete_records_select('task_adhoc', $sql_where, ['\block_th_course_status\task\change_course_status_adhoc_task',
		$customdata]);
	// $DB->set_debug(false);
}