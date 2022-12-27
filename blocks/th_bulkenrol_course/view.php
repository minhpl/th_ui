<?php

require_once '../../config.php';
require_once 'blocklib.php';
require_once 'th_bulkenrol_course_form.php';
require_once 'confirm_form.php';
require_once $CFG->dirroot . '/enrol/manual/locallib.php';
require_once $CFG->dirroot . '/' . $CFG->admin . '/tool/uploaduser/locallib.php';
require_once $CFG->dirroot . '/' . $CFG->admin . '/tool/uploaduser/user_form.php';

global $DB, $OUTPUT, $PAGE, $COURSE;

$localth_bulkenrol_csvkey = optional_param('key', 0, PARAM_ALPHANUMEXT);

// Check for all required variables.
$courseid = $COURSE->id;

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_bulkenrol_course', $courseid);
}

require_login($courseid);
require_capability('block/th_bulkenrol_course:view', context_course::instance($COURSE->id));

$pageurl = '/blocks/th_bulkenrol_course/view.php';
$title = get_string('enrolcoursetitle', 'block_th_bulkenrol_course');
$PAGE->set_url('/blocks/th_bulkenrol_course/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_bulkenrol_course', 'block_th_bulkenrol_course'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_bulkenrol_course'));

$editurl = new moodle_url('/blocks/th_bulkenrol_course/view.php');
$settingsnode = $PAGE->settingsnav->add(get_string('breadcrumb', 'block_th_bulkenrol_course'), $editurl);
$settingsnode->make_active();

if (!$enrol_manual = enrol_get_plugin('manual')) {
	throw new coding_exception('Can not instantiate enrol_manual');
}

if (empty($localth_bulkenrol_csvkey)) {
	$th_bulkenrol_course = new th_bulkenrol_course_form();

	if ($th_bulkenrol_course->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/my');
		redirect($courseurl);
	} else if ($fromform = $th_bulkenrol_course->get_data()) {

		$content = $th_bulkenrol_course->get_file_content('usermails');
		$contents = th_bulkenrol_csv_parse_emails($content);

		$emails = th_get_content($contents);

		$checkedmails = th_bulkenrol_csv_check_user_mails($emails);

		// Create local_th_bulkenrol_csv array in Session.
		if (!isset($SESSION->local_th_bulkenrol_csv)) {
			$SESSION->local_th_bulkenrol_csv = array();
		}

		// Save data in Session.
		$localth_bulkenrol_csvkey = $courseid . '_' . time();
		$SESSION->local_th_bulkenrol_csv[$localth_bulkenrol_csvkey] = $checkedmails;

		// Create local_th_bulkenrol_csv_inputs array in session.
		if (!isset($SESSION->local_th_bulkenrol_csv_inputs)) {
			$SESSION->local_th_bulkenrol_csv_inputs = array();
		}
		$localth_bulkenrol_csvdata = $localth_bulkenrol_csvkey . '_data';
		$SESSION->local_th_bulkenrol_csv_inputs[$localth_bulkenrol_csvdata] = $fromform;

		if (!isset($SESSION->local_th_bulkenrol_csv_options)) {
			$SESSION->local_th_bulkenrol_csv_options = array();
		}

	} else {
		// form didn't validate or this is the first display
		echo $OUTPUT->header();
		echo $OUTPUT->heading($title);
		echo "</br>";
		$th_bulkenrol_course->display();
		echo $OUTPUT->footer();
	}
}

if ($localth_bulkenrol_csvkey) {
	$form2 = new confirm_form(null, array('local_th_bulkenrol_csv_key' => $localth_bulkenrol_csvkey));

	if ($form2->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/blocks/th_bulkenrol_course/view.php');
		redirect($courseurl);
	} else if ($formdata = $form2->get_data()) {
		if (!empty($localth_bulkenrol_csvkey) && !empty($SESSION->local_th_bulkenrol_csv) &&
			array_key_exists($localth_bulkenrol_csvkey, $SESSION->local_th_bulkenrol_csv)) {
			set_time_limit(600);
			$data = $SESSION->local_th_bulkenrol_csv[$localth_bulkenrol_csvkey];

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

			$link = "<a href='view.php'>tiếp tục ghi danh</a>";
			$link1 = $CFG->wwwroot . '/my';
			$home = "<a href='$link1'>trang chủ</a>";
			$wn = 'Ghi danh thành công bạn có muốn ' . $link . ' hoặc quay lại ' . $home;

			$notification = new \core\output\notification($wn
				,
				\core\output\notification::NOTIFY_WARNING);

			$notification->set_show_closebutton(false);

			echo $OUTPUT->header();
			echo $OUTPUT->heading(get_string('pluginname', 'block_th_bulkenrol_course'));
			echo $OUTPUT->render($notification);
			echo $OUTPUT->footer();
		}
	} else {

		echo $OUTPUT->header();
		echo $OUTPUT->heading(get_string('pluginname', 'block_th_bulkenrol_course'));
		if (!empty($localth_bulkenrol_csvkey) && !empty($SESSION->local_th_bulkenrol_csv) &&
			array_key_exists($localth_bulkenrol_csvkey, $SESSION->local_th_bulkenrol_csv)) {

			$localth_bulkenrol_csvdata = $SESSION->local_th_bulkenrol_csv[$localth_bulkenrol_csvkey];

			if (!empty($localth_bulkenrol_csvdata->error_messages)) {

				$errors = $localth_bulkenrol_csvdata->error_messages;

				$html1 = th_display_table_error($errors);
				echo $OUTPUT->heading(get_string('Hints', 'block_th_bulkenrol_course'));
				echo $html1;

			}

			if (!empty($localth_bulkenrol_csvdata->user_enroled) || !empty($localth_bulkenrol_csvdata->moodleusers_for_email)) {

				$enroleds = $localth_bulkenrol_csvdata->moodleusers_for_email;
				$no_enroleds = $localth_bulkenrol_csvdata->user_enroled;

				$html = th_display_table_enrol($enroleds, $no_enroleds);
				echo $OUTPUT->heading(get_string('users_to_enrol_in_course', 'block_th_bulkenrol_course'));
				echo $html;
			}
		}
		// Show notification if there aren't any valid email addresses to enrol.
		if (!empty($localth_bulkenrol_csvdata) && isset($localth_bulkenrol_csvdata->validemailfound) &&
			empty($localth_bulkenrol_csvdata->validemailfound)) {
			$a = new stdClass();
			$url = new moodle_url('/blocks/th_bulkenrol_course/view.php', array('id' => $courseid, 'editlist' => $localth_bulkenrol_csvkey));
			$a->url = $url->out();

			$wn = get_string('error_no_valid_email_in_list', 'block_th_bulkenrol_course', $a);

			$notification = new \core\output\notification($wn
				,
				\core\output\notification::NOTIFY_WARNING);

			$notification->set_show_closebutton(false);
			echo $OUTPUT->render($notification);

			// Otherwise show the enrolment details and the form with the enrol users button.
		} else {
			echo $form2->display();
		}
		echo $OUTPUT->footer();

	}
}
?>

