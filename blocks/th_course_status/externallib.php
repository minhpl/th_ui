<?php

require_once "$CFG->libdir/externallib.php";
require_once "lib.php";
require_once $CFG->dirroot . '/course/lib.php';
require_once $CFG->dirroot . "/course/externallib.php";
require_once $CFG->dirroot . '/blocks/th_course_status/lib.php';

class local_th_check_status_external extends external_api {

	public static function check_course_status_parameters() {
		return new external_function_parameters(
			array(
				'userid' => new external_value(PARAM_INT, 'user id'),
				'courseid' => new external_value(PARAM_INT, 'course id'),
			)
		);
	}

	public static function check_course_status_returns() {

		return new external_single_structure(
			array(
				'check' => new external_value(PARAM_BOOL, 'check teacher', VALUE_OPTIONAL),
				'status' => new external_value(PARAM_INT, 'check status course', VALUE_OPTIONAL),
				'number_user' => new external_value(PARAM_INT, 'check number_user', VALUE_OPTIONAL),
				'startdate' => new external_value(PARAM_TEXT, 'startdate', VALUE_OPTIONAL),
			),
			'',
			VALUE_OPTIONAL
		);
	}

	public static function check_course_status($userid, $courseid) {

		global $DB;

		// $role_teacher = $DB->get_record('role', array('shortname' => 'editingteacher'));
		// $context = context_course::instance($courseid);
		// $teachers = get_role_users($role_teacher->id, $context);

		$course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = '$courseid'");

		$roleid = $DB->get_field_sql("SELECT id FROM {role} WHERE shortname = 'student'");
		$count = $DB->get_field_sql("SELECT COUNT(ue.userid) FROM {enrol} as e, {user_enrolments} as ue, {user} as u, {context} as c, {role_assignments} as ra WHERE ue.status = 0 AND e.courseid = '$courseid' AND e.enrol = 'manual' AND e.id = ue.enrolid AND u.id = ue.userid AND c.instanceid = '$courseid' AND c.contextlevel = '50' AND c.id = ra.contextid AND ra.userid = u.id AND ra.roleid = '$roleid'");

		$id_max = $DB->get_field_sql("SELECT MAX(id) FROM {block_th_course_status} WHERE course = '$courseid'");
		if (isset($id_max)){
			$status = $DB->get_field_sql("SELECT ishidden FROM {block_th_course_status} WHERE id = '$id_max'");
		}

		$result = new stdClass();

		if(isset($status)) {
			if($status == 0){
				$result->status = 1;
			} else {
				$result->status = 0;
			}
			
		} else {
			$result->status = $course->visible;
		}
		
		$result->number_user = $count;
		$result->startdate = date("Y/m/d H:i:s", $course->startdate);

		// Check the user has update or visibility setting capability within their role.
		$capabilities = array(
			'moodle/course:update',
			'moodle/course:visibility',
			'moodle/course:viewhiddencourses',
		);
		
		$context = context_course::instance($courseid);
		if (has_any_capability($capabilities, $context, $userid)) {
			$result->check = true;
		} else {
			$result->check = false;
		}

		return $result;

		// $result->check = false;

		// foreach ($teachers as $teacher) {
		// 	if ($userid == $teacher->id) {
		// 		$result->check = true;
		// 		return $result;
		// 	}
		// }
	}


	public static function th_published_course_parameters() {
		return new external_function_parameters(
			array(
				'userid' => new external_value(PARAM_INT, 'user id'),
				'courseid' => new external_value(PARAM_INT, 'course id'),
			)
		);
	}

	public static function th_published_course_returns() {

		return new external_single_structure(
			array(
				'status' => new external_value(PARAM_INT, 'status course', VALUE_OPTIONAL),
			),
			'',
			VALUE_OPTIONAL
		);
	}

	public static function th_published_course($userid, $courseid) {

		global $DB;
		$course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = '$courseid'");

		$data = new stdClass();
		$data->course = $courseid;
		$data->timecreated = time();
		$data->timemodified = time();
		$data->teachingid = $userid;
		$data->ishidden = 0;

		$DB->insert_record('block_th_course_status', $data, false);

		$visible = $course->visible;
		$startdate = $course->startdate;

		if (time() >= $startdate) {
			\core_course\management\helper::action_course_show_by_record($courseid);
			delete_scheduled_task_bycourseid($courseid);
		} else {
			$task = new \block_th_course_status\task\change_course_status_adhoc_task();
			$task->set_next_run_time($startdate);
			$task->set_custom_data(array('courseid' => $courseid));
			\core\task\manager::reschedule_or_queue_adhoc_task($task);
		}

		$result = new stdClass();
		$result->status = 1;

		return $result;
	}

	public static function th_unpublished_course_parameters() {
		return new external_function_parameters(
			array(
				'userid' => new external_value(PARAM_INT, 'user id'),
				'courseid' => new external_value(PARAM_INT, 'course id'),
			)
		);
	}

	public static function th_unpublished_course_returns() {

		return new external_single_structure(
			array(
				'status' => new external_value(PARAM_INT, 'status course', VALUE_OPTIONAL),
			),
			'',
			VALUE_OPTIONAL
		);
	}

	public static function th_unpublished_course($userid, $courseid) {

		global $DB;

		$data = new stdClass();
		$data->course = $courseid;
		$data->timecreated = time();
		$data->timemodified = time();
		$data->teachingid = $userid;
		$data->ishidden = 1;

		$course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = '$courseid'");
		$course_visible = $course->visible;
		$startdate = $course->startdate;
		if ($course_visible == 0) {
			$id = $DB->insert_record('block_th_course_status', $data, $returnid = true);
			delete_scheduled_task_bycourseid($courseid);
		} else {
			if ($startdate > time()) {
				$id = $DB->insert_record('block_th_course_status', $data, $returnid = true);
				\core_course\management\helper::action_course_hide_by_record($courseid);
				delete_scheduled_task_bycourseid($courseid);
			}
		}

		$result = new stdClass();
		$result->status = 0;

		return $result;
	}
}
