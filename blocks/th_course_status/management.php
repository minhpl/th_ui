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
require_once $CFG->dirroot . '/course/lib.php';
require_once 'lib.php';

global $USER, $DB, $COURSE;
// Security checks.
require_login(); // Check user is logged in of course.
require_sesskey(); // Check for valid session key.

// Get params.
$action = required_param('action', PARAM_ALPHA);
$courseid = required_param('courseid', PARAM_INT);
$redirecturl = required_param('redirect', PARAM_TEXT);

// Check capabilities for further safety.
$context = context_course::instance($courseid);
$PAGE->set_context($context);
if (!has_all_capabilities(array('moodle/course:visibility', 'moodle/course:viewhiddencourses'), $context)) {
	return print_error('nopermission', 'th_course_status', $redirecturl);
}

// Prepare an outcome object. We always use this.
// $outcome = new stdClass;
// $outcome->error = false;
// $outcome->outcome = false;

$bcstt = new stdClass();
$bcstt->teachingid = $USER->id;
$bcstt->course = $courseid;
$bcstt->timecreated = time();
$bcstt->ishidden = $action == 'hidecourse' ? true : false;

// Note. Running either of these methods seem to handle errors fairly well so the return value in
// $outcome->outcome isn't checked afterwards.

$course = $DB->get_record('course', array('id' => $courseid), 'id,startdate,visible');
$startdate = $course->startdate;

switch ($action) {
case 'hidecourse':

	$course_visible = $course->visible;
	if ($course_visible == 0) {
		$id = $DB->insert_record('block_th_course_status', $bcstt, $returnid = true);
		delete_scheduled_task_bycourseid($courseid);
	} else {
		//course_visible ==true;
		if ($startdate > time()) {
			$id = $DB->insert_record('block_th_course_status', $bcstt, $returnid = true);
			\core_course\management\helper::action_course_hide_by_record($courseid);
			delete_scheduled_task_bycourseid($courseid);
		}
	}

	break;
case 'showcourse':
	$id = $DB->insert_record('block_th_course_status', $bcstt, $returnid = true);

	if (time() >= $startdate) {
		\core_course\management\helper::action_course_show_by_record($courseid);
		delete_scheduled_task_bycourseid($courseid);
	} else {
		$task = new \block_th_course_status\task\change_course_status_adhoc_task();
		$task->set_next_run_time($startdate);
		$task->set_custom_data(array('courseid' => $courseid));
		\core\task\manager::reschedule_or_queue_adhoc_task($task);
	}

	break;
}

$url = new moodle_url($redirecturl, array());
/*if ($action == 'showcourse')*/{
	redirect($url);
}
