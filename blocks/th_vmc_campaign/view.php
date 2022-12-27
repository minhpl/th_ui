<?php
require '../../config.php';
require_once $CFG->dirroot . '/cohort/lib.php';
require_once $CFG->libdir . '/adminlib.php';
require_once "lib.php";

global $DB, $CFG, $COURSE;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', 'block_th_vmc_campaign', $COURSE->id);
}
require_login($COURSE->id);
require_capability('block/th_vmc_campaign:view', context_course::instance($COURSE->id));

$PAGE->set_url(new moodle_url('/blocks/th_vmc_campaign/view.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('pluginname', 'block_th_vmc_campaign'));
$PAGE->set_title(get_string('pluginname', 'block_th_vmc_campaign'));
$editurl = new moodle_url('/blocks/th_vmc_campaign/view.php');
$settingsnode = $PAGE->settingsnav->add(get_string('pluginname', 'block_th_vmc_campaign'), $editurl);

echo $OUTPUT->header();

$page = optional_param('page', 0, PARAM_INT);
//$searchquery = optional_param('search', '', PARAM_RAW);
$id = optional_param('id', 0, PARAM_INT);

$campaigns = th_vmc_campaign::get_all_marketing_campaign();

$count = th_vmc_campaign::count_all_campaign();
echo $OUTPUT->heading(get_string('viewall', 'block_th_vmc_campaign') . $count);

$baseurl = new moodle_url('/blocks/th_vmc_campaign/view.php');

if ($editcontrols = campaign_edit_controls($context, $baseurl)) {
	echo $OUTPUT->render($editcontrols);
}
$stt = 1;
$data = [];
foreach ($campaigns as $key => $campaign) {
	$line = array();
	$line[] = $stt;
	$line[] = $campaign->campaigncode;
	$line[] = $campaign->campaignname;
	$line[] = $campaign->campaigndescription;

	$buttons = array();
	$urlparams = array('id' => $campaign->id, 'returnurl' => $baseurl->out_as_local_url(false));
	$buttons[] = html_writer::link(new moodle_url('/blocks/th_vmc_campaign/user.php', $urlparams),
		$OUTPUT->pix_icon('i/users', get_string('users')),
		array('title' => get_string('user')));
	$buttons[] = html_writer::link(new moodle_url('/blocks/th_vmc_campaign/edit.php', $urlparams),
		$OUTPUT->pix_icon('t/edit', get_string('edit')),
		array('title' => get_string('edit')));
	$buttons[] = html_writer::link(new moodle_url('/blocks/th_vmc_campaign/edit.php', $urlparams + array('delete' => 1)),
		$OUTPUT->pix_icon('t/delete', get_string('delete')),
		array('title' => get_string('delete')));

	$line[] = implode(' ', $buttons);

	$data[] = $row = new html_table_row($line);
	$stt++;
}
$table = new html_table();
$table->head = array(get_string('no.', 'block_th_vmc_campaign'), get_string('campaigncode', 'block_th_vmc_campaign'), get_string('campaignname', 'block_th_vmc_campaign'), get_string('campaigndescription', 'block_th_vmc_campaign'), get_string('edit'));
$table->colclasses = array('leftalign name', 'leftalign id', 'leftalign description', 'leftalign size', 'centeralign source', 'centeralign action');
$table->id = 'campaign';
$table->attributes['class'] = 'admintable generaltable';
$table->data = $data;
echo html_writer::table($table);
echo $OUTPUT->footer();