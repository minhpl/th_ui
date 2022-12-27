<?php

use \block_th_course_enrolment_report\libs;
require_once '../../config.php';
require_once 'th_course_enrolment_report_form.php';
require_once $CFG->dirroot . '/enrol/manual/locallib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';
require_once "{$CFG->libdir}/completionlib.php";

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

// Check for all required variables.
$courseid = $COURSE->id;

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_course_enrolment_report', $courseid);
}

require_login($courseid);
require_capability('block/th_course_enrolment_report:view', context_course::instance($COURSE->id));

$pageurl = '/blocks/th_course_enrolment_report/view.php';
$title = get_string('report_title', 'block_th_course_enrolment_report');
$PAGE->set_url('/blocks/th_course_enrolment_report/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_course_enrolment_report', 'block_th_course_enrolment_report'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_course_enrolment_report'));
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->js_call_amd('local_thlib/main', 'addAsteriskToCustomRequiredFieldForm', array($CFG->wwwroot));

$editurl = new moodle_url('/blocks/th_course_enrolment_report/view.php');
$settingsnode = $PAGE->settingsnav->add(get_string('breadcrumb', 'block_th_course_enrolment_report'), $editurl);
$settingsnode->make_active();

if (!$enrol_manual = enrol_get_plugin('manual')) {
	throw new coding_exception('Can not instantiate enrol_manual');
}

$th_course_enrolment_report = new th_course_enrolment_report_form();

if ($th_course_enrolment_report->is_cancelled()) {
	// Cancelled forms redirect to the course main page.
	$courseurl = new moodle_url('/my');
	redirect($courseurl);
} else if ($fromform = $th_course_enrolment_report->get_data()) {

	$enddate = $fromform->enddate + (24 * 60 * 60 - 1);
	$startdate = $fromform->startdate;
	$course_id = $fromform->course_id;
	$user_id = $fromform->userid;

	$date_now = time();
	$table = new html_table();
	$table->head = array(get_string('STT', 'block_th_course_enrolment_report'), get_string('User_Full_Name', 'block_th_course_enrolment_report'), get_string('User_Name', 'block_th_course_enrolment_report'), get_string('User_Role', 'block_th_course_enrolment_report'), get_string('Course', 'block_th_course_enrolment_report'), get_string('Enrolment_Date', 'block_th_course_enrolment_report'), get_string('Enrolment_Activation_Date', 'block_th_course_enrolment_report'), get_string('Enrolment_Expire_Date', 'block_th_course_enrolment_report'), get_string('Enrolment_Status', 'block_th_course_enrolment_report'), get_string('Completion_Status', 'block_th_course_enrolment_report'));
	$stt = 0;

	$max1 = count($course_id);
	$max2 = count($user_id);

	$libs = new libs();
	$liststudents = $libs->get_list_id_students();
	$listcourses = $libs->get_list_id_courses();

	foreach ($listcourses as $k => $course) {
		$listcourses_arr[] = $course;
	}

	foreach ($liststudents as $k => $student) {
		$liststudents_arr[] = $student;
	}

	if ($max1 != 0 && $max2 != 0) {
		$status = 0;
		$max1 = count($course_id);
		$max2 = count($user_id);
	} else if ($max1 == 0 && $max2 != 0) {
		$status = 0;
		$course_id = $listcourses_arr;
		$max1 = count($course_id);
		$max2 = count($user_id);
	} else if ($max1 != 0 && $max2 == 0) {
		$status = 0;
		$user_id = $liststudents_arr;
		$max1 = count($course_id);
		$max2 = count($user_id);
	} else {
		$status = 1;
	}

	$course_active_sql = "select *
			from
			  (
			  select {user_enrolments}.id, {enrol}.courseid as ue_courseid, {user_enrolments}.userid as ue_userid, {user_enrolments}.timestart, {user_enrolments}.timeend, {user_enrolments}.enrolid, {user_enrolments}.timecreated as ue_timecreated, u.firstname as ue_firstname, u.lastname as ue_lastname, u.username as ue_username, c.fullname as ue_fullname, {user_enrolments}.status
			  from {enrol}, {user_enrolments}, {user} as u, {course} as c
			  where {enrol}.id = {user_enrolments}.enrolid and {user_enrolments}.userid = u.id and {enrol}.courseid = c.id) as a
			left join (
			  select {th_registeredcourses}.courseid, {th_registeredcourses}.timecreated,
			  {course}.fullname, {user}.firstname, {user}.lastname, {user}.username, {th_registeredcourses}.userid, {th_registeredcourses}.timeactivated
			  from {th_registeredcourses}, {course}, {user}
			  where {th_registeredcourses}.courseid = {course}.id and {th_registeredcourses}.userid = {user}.id
			) as b
			on a.ue_userid = b.userid and a.ue_courseid = b.courseid;";

	$course_no_active_sql = "select rc.*, c.fullname, u.firstname, u.lastname, u.username from {th_registeredcourses} as rc, {course} as c, {user} as u where timeactivated = 0 and rc.courseid = c.id and rc.userid = u.id and rc.timecreated >= $startdate and rc.timecreated <= $enddate";

	$actives = $DB->get_records_sql($course_active_sql);

	$no_actives = $DB->get_records_sql($course_no_active_sql);

	foreach ($actives as $k => $active) {
		$course_actives[] = $active;
	}

	foreach ($no_actives as $k => $no_active) {
		$course_no_actives[] = $no_active;
	}

	if ($status == 1) {

		if (sizeof($actives) > 0) {
			$max = count($course_actives);

			for ($i = 0; $i < $max; ++$i) {
				if ($course_actives[$i]->courseid == null) {
					if ($course_actives[$i]->ue_timecreated >= $startdate && $course_actives[$i]->ue_timecreated <= $enddate) {
						$fullname = $course_actives[$i]->ue_firstname . ' ' . $course_actives[$i]->ue_lastname;
						$username = $course_actives[$i]->ue_username;
						$course_name = $course_actives[$i]->ue_fullname;

						$courseid = $course_actives[$i]->ue_courseid;
						$userid = $course_actives[$i]->ue_userid;

						$course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = $courseid");

						$info = new completion_info($course);
						$coursecomplete = $info->is_course_complete($userid);

						if ($coursecomplete) {
							$Completion_Status = get_string('completion', 'block_th_course_enrolment_report');
						} else {
							$Completion_Status = get_string('no_completion', 'block_th_course_enrolment_report');
						}

						$sql = "SELECT r.roleid FROM {role_assignments} as r, {context} as c
							WHERE r.userid = $userid AND r.contextid = c.id
							AND c.instanceid = $courseid AND c.contextlevel = 50;";
						$roles = $DB->get_records_sql($sql);

						$user_role = '';

						foreach ($roles as $k => $role) {
							$str = $libs->get_role_name($role->roleid);
							$user_role .= ' ' . $str;
						}

						$timecreated = $course_actives[$i]->ue_timecreated;

						if ($course_actives[$i]->timestart == 0) {
							$time_start = 'N/A';
						} else {
							$time_start = $course_actives[$i]->timestart;
						}

						if ($course_actives[$i]->timeend == 0) {
							$time_end = 'N/A';
						} else {
							$time_end = $course_actives[$i]->timeend;
						}

						if ($course_actives[$i]->status == 0 && $date_now <= $time_end) {
							$Enrolment_Status = 'Active';
						} else {
							$Enrolment_Status = 'Expired';
						}

						$stt = $stt + 1;
						$link = new moodle_url('/user/index.php', ['id' => $courseid]);
						$link_course = html_writer::link($link, $course_name);
						$link1 = new moodle_url('/user/profile.php', ['id' => $userid]);
						$link_user = html_writer::link($link1, $fullname);

						$link2 = new moodle_url('/blocks/completionstatus/details.php', ['course' => $courseid, 'user' => $userid]);
						$link_status1 = html_writer::link($link2, $Completion_Status);

						$link3 = new moodle_url('/report/completion/index.php', ['course' => $courseid]);
						$link_status2 = html_writer::link($link3, $Completion_Status);

						$row = new html_table_row();
						$cell = new html_table_cell($stt);
						$row->cells[] = $cell;
						$cell = new html_table_cell($link_user);
						$row->cells[] = $cell;
						$cell = new html_table_cell($username);
						$row->cells[] = $cell;
						$cell = new html_table_cell($user_role);
						$row->cells[] = $cell;
						$cell = new html_table_cell($link_course);
						$row->cells[] = $cell;
						$cell = new html_table_cell(date('d/m/Y', $timecreated));
						$row->cells[] = $cell;

						if ($time_start == 'N/A') {
							$cell = new html_table_cell($time_start);
							$row->cells[] = $cell;
						} else {
							$cell = new html_table_cell(date('d/m/Y', $time_start));
							$row->cells[] = $cell;
						}

						if ($time_end == 'N/A') {
							$cell = new html_table_cell($time_end);
							$row->cells[] = $cell;
						} else {
							$cell = new html_table_cell(date('d/m/Y', $time_end));
							$row->cells[] = $cell;
						}

						$cell = new html_table_cell($Enrolment_Status);
						$row->cells[] = $cell;

						$context = context_course::instance($courseid, MUST_EXIST);
						$userrecord = $DB->get_record('user', array('id' => $userid));
						$userenroled = is_enrolled($context, $userrecord->id);

						if (!empty($userenroled)) {
							$cell = new html_table_cell($link_status1);
							$row->cells[] = $cell;
						} else {
							$cell = new html_table_cell($link_status2);
							$row->cells[] = $cell;
						}

						if ($Completion_Status == 'Chưa hoàn thành' || $Completion_Status == 'Unfinished') {
							$cell->attributes = array('class' => "bg-danger");
						} else {
							$cell->attributes = array('class' => "bg-success");
						}

						$table->data[] = $row;
					}

				} else {
					if ($course_actives[$i]->timecreated >= $startdate && $course_actives[$i]->timecreated <= $enddate) {
						$fullname = $course_actives[$i]->firstname . ' ' . $course_actives[$i]->lastname;
						$username = $course_actives[$i]->username;
						$course_name = $course_actives[$i]->fullname;

						$courseid = $course_actives[$i]->courseid;
						$userid = $course_actives[$i]->userid;

						$course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = $courseid");

						$info = new completion_info($course);
						$coursecomplete = $info->is_course_complete($userid);

						if ($coursecomplete) {
							$Completion_Status = get_string('completion', 'block_th_course_enrolment_report');
						} else {
							$Completion_Status = get_string('no_completion', 'block_th_course_enrolment_report');
						}

						$sql = "SELECT r.roleid FROM {role_assignments} as r, {context} as c
							WHERE r.userid = $userid AND r.contextid = c.id
							AND c.instanceid = $courseid AND c.contextlevel = 50;";
						$roles = $DB->get_records_sql($sql);

						$user_role = '';

						foreach ($roles as $k => $role) {
							$str = $libs->get_role_name($role->roleid);
							$user_role .= ' ' . $str;
						}

						$timecreated = $course_actives[$i]->timecreated;

						if ($course_actives[$i]->timestart == 0) {
							$time_start = 'N/A';
						} else {
							$time_start = $course_actives[$i]->timestart;
						}

						if ($course_actives[$i]->timeend == 0) {
							$time_end = 'N/A';
						} else {
							$time_end = $course_actives[$i]->timeend;
						}

						if ($course_actives[$i]->status == 0 && $date_now <= $time_end) {
							$Enrolment_Status = 'Active';
						} else {
							$Enrolment_Status = 'Expired';
						}

						$stt = $stt + 1;
						$link = new moodle_url('/user/index.php', ['id' => $courseid]);
						$link_course = html_writer::link($link, $course_name);
						$link1 = new moodle_url('/user/profile.php', ['id' => $userid]);
						$link_user = html_writer::link($link1, $fullname);

						$link2 = new moodle_url('/blocks/completionstatus/details.php', ['course' => $courseid, 'user' => $userid]);
						$link_status1 = html_writer::link($link2, $Completion_Status);

						$link3 = new moodle_url('/report/completion/index.php', ['course' => $courseid]);
						$link_status2 = html_writer::link($link3, $Completion_Status);

						$row = new html_table_row();
						$cell = new html_table_cell($stt);
						$row->cells[] = $cell;
						$cell = new html_table_cell($link_user);
						$row->cells[] = $cell;
						$cell = new html_table_cell($username);
						$row->cells[] = $cell;
						$cell = new html_table_cell($user_role);
						$row->cells[] = $cell;
						$cell = new html_table_cell($link_course);
						$row->cells[] = $cell;
						$cell = new html_table_cell(date('d/m/Y', $timecreated));
						$row->cells[] = $cell;

						if ($time_start == 'N/A') {
							$cell = new html_table_cell($time_start);
							$row->cells[] = $cell;
						} else {
							$cell = new html_table_cell(date('d/m/Y', $time_start));
							$row->cells[] = $cell;
						}

						if ($time_end == 'N/A') {
							$cell = new html_table_cell($time_end);
							$row->cells[] = $cell;
						} else {
							$cell = new html_table_cell(date('d/m/Y', $time_end));
							$row->cells[] = $cell;
						}

						$cell = new html_table_cell($Enrolment_Status);
						$row->cells[] = $cell;

						$context = context_course::instance($courseid, MUST_EXIST);
						$userrecord = $DB->get_record('user', array('id' => $userid));
						$userenroled = is_enrolled($context, $userrecord->id);

						if (!empty($userenroled)) {
							$cell = new html_table_cell($link_status1);
							$row->cells[] = $cell;
						} else {
							$cell = new html_table_cell($link_status2);
							$row->cells[] = $cell;
						}

						if ($Completion_Status == 'Chưa hoàn thành' || $Completion_Status == 'Unfinished') {
							$cell->attributes = array('class' => "bg-danger");
						} else {
							$cell->attributes = array('class' => "bg-success");
						}

						$table->data[] = $row;
					}

				}

			}
		}

		if ($DB->record_exists_sql($course_no_active_sql) == 1) {
			$max1 = count($course_no_actives);

			for ($j = 0; $j < $max1; ++$j) {
				$fullname = $course_no_actives[$j]->firstname . ' ' . $course_no_actives[$j]->lastname;
				$username = $course_no_actives[$j]->username;
				$course_name = $course_no_actives[$j]->fullname;

				$courseid = $course_no_actives[$j]->courseid;
				$userid = $course_no_actives[$j]->userid;

				$Completion_Status = get_string('no_completion', 'block_th_course_enrolment_report');

				$user_role = 'student';

				$timecreated = $course_no_actives[$j]->timecreated;

				$time_start = 'N/A';

				$time_end = 'N/A';

				$Enrolment_Status = 'Expired';

				$stt = $stt + 1;
				$link = new moodle_url('/user/index.php', ['id' => $courseid]);
				$link_course = html_writer::link($link, $course_name);
				$link1 = new moodle_url('/user/profile.php', ['id' => $userid]);
				$link_user = html_writer::link($link1, $fullname);

				$link2 = new moodle_url('/blocks/completionstatus/details.php', ['course' => $courseid, 'user' => $userid]);
				$link_status1 = html_writer::link($link2, $Completion_Status);

				$link3 = new moodle_url('/report/completion/index.php', ['course' => $courseid]);
				$link_status2 = html_writer::link($link3, $Completion_Status);

				$row = new html_table_row();
				$cell = new html_table_cell($stt);
				$row->cells[] = $cell;
				$cell = new html_table_cell($link_user);
				$row->cells[] = $cell;
				$cell = new html_table_cell($username);
				$row->cells[] = $cell;
				$cell = new html_table_cell($user_role);
				$row->cells[] = $cell;
				$cell = new html_table_cell($link_course);
				$row->cells[] = $cell;
				$cell = new html_table_cell(date('d/m/Y', $timecreated));
				$row->cells[] = $cell;

				$cell = new html_table_cell($time_start);
				$row->cells[] = $cell;

				$cell = new html_table_cell($time_end);
				$row->cells[] = $cell;

				$cell = new html_table_cell($Enrolment_Status);
				$row->cells[] = $cell;

				$context = context_course::instance($courseid, MUST_EXIST);
				$userrecord = $DB->get_record('user', array('id' => $userid));
				$userenroled = is_enrolled($context, $userrecord->id);

				if (!empty($userenroled)) {
					$cell = new html_table_cell($link_status1);
					$row->cells[] = $cell;
				} else {
					$cell = new html_table_cell($link_status2);
					$row->cells[] = $cell;
				}

				if ($Completion_Status == 'Chưa hoàn thành' || $Completion_Status == 'Unfinished') {
					$cell->attributes = array('class' => "bg-danger");
				} else {
					$cell->attributes = array('class' => "bg-success");
				}

				$table->data[] = $row;
			}
		}

	} else {
		if (sizeof($actives) > 0) {
			$max = count($course_actives);

			for ($k = 0; $k < $max; ++$k) {
				for ($i = 0; $i < $max1; ++$i) {

					for ($j = 0; $j < $max2; ++$j) {

						if ($course_actives[$k]->courseid == null) {
							if ($course_actives[$k]->ue_timecreated >= $startdate && $course_actives[$k]->ue_timecreated <= $enddate) {

								if ($course_actives[$k]->ue_courseid == $course_id[$i] && $course_actives[$k]->ue_userid == $user_id[$j]) {

									$fullname = $course_actives[$k]->ue_firstname . ' ' . $course_actives[$k]->ue_lastname;
									$username = $course_actives[$k]->ue_username;
									$course_name = $course_actives[$k]->ue_fullname;

									$courseid = $course_actives[$k]->ue_courseid;
									$userid = $course_actives[$k]->ue_userid;

									$course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = $courseid");

									$info = new completion_info($course);
									$coursecomplete = $info->is_course_complete($userid);

									if ($coursecomplete) {
										$Completion_Status = get_string('completion', 'block_th_course_enrolment_report');
									} else {
										$Completion_Status = get_string('no_completion', 'block_th_course_enrolment_report');
									}

									$sql = "SELECT r.roleid FROM {role_assignments} as r, {context} as c
										WHERE r.userid = $userid AND r.contextid = c.id
										AND c.instanceid = $courseid AND c.contextlevel = 50;";
									$roles = $DB->get_records_sql($sql);

									$user_role = '';

									foreach ($roles as $key => $role) {
										$str = $libs->get_role_name($role->roleid);
										$user_role .= ' ' . $str;
									}

									$timecreated = $course_actives[$k]->ue_timecreated;

									if ($course_actives[$k]->timestart == 0) {
										$time_start = 'N/A';
									} else {
										$time_start = $course_actives[$k]->timestart;
									}

									if ($course_actives[$k]->timeend == 0) {
										$time_end = 'N/A';
									} else {
										$time_end = $course_actives[$k]->timeend;
									}

									if ($course_actives[$k]->status == 0 && $date_now <= $time_end) {
										$Enrolment_Status = 'Active';
									} else {
										$Enrolment_Status = 'Expired';
									}

									$stt = $stt + 1;
									$link = new moodle_url('/user/index.php', ['id' => $courseid]);
									$link_course = html_writer::link($link, $course_name);
									$link1 = new moodle_url('/user/profile.php', ['id' => $userid]);
									$link_user = html_writer::link($link1, $fullname);

									$link2 = new moodle_url('/blocks/completionstatus/details.php', ['course' => $courseid, 'user' => $userid]);
									$link_status1 = html_writer::link($link2, $Completion_Status);

									$link3 = new moodle_url('/report/completion/index.php', ['course' => $courseid]);
									$link_status2 = html_writer::link($link3, $Completion_Status);

									$row = new html_table_row();
									$cell = new html_table_cell($stt);
									$row->cells[] = $cell;
									$cell = new html_table_cell($link_user);
									$row->cells[] = $cell;
									$cell = new html_table_cell($username);
									$row->cells[] = $cell;
									$cell = new html_table_cell($user_role);
									$row->cells[] = $cell;
									$cell = new html_table_cell($link_course);
									$row->cells[] = $cell;
									$cell = new html_table_cell(date('d/m/Y', $timecreated));
									$row->cells[] = $cell;

									if ($time_start == 'N/A') {
										$cell = new html_table_cell($time_start);
										$row->cells[] = $cell;
									} else {
										$cell = new html_table_cell(date('d/m/Y', $time_start));
										$row->cells[] = $cell;
									}

									if ($time_end == 'N/A') {
										$cell = new html_table_cell($time_end);
										$row->cells[] = $cell;
									} else {
										$cell = new html_table_cell(date('d/m/Y', $time_end));
										$row->cells[] = $cell;
									}

									$cell = new html_table_cell($Enrolment_Status);
									$row->cells[] = $cell;

									$context = context_course::instance($courseid, MUST_EXIST);
									$userrecord = $DB->get_record('user', array('id' => $userid));
									$userenroled = is_enrolled($context, $userrecord->id);

									if (!empty($userenroled)) {
										$cell = new html_table_cell($link_status1);
										$row->cells[] = $cell;
									} else {
										$cell = new html_table_cell($link_status2);
										$row->cells[] = $cell;
									}

									if ($Completion_Status == 'Chưa hoàn thành' || $Completion_Status == 'Unfinished') {
										$cell->attributes = array('class' => "bg-danger");
									} else {
										$cell->attributes = array('class' => "bg-success");
									}

									$table->data[] = $row;

								}

							}

						} else {
							if ($course_actives[$k]->timecreated >= $startdate && $course_actives[$k]->timecreated <= $enddate) {

								if ($course_actives[$k]->courseid == $course_id[$i] && $course_actives[$k]->userid == $user_id[$j]) {

									$fullname = $course_actives[$k]->firstname . ' ' . $course_actives[$k]->lastname;
									$username = $course_actives[$k]->username;
									$course_name = $course_actives[$k]->fullname;

									$courseid = $course_actives[$k]->courseid;
									$userid = $course_actives[$k]->userid;

									$course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = $courseid");

									$info = new completion_info($course);
									$coursecomplete = $info->is_course_complete($userid);

									if ($coursecomplete) {
										$Completion_Status = get_string('completion', 'block_th_course_enrolment_report');
									} else {
										$Completion_Status = get_string('no_completion', 'block_th_course_enrolment_report');
									}

									$sql = "SELECT r.roleid FROM {role_assignments} as r, {context} as c
										WHERE r.userid = $userid AND r.contextid = c.id
										AND c.instanceid = $courseid AND c.contextlevel = 50;";
									$roles = $DB->get_records_sql($sql);

									$user_role = '';

									foreach ($roles as $key => $role) {
										$str = $libs->get_role_name($role->roleid);
										$user_role .= ' ' . $str;
									}

									$timecreated = $course_actives[$k]->timecreated;

									if ($course_actives[$k]->timestart == 0) {
										$time_start = 'N/A';
									} else {
										$time_start = $course_actives[$k]->timestart;
									}

									if ($course_actives[$k]->timeend == 0) {
										$time_end = 'N/A';
									} else {
										$time_end = $course_actives[$k]->timeend;
									}

									if ($course_actives[$k]->status == 0 && $date_now <= $time_end) {
										$Enrolment_Status = 'Active';
									} else {
										$Enrolment_Status = 'Expired';
									}

									$stt = $stt + 1;
									$link = new moodle_url('/user/index.php', ['id' => $courseid]);
									$link_course = html_writer::link($link, $course_name);
									$link1 = new moodle_url('/user/profile.php', ['id' => $userid]);
									$link_user = html_writer::link($link1, $fullname);

									$link2 = new moodle_url('/blocks/completionstatus/details.php', ['course' => $courseid, 'user' => $userid]);
									$link_status1 = html_writer::link($link2, $Completion_Status);

									$link3 = new moodle_url('/report/completion/index.php', ['course' => $courseid]);
									$link_status2 = html_writer::link($link3, $Completion_Status);

									$row = new html_table_row();
									$cell = new html_table_cell($stt);
									$row->cells[] = $cell;
									$cell = new html_table_cell($link_user);
									$row->cells[] = $cell;
									$cell = new html_table_cell($username);
									$row->cells[] = $cell;
									$cell = new html_table_cell($user_role);
									$row->cells[] = $cell;
									$cell = new html_table_cell($link_course);
									$row->cells[] = $cell;
									$cell = new html_table_cell(date('d/m/Y', $timecreated));
									$row->cells[] = $cell;

									if ($time_start == 'N/A') {
										$cell = new html_table_cell($time_start);
										$row->cells[] = $cell;
									} else {
										$cell = new html_table_cell(date('d/m/Y', $time_start));
										$row->cells[] = $cell;
									}

									if ($time_end == 'N/A') {
										$cell = new html_table_cell($time_end);
										$row->cells[] = $cell;
									} else {
										$cell = new html_table_cell(date('d/m/Y', $time_end));
										$row->cells[] = $cell;
									}

									$cell = new html_table_cell($Enrolment_Status);
									$row->cells[] = $cell;

									$context = context_course::instance($courseid, MUST_EXIST);
									$userrecord = $DB->get_record('user', array('id' => $userid));
									$userenroled = is_enrolled($context, $userrecord->id);

									if (!empty($userenroled)) {
										$cell = new html_table_cell($link_status1);
										$row->cells[] = $cell;
									} else {
										$cell = new html_table_cell($link_status2);
										$row->cells[] = $cell;
									}

									if ($Completion_Status == 'Chưa hoàn thành' || $Completion_Status == 'Unfinished') {
										$cell->attributes = array('class' => "bg-danger");
									} else {
										$cell->attributes = array('class' => "bg-success");
									}

									$table->data[] = $row;
								}

							}

						}

					}

				}

			}

		}

		if ($DB->record_exists_sql($course_no_active_sql) == 1) {
			$max = count($course_no_actives);

			for ($z = 0; $z < $max; ++$z) {

				for ($i = 0; $i < $max1; ++$i) {

					for ($j = 0; $j < $max2; ++$j) {

						if ($course_no_actives[$z]->courseid == $course_id[$i] && $course_no_actives[$z]->userid == $user_id[$j]) {

							$fullname = $course_no_actives[$z]->firstname . ' ' . $course_no_actives[$z]->lastname;
							$username = $course_no_actives[$z]->username;
							$course_name = $course_no_actives[$z]->fullname;

							$courseid = $course_no_actives[$z]->courseid;
							$userid = $course_no_actives[$z]->userid;

							$user_role = 'student';

							$timecreated = $course_no_actives[$z]->timecreated;

							$time_start = 'N/A';

							$time_end = 'N/A';

							$Enrolment_Status = 'Expired';

							$stt = $stt + 1;
							$link = new moodle_url('/user/index.php', ['id' => $courseid]);
							$link_course = html_writer::link($link, $course_name);
							$link1 = new moodle_url('/user/profile.php', ['id' => $userid]);
							$link_user = html_writer::link($link1, $fullname);

							$Completion_Status = get_string('no_completion', 'block_th_course_enrolment_report');
							$link2 = new moodle_url('/blocks/completionstatus/details.php', ['course' => $courseid, 'user' => $userid]);
							$link_status1 = html_writer::link($link2, $Completion_Status);

							$link3 = new moodle_url('/report/completion/index.php', ['course' => $courseid]);
							$link_status2 = html_writer::link($link3, $Completion_Status);

							$row = new html_table_row();
							$cell = new html_table_cell($stt);
							$row->cells[] = $cell;
							$cell = new html_table_cell($link_user);
							$row->cells[] = $cell;
							$cell = new html_table_cell($username);
							$row->cells[] = $cell;
							$cell = new html_table_cell($user_role);
							$row->cells[] = $cell;
							$cell = new html_table_cell($link_course);
							$row->cells[] = $cell;
							$cell = new html_table_cell(date('d/m/Y', $timecreated));
							$row->cells[] = $cell;

							$cell = new html_table_cell($time_start);
							$row->cells[] = $cell;

							$cell = new html_table_cell($time_end);
							$row->cells[] = $cell;

							$cell = new html_table_cell($Enrolment_Status);
							$row->cells[] = $cell;

							$context = context_course::instance($courseid, MUST_EXIST);
							$userrecord = $DB->get_record('user', array('id' => $userid));
							$userenroled = is_enrolled($context, $userrecord->id);

							if (empty($userenroled)) {
								$cell = new html_table_cell($link_status1);
								$row->cells[] = $cell;
							} else {
								$cell = new html_table_cell($link_status2);
								$row->cells[] = $cell;
							}

							if ($Completion_Status == 'Chưa hoàn thành' || $Completion_Status == 'Unfinished') {
								$cell->attributes = array('class' => "bg-danger");
							} else {
								$cell->attributes = array('class' => "bg-success");
							}

							$table->data[] = $row;
						}

					}

				}

			}

		}

	}

	echo $OUTPUT->header();
	echo $OUTPUT->heading($title);
	echo "</br>";
	$th_course_enrolment_report->display();
	echo "</br>";

	if ($stt !== 0) {
		$table->attributes = array('class' => 'th-table', 'border' => '1');
		$table->attributes['style'] = "width: 100%; text-align:center;";
		$html = html_writer::table($table);
		echo $OUTPUT->heading(get_string('reportprocess', 'block_th_course_enrolment_report'));
		echo "</br>";
		echo $html;
		echo "</br>";
		$lang = current_language();
		echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
		$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th-table', 'BÁO CÁO GÁN KHÓA HỌC', $lang));
	} else {
		echo $OUTPUT->heading(get_string('errorprocess', 'block_th_course_enrolment_report'));
	}

	echo $OUTPUT->footer();

} else {
	// form didn't validate or this is the first display
	echo $OUTPUT->header();
	echo $OUTPUT->heading($title);
	echo "</br>";
	$th_course_enrolment_report->display();
	echo $OUTPUT->footer();
}
?>

