<?php
require '../../config.php';
require_once "lib.php";
require_once "th_vmc_campaign_form.php";

global $DB, $CFG, $COURSE;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', 'block_th_vmc_campaign', $COURSE->id);
}
require_login($COURSE->id);
require_capability('block/th_vmc_campaign:view', context_course::instance($COURSE->id));

$id = required_param('id', PARAM_INT);
$campaignid = optional_param('campaignid', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

$baseurl = new moodle_url('/blocks/th_vmc_campaign/view.php');
$urlparams = array('id' => $campaignid, 'returnurl' => $baseurl->out_as_local_url(false));
$returnurl = new moodle_url('/blocks/th_vmc_campaign/user.php', $urlparams);

$baseurl = new moodle_url('/blocks/th_vmc_campaign/user_delete.php');
$PAGE->set_url($baseurl);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

if ($delete) {
	$PAGE->url->param('delete', 1);
	if ($confirm and confirm_sesskey()) {
		$check = delete_user_campaign_course($id);

		if ($check == 1) {
			redirect($returnurl, get_string('delete_successful', 'block_th_vmc_campaign'), null, \core\output\notification::NOTIFY_SUCCESS);
		} else {
			redirect($returnurl, get_string('delete_error', 'block_th_vmc_campaign'), null, \core\output\notification::NOTIFY_ERROR);
		}
	}
	$strheading = get_string('delete_user_campaign_course', 'block_th_vmc_campaign');
	$PAGE->navbar->add($strheading);
	$PAGE->set_title($strheading);
	$PAGE->set_heading($COURSE->fullname);
	echo $OUTPUT->header();
	echo $OUTPUT->heading($strheading);
	$yesurl = new moodle_url('/blocks/th_vmc_campaign/user_delete.php', array('id' => $id, 'confirm' => 1, 'delete' => 1, 'sesskey' => sesskey(), 'campaignid' => $campaignid));
	$message = get_string('confirm_delete_user', 'block_th_vmc_campaign', array('username' => get_username($id)->username, 'fullname' => get_username($id)->fullname));
	echo $OUTPUT->confirm($message, $yesurl, $returnurl);
	echo $OUTPUT->footer();
	die;
}