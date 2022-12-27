<?php
require '../../config.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/blocks/th_manage_activatecourses/classes/lib.php';
require_once $CFG->dirroot . '/blocks/th_manage_activatecourses/th_manage_activatecourses_form.php';

global $DB, $CFG, $COURSE, $PAGE, $OUTPUT, $SESSION;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', 'th_manage_activatecourses', $COURSE->id);
}
require_login($COURSE->id);
require_capability('block/th_manage_activatecourses:view', context_course::instance($COURSE->id));

$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$blockth_bulkenrolkey = optional_param('key', 0, PARAM_ALPHANUMEXT);
$option = required_param('option', PARAM_INT);

if ($option != 0 && $option != 1) {
	$returnurl = new moodle_url('/blocks/th_manage_activatecourses/view.php');
	redirect($returnurl);
}

if ($returnurl) {
	$returnurl = new moodle_url($returnurl);
} else {
	$returnurl = new moodle_url('/blocks/th_manage_activatecourses/view.php');
}

$baseurl = new moodle_url('/blocks/th_manage_activatecourses/user.php');
$PAGE->set_url($baseurl);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

if ($option == 0) {
	$strheading = get_string('add_users', 'block_th_manage_activatecourses');
} else {
	$strheading = get_string('del_users', 'block_th_manage_activatecourses');
}

$PAGE->set_title($strheading);
$PAGE->set_heading($COURSE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'block_th_manage_activatecourses'), new moodle_url('/blocks/th_manage_activatecourses/view.php'));

// print_object($option);

if (empty($blockth_bulkenrolkey)) {
	$form = new th_bulk_form(null, array('option' => $option));
	if ($formdata = $form->get_data()) {

		$emails = $formdata->usermails;
		$campaignid = 0;
		if (!empty($formdata->campaignid)) {
			$campaignid = $formdata->campaignid;
		}

		$checkedmails = block_th_manage_activatecourses_check_user_mails($emails, $option);

		// Create block_th_vmc_campaign array in Session.
		if (!isset($SESSION->block_th_bulkenrol)) {
			$SESSION->block_th_bulkenrol = array();
		}
		// Save data in Session.
		$blockth_bulkenrolkey = $campaignid . "_" . time();
		$SESSION->block_th_bulkenrol[$blockth_bulkenrolkey] = $checkedmails;

		// Create block_th_bulkenrol_inputs array in session.
		if (!isset($SESSION->block_th_bulkenrol_inputs)) {
			$SESSION->block_th_bulkenrol_inputs = array();
		}
		$blockth_bulkenroldata = $blockth_bulkenrolkey . '_data';
		$SESSION->block_th_bulkenrol_inputs[$blockth_bulkenroldata] = $formdata;

		if (!isset($SESSION->block_th_bulkenrol_options)) {
			$SESSION->block_th_bulkenrol_options = array();
		}

		$SESSION->block_th_bulkenrol_options[$blockth_bulkenrolkey] = $option;

		if (!empty($formdata->campaignid)) {
			if (!isset($SESSION->block_th_bulkenrol_campaign)) {
				$SESSION->block_th_bulkenrol_campaign = array();
			}

			$SESSION->block_th_bulkenrol_campaign[$blockth_bulkenrolkey] = $campaignid;
		}

	} else if ($form->is_cancelled()) {
		$returnurl = new moodle_url('/blocks/th_manage_activatecourses/view.php');
		redirect($returnurl);
	} else {
		echo $OUTPUT->header();
		if ($option == 0) {
			echo $OUTPUT->heading(get_string('add_users', 'block_th_manage_activatecourses'));
		} else {
			echo $OUTPUT->heading(get_string('del_users', 'block_th_manage_activatecourses'));
		}
		$baseurl = new moodle_url('/blocks/th_manage_activatecourses/user.php', array('option' => $option));

		if ($editcontrols = block_th_manage_activatecourses_controls($context, $baseurl)) {
			echo $OUTPUT->render($editcontrols);
		}
		echo $form->display();
		echo $OUTPUT->footer();
	}
}

if ($blockth_bulkenrolkey) {

	$form2 = new confirm_form(null, array('block_th_bulkenrol_key' => $blockth_bulkenrolkey, 'option' => $option));

	if ($formdata = $form2->get_data()) {
		if (!empty($blockth_bulkenrolkey) && !empty($SESSION->block_th_bulkenrol) &&
			array_key_exists($blockth_bulkenrolkey, $SESSION->block_th_bulkenrol)) {
			set_time_limit(600);

			$option = $SESSION->block_th_bulkenrol_options[$blockth_bulkenrolkey];

			if ($option == 0) {
				//add
				$msg = block_th_bulk_add_activatecourses_users($blockth_bulkenrolkey);
			}
			if ($option == 1) {
				//delete
				$msg = block_th_bulk_delete_activatecourses_users($blockth_bulkenrolkey);
			}

			if ($msg->status == 'error') {
				redirect($CFG->wwwroot . '/blocks/th_manage_activatecourses/view.php', "$msg->text", null, \core\output\notification::NOTIFY_ERROR);
			} else {
				redirect($CFG->wwwroot . '/blocks/th_manage_activatecourses/view.php', "$msg->text", null, \core\output\notification::NOTIFY_SUCCESS);
			}

		} else {
			redirect($CFG->wwwroot . '/blocks/th_manage_activatecourses/view.php');
		}
	} else if ($form2->is_cancelled()) {
		redirect($CFG->wwwroot . '/blocks/th_manage_activatecourses/user.php?option=' . $option);
	} else {

		$PAGE->set_url('/blocks/th_manage_activatecourses/user_edit.php');

		echo $OUTPUT->header();
		if ($option == 0) {
			echo $OUTPUT->heading(get_string('add_users', 'block_th_manage_activatecourses'));
		} else {
			echo $OUTPUT->heading(get_string('del_users', 'block_th_manage_activatecourses'));
		}
		$baseurl = new moodle_url('/blocks/th_manage_activatecourses/user.php', array('option' => $option));

		if ($editcontrols = block_th_manage_activatecourses_controls($context, $baseurl)) {
			echo $OUTPUT->render($editcontrols);
		}
		if (!empty($blockth_bulkenrolkey) && !empty($SESSION->block_th_bulkenrol) &&
			array_key_exists($blockth_bulkenrolkey, $SESSION->block_th_bulkenrol)) {

			$blockth_bulkenroldata = $SESSION->block_th_bulkenrol[$blockth_bulkenrolkey];

			$option = $SESSION->block_th_bulkenrol_options[$blockth_bulkenrolkey];

			if (!empty($blockth_bulkenroldata)) {

				if ($option == 0) {
					//add
					block_th_bulk_add_activatecourses_display_table($blockth_bulkenroldata, BLOCKth_bulkactivatecourse_HINT);
					block_th_bulk_add_activatecourses_display_table($blockth_bulkenroldata, BLOCKth_bulkactivatecourse_ENROLUSERS);
				}
				if ($option == 1) {
					//delete
					block_th_bulk_delete_activatecourses_display_table($blockth_bulkenroldata, BLOCKth_bulkactivatecourse_HINT);
					block_th_bulk_delete_activatecourses_display_table($blockth_bulkenroldata, BLOCKth_bulkactivatecourse_ENROLUSERS);
				}
			}
		}

		// Show notification if there aren't any valid email addresses to enrol.
		if (!empty($blockth_bulkenroldata) && isset($blockth_bulkenroldata->validemailfound) &&
			empty($blockth_bulkenroldata->validemailfound)) {
			$a = new stdClass();
			$url = new moodle_url('/blocks/th_manage_activatecourses/user.php', array('editlist' => $blockth_bulkenrolkey, 'option' => $option));
			$a->url = $url->out();
			$notification = new \core\output\notification(
				get_string('error_no_valid_email_in_list', 'block_th_manage_activatecourses', $a),
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