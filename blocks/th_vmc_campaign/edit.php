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

$id = optional_param('id', 0, PARAM_INT);
$contextid = optional_param('contextid', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

if ($id) {
	$campaign = $DB->get_record('marketing_campaign', array('id' => $id), '*', MUST_EXIST);
} else {
	$campaign = new stdClass();
	$campaign->id = 0;
	$campaign->campaigncode = '';
	$campaign->campaignname = '';
	$campaign->campaigndescription = '';
}

if ($returnurl) {
	$returnurl = new moodle_url($returnurl);
} else {
	$returnurl = new moodle_url('/blocks/th_vmc_campaign/view.php');
}
$baseurl = new moodle_url('/blocks/th_vmc_campaign/edit.php');
$PAGE->set_url($baseurl);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

if ($delete) {
	$PAGE->url->param('delete', 1);
	if ($confirm and confirm_sesskey()) {
		// print_object($returnurl);
		// exit();
		$check = th_vmc_campaign::delete_marketing_campaign($id);

		if ($check == 1) {
			redirect($returnurl, get_string('delete_campaign_successful', 'block_th_vmc_campaign'), null, \core\output\notification::NOTIFY_SUCCESS);
		} else {
			redirect($returnurl, get_string('delete_campaign_error', 'block_th_vmc_campaign'), null, \core\output\notification::NOTIFY_ERROR);
		}
	}
	$strheading = get_string('delcampaign', 'block_th_vmc_campaign');
	$PAGE->navbar->add($strheading);
	$PAGE->set_title($strheading);
	$PAGE->set_heading($COURSE->fullname);
	echo $OUTPUT->header();
	echo $OUTPUT->heading($strheading);
	$yesurl = new moodle_url('/blocks/th_vmc_campaign/edit.php', array('id' => $id, 'confirm' => 1, 'delete' => 1, 'sesskey' => sesskey()));
	$message = get_string('confirm', 'block_th_vmc_campaign', array('campaignname' => th_vmc_campaign::get_name_campaign($id)));
	echo $OUTPUT->confirm($message, $yesurl, $returnurl);
	echo $OUTPUT->footer();
	die;
}

// $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES,
// 	'maxbytes' => $SITE->maxbytes, 'context' => $context);
if ($campaign->id) {
	// Edit existing.
	$strheading = get_string('editcampaign', 'block_th_vmc_campaign');

} else {
	// Add new.
	$strheading = get_string('addcampaign', 'block_th_vmc_campaign');
}

$PAGE->set_title($strheading);
$PAGE->set_heading($COURSE->fullname);
$PAGE->navbar->add($strheading);

$editform = new th_vmc_campaign_form();
$description = array('text' => $campaign->campaigndescription);
$editform->set_data(array('campaigncode' => $campaign->campaigncode, 'campaignname' => $campaign->campaignname, 'id' => $id, 'description' => $description));

if ($editform->is_cancelled()) {
	redirect($returnurl);

} else if ($data = $editform->get_data()) {
	$dataobject = new stdClass();
	$dataobject->campaigncode = trim($data->campaigncode);
	$dataobject->campaignname = trim($data->campaignname);
	$dataobject->campaigndescription = $data->description['text'];
	$time = time();
	if ($id != 0) {
		$dataobject->id = $id;
		$dataobject->timemodified = $time;
		th_vmc_campaign::update_marketing_campaign($dataobject);
	} else {
		$dataobject->timecreated = $time;
		$dataobject->timemodified = $time;
		th_vmc_campaign::add_marketing_campaign($dataobject);
	}
	// Redirect to where we were before.
	redirect($returnurl);
}

echo $OUTPUT->header();
echo $OUTPUT->heading($strheading);

if (!$id && ($editcontrols = campaign_edit_controls($context, $baseurl))) {
	echo $OUTPUT->render($editcontrols);
}

echo $editform->display();
echo $OUTPUT->footer();