<?php

require_once '../../config.php';
require_once 'blocklib.php';
require_once 'confirm_form.php';
require_once $CFG->dirroot . '/enrol/manual/locallib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';
require_once 'th_bulk_enrol_student_form.php';
include 'classes/PHPExcel/IOFactory.php';
include 'classes/PHPExcel.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $_FILES;

$th_bulkenrol_csvkey = optional_param('key', 0, PARAM_ALPHANUMEXT);

// Check for all required variables.
$courseid = $COURSE->id;

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_bulk_enrol_student', $courseid);
}

require_login($courseid);
require_capability('block/th_bulk_enrol_student:view', context_course::instance($COURSE->id));
$context = context_system::instance();
$pageurl = '/blocks/th_bulk_enrol_student/view2.php';
$title = get_string('enrolcoursetitle2', 'block_th_bulk_enrol_student');
$PAGE->set_url('/blocks/th_bulk_enrol_student/view2.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_bulk_enrol_student', 'block_th_bulk_enrol_student'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_bulk_enrol_student'));

$editurl = new moodle_url('/blocks/th_bulk_enrol_student/view2.php');
$settingsnode = $PAGE->settingsnav->add(get_string('breadcrumb', 'block_th_bulk_enrol_student'), $editurl);
$settingsnode->make_active();

if (!$enrol_manual = enrol_get_plugin('manual')) {
	throw new coding_exception('Can not instantiate enrol_manual');
}

if (empty($th_bulkenrol_csvkey)) {

	$th_bulk_enrol_student = new th_bulk_enrol_student_form();

	if ($th_bulk_enrol_student->is_cancelled()) {
		$courseurl = new moodle_url('/my');
		redirect($courseurl);
	} else if ($fromform = $th_bulk_enrol_student->get_data()) {
		$content = $th_bulk_enrol_student->get_file_content('list_students');
		$contents = th_bulkenrol_csv_parse_emails($content);
		$emails = th_bulkenrol_csv_get_content($contents);

		$checked_student = th_bulkenrol_csv_check_user_mails($emails);

		// Save data in Session.
		$th_bulkenrol_csvkey = $courseid . '_' . time();
		$SESSION->block_th_enrol_students[$th_bulkenrol_csvkey] = $checked_student;

	} else {
		// form didn't validate or this is the first display
		echo $OUTPUT->header();
		$baseurl = new moodle_url('/blocks/th_bulk_enrol_student/view2.php');
		if ($editcontrols = local_th_register_course_controls($context, $baseurl)) {
			echo $OUTPUT->render($editcontrols);
		}
		echo $OUTPUT->heading("<center>$title</center>");
		$th_bulk_enrol_student->display();
		echo $OUTPUT->footer();
	}
}

if ($th_bulkenrol_csvkey) {
	$form2 = new confirm_form2(null, array('th_bulkenrol_csvkey' => $th_bulkenrol_csvkey));

	if ($form2->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/blocks/th_bulk_enrol_student/view.php');
		redirect($courseurl);
	} else if ($formdata = $form2->get_data()) {
		if (!empty($th_bulkenrol_csvkey) && !empty($SESSION->block_th_enrol_students) &&
			array_key_exists($th_bulkenrol_csvkey, $SESSION->block_th_enrol_students)) {

			set_time_limit(600);
			$data = $SESSION->block_th_enrol_students[$th_bulkenrol_csvkey];
			$no_enroleds = $data->user_enroled;

			foreach ($no_enroleds as $k => $no_enroled) {
				$courseid = $no_enroled->courseid;
				$userid = $no_enroled->id;
				$roleid = $no_enroled->roleid;
				$instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
				$timestart = time();
				$timeend = 0;
				$enrol_manual->enrol_user($instance, $userid, $roleid, $timestart, $timeend);
			}

			$link = "<a href='view2.php'>tiếp tục ghi danh</a>";
			$link1 = $CFG->wwwroot . '/my';
			$home = "<a href='$link1'>trang chủ</a>";
			$wn = 'Ghi danh thành công bạn có muốn ' . $link . ' hoặc quay lại ' . $home;

			$notification = new \core\output\notification($wn
				,
				\core\output\notification::NOTIFY_WARNING);

			$notification->set_show_closebutton(false);

			echo $OUTPUT->header();
			echo $OUTPUT->heading(get_string('pluginname', 'block_th_bulk_enrol_student'));
			echo $OUTPUT->render($notification);
			echo $OUTPUT->footer();
		}
	} else {
		echo $OUTPUT->header();
		echo $OUTPUT->heading("<center>$title</center>");

		if (
			!empty($th_bulkenrol_csvkey) && !empty($SESSION->block_th_enrol_students) &&
			array_key_exists($th_bulkenrol_csvkey, $SESSION->block_th_enrol_students)
		) {
			$data = $SESSION->block_th_enrol_students[$th_bulkenrol_csvkey];

			if (!empty($data->error_messages)) {

				$errors = $data->error_messages;

				$html1 = th_display_table_error($errors);
				echo $OUTPUT->heading(get_string('Hints', 'block_th_bulk_enrol_student'));
				echo $html1;
			}
			if (!empty($data->user_enroled) || !empty($data->moodleusers_for_email) || !empty($data->error_messages)) {

				$enroleds = $data->moodleusers_for_email;
				$no_enroleds = $data->user_enroled;
				$error_arrays = $data->error_arrays;

				$html = th_display_table_enrol($enroleds, $no_enroleds, $error_arrays);
				echo '<h2 class = "title"><center>DANH SÁCH GHI DANH HỌC VIÊN</center></h2>';
				echo $html;
				$lang = current_language();
				echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
				$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_enrol_table', 'BÁO CÁO GHI DANH THEO LÔ HỌC VIÊN VÀO KHÓA HỌC', $lang));
			}
		}

		if (
			!empty($data) && isset($data->validemailfound) &&
			empty($data->validemailfound)
		) {
			$a = new stdClass();
			$url = new moodle_url('/blocks/th_bulk_enrol_student/view2.php');
			$a->url = $url->out();
			$wn = get_string('error_no_valid_email_in_list', 'block_th_bulk_enrol_student', $a);
			$notification = new \core\output\notification(
				$wn,
				\core\output\notification::NOTIFY_WARNING
			);
			$notification->set_show_closebutton(false);
			echo $OUTPUT->render($notification);
		} else {
			echo $form2->display();
		}
		echo $OUTPUT->footer();
	}
}
