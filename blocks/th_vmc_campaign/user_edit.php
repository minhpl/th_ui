<?php
require '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once "lib.php";
require_once "th_vmc_campaign_form.php";

global $DB, $CFG, $COURSE, $PAGE, $OUTPUT, $SESSION;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', 'block_th_vmc_campaign', $COURSE->id);
}
require_login($COURSE->id);
require_capability('block/th_vmc_campaign:view', context_course::instance($COURSE->id));

$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$id = required_param('id', PARAM_INT);
$blockth_bulkenrolkey = optional_param('key', 0, PARAM_ALPHANUMEXT);
$option = required_param('option', PARAM_INT);

if ($returnurl) {
	$returnurl = new moodle_url($returnurl);
} else {
	$returnurl = new moodle_url('/blocks/th_vmc_campaign/view.php');
}

$baseurl = new moodle_url('/blocks/th_vmc_campaign/user.php');
$PAGE->set_url($baseurl);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

$strheading = get_string('addcampaign', 'block_th_vmc_campaign');
$PAGE->set_title($strheading);
$PAGE->set_heading($COURSE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'block_th_vmc_campaign'), new moodle_url('/blocks/th_vmc_campaign/view.php'));

if (empty($blockth_bulkenrolkey)) {
	$form = new th_bulkcampaign_form(null, array('campaignid' => $id, 'option' => $option));
	if ($formdata = $form->get_data()) {

		$emails = $formdata->usermails;
		$campaignid = $formdata->id;

		$checkedmails = block_th_vmc_campaign_check_user_mails($emails, $campaignid, $option);

		// Create block_th_vmc_campaign array in Session.
		if (!isset($SESSION->block_th_bulkenrol)) {
			$SESSION->block_th_bulkenrol = array();
		}
		// Save data in Session.
		$blockth_bulkenrolkey = $campaignid . '_' . time();
		$SESSION->block_th_bulkenrol[$blockth_bulkenrolkey] = $checkedmails;

		// Create block_th_bulkenrol_inputs array in session.
		if (!isset($SESSION->block_th_bulkenrol_inputs)) {
			$SESSION->block_th_bulkenrol_inputs = array();
		}
		$blockth_bulkenroldata = $blockth_bulkenrolkey . '_data';
		$SESSION->block_th_bulkenrol_inputs[$blockth_bulkenroldata] = $emails;

		if (!isset($SESSION->block_th_bulkenrol_options)) {
			$SESSION->block_th_bulkenrol_options = array();
		}

		$SESSION->block_th_bulkenrol_options[$blockth_bulkenrolkey] = $option;

	} else if ($form->is_cancelled()) {
		$returnurl = new moodle_url('/blocks/th_vmc_campaign/user.php', array('id' => $id, 'returnurl' => $returnurl->out_as_local_url(false)));
		redirect($returnurl);
	} else {
		echo $OUTPUT->header();
		if ($option == 0) {
			echo $OUTPUT->heading(get_string('add_users', 'block_th_vmc_campaign'));
		} else {
			echo $OUTPUT->heading(get_string('del_users', 'block_th_vmc_campaign'));
		}

		echo $form->display();
		echo $OUTPUT->footer();
	}
}
//print_object($option);
// exit();

if ($blockth_bulkenrolkey) {

	$form2 = new confirm_form(null, array('block_th_bulkenrol_key' => $blockth_bulkenrolkey, 'campaignid' => $id, 'option' => $option));

	if ($formdata = $form2->get_data()) {
		if (!empty($blockth_bulkenrolkey) && !empty($SESSION->block_th_bulkenrol) &&
			array_key_exists($blockth_bulkenrolkey, $SESSION->block_th_bulkenrol)) {
			set_time_limit(600);

			$option = $SESSION->block_th_bulkenrol_options[$blockth_bulkenrolkey];

			if ($option == 0) {
				//add
				$msg = block_th_bulkenrol_users($blockth_bulkenrolkey);
			} else if ($option == 1) {
				//delete
				$msg = block_th_bulkunenrol_users($blockth_bulkenrolkey);
			}

			if ($msg->status == 'error') {
				redirect($CFG->wwwroot . '/blocks/th_vmc_campaign/user.php?id=' . $id . '&returnurl=' . $returnurl->out_as_local_url(false), "$msg->text", null, \core\output\notification::NOTIFY_ERROR);
			} else {
				redirect($CFG->wwwroot . '/blocks/th_vmc_campaign/user.php?id=' . $id . '&returnurl=' . $returnurl->out_as_local_url(false), "$msg->text", null, \core\output\notification::NOTIFY_SUCCESS);
			}

		} else {
			redirect($CFG->wwwroot . '/blocks/th_vmc_campaign/view.php');
		}
	} else if ($form2->is_cancelled()) {
		redirect($CFG->wwwroot . '/blocks/th_vmc_campaign/user.php?id=' . $id . '&returnurl=' . $returnurl->out_as_local_url(false));
	} else {

		$PAGE->set_url('/blocks/th_vmc_campaign/user_edit.php');

		echo $OUTPUT->header();
		if ($option == 0) {
			echo $OUTPUT->heading(get_string('add_users', 'block_th_vmc_campaign'));
		} else {
			echo $OUTPUT->heading(get_string('del_users', 'block_th_vmc_campaign'));
		}
		if (!empty($blockth_bulkenrolkey) && !empty($SESSION->block_th_bulkenrol) &&
			array_key_exists($blockth_bulkenrolkey, $SESSION->block_th_bulkenrol)) {

			$blockth_bulkenroldata = $SESSION->block_th_bulkenrol[$blockth_bulkenrolkey];

			$option = $SESSION->block_th_bulkenrol_options[$blockth_bulkenrolkey];
			// print_object($blockth_bulkenroldata);
			// exit();

			if (!empty($blockth_bulkenroldata)) {

				if ($option == 0) {
					//add
					block_th_vmc_campaign_display_table($blockth_bulkenroldata, BLOCKth_bulkenrol_HINT);
					block_th_vmc_campaign_display_table($blockth_bulkenroldata, BLOCKth_bulkenrol_ENROLUSERS);
				} else if ($option == 1) {
					//delete
					block_th_bulkunenrol_display_table($blockth_bulkenroldata, BLOCKth_bulkenrol_HINT);
					block_th_bulkunenrol_display_table($blockth_bulkenroldata, BLOCKth_bulkenrol_ENROLUSERS);
				}
			}
		}

		// Show notification if there aren't any valid email addresses to enrol.
		if (!empty($blockth_bulkenroldata) && isset($blockth_bulkenroldata->validemailfound) &&
			empty($blockth_bulkenroldata->validemailfound)) {
			$a = new stdClass();
			$url = new moodle_url('/blocks/th_vmc_campaign/user_edit.php', array('id' => $id, 'editlist' => $blockth_bulkenrolkey, 'option' => $option));
			$a->url = $url->out();
			$notification = new \core\output\notification(
				get_string('error_no_valid_email_in_list', 'block_th_vmc_campaign', $a),
				\core\output\notification::NOTIFY_WARNING);
			$notification->set_show_closebutton(false);
			echo $OUTPUT->render($notification);

			// Otherwise show the enrolment details and the form with the enrol users button.
		} else {
			echo $form2->display();
		}
		//print_object($blockth_bulkenroldata);
		//exit();
		echo $OUTPUT->footer();
	}
}