<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Moodle TH Course status management page.
 *
 * Used to actually publish / unpublish a course when called with valid querystring paraments
 * (including valid session id). Adapted from core Moodle course functionality.
 *
 * @package block_th_course_status
 * @copyright 2018 Manoj Solanki (Coventry University)
 * @copyright
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once '../../config.php';
require_once 'externallib.php';
// require_once $CFG->dirroot . '/course/lib.php';
// require_once $CFG->libdir . '/datalib.php';

// global $USER, $DB, $OUTPUT;
// // Security checks.
// require_login(); // Check user is logged in of course.

// // Get params.
// $courseid = 2;

// // Check capabilities for further safety.
// $context = context_course::instance($courseid);
// $PAGE->set_context($context);
// $pageurl = '/blocks/th_course_status/view.php';
// $PAGE->set_url('/blocks/th_course_status/view.php');
// $PAGE->set_pagelayout('standard');
// $PAGE->set_heading(get_string('heading', 'block_th_course_status'));
// $PAGE->set_title($SITE->fullname . ': ' . get_string('heading', 'block_th_course_status'));

// if (!has_all_capabilities(array('moodle/course:visibility', 'moodle/course:viewhiddencourses'), $context)) {
// 	return print_error('nopermission', 'th_course_status', $redirecturl);
// }

// // function execute() {
// // 	global $CFG, $DB;
// // 	require_once "{$CFG->dirroot}/course/lib.php";
// // 	$a = "xin chao the gioi";

// // 	mtrace("My task started 3");
// // 	$sql = "SELECT *
// //                 from {block_th_course_status}
// //                 group by course
// //                 order by timecreated desc";

// // 	$records = $DB->get_records_sql($sql);
// // 	foreach ($records as $record) {
// // 		$courseid = $record->course;
// // 		$ishidden = $record->ishidden;
// // 		$timecreated = $record->timecreated;

// // 		$course = get_course($courseid);
// // 		$startdate = $course->startdate;

// // 		if ($ishidden == false && time() > $startdate) {
// // 			print_object($courseid);
// // 			\core_course\management\helper::action_course_show_by_record((int) $courseid);
// // 		}
// // 	}
// // 	mtrace("My task finished 3"); //
// // }

// // execute();

// function get_queued_adhoc_task_record($task) {
// 	global $DB;

// 	$record = \core\task\manager::record_from_adhoc_task($task);
// 	$params = [$record->classname, $record->component, $record->customdata];
// 	$sql = 'classname = ? AND component = ? AND ' .
// 	$DB->sql_compare_text('customdata', \core_text::strlen($record->customdata) + 1) . ' = ?';

// 	if ($record->userid) {
// 		$params[] = $record->userid;
// 		$sql .= " AND userid = ? ";
// 	}
// 	return $DB->get_record_select('task_adhoc', $sql, $params);
// }

// echo $OUTPUT->header();

// // $task = new \block_th_course_status\task\change_course_status_adhoc_task();
// // $task->set_next_run_time(time() + 60);
// // \core\task\manager::reschedule_or_queue_adhoc_task($task);
// // \core\task\manager::queue_adhoc_task($task);

// // print_object($task);

// if ($existingrecord = get_queued_adhoc_task_record($task)) {
// 	print_object($existingrecord->id);
// } else {
// 	print_object("no id");
// }

// echo $OUTPUT->footer();
// $a = local_th_check_status_external::check_course_status(116, 177);
// print_object($a);
// exit;

