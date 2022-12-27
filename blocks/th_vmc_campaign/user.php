<?php
require '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once "lib.php";

global $DB, $CFG, $COURSE;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', 'block_th_vmc_campaign', $COURSE->id);
}
require_login($COURSE->id);
require_capability('block/th_vmc_campaign:view', context_course::instance($COURSE->id));

$campaign_id = optional_param('id', 0, PARAM_INT);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

if ($returnurl) {
	$returnurl = new moodle_url($returnurl);
} else {
	$returnurl = new moodle_url('/blocks/th_vmc_campaign/view.php');
}

if ($campaign_id && $DB->record_exists('marketing_campaign', array('id' => $campaign_id), '*', MUST_EXIST)) {
	$user_campaign_course = $DB->get_records('user_campaign_course', array('campaignid' => $campaign_id));
	$total = count($user_campaign_course);
} else {
	redirect($returnurl);
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

if ($user_campaign_course != null) {
	$extra = array_filter(explode(',', $CFG->showuseridentity));
	$userfields = array_values($extra);

	$usernamefield = get_all_user_name_fields();
	$usernamefield = implode(",", $usernamefield);
	$alluserfields = "id," . $usernamefield;

	if (count($userfields) > 0) {
		$alluserfields .= "," . implode(',', $userfields);
	}

	$alluserfields .= "," . "email";

	$user_arr = $DB->get_records('user', array('deleted' => 0, 'suspended' => 0), "", $alluserfields);
	$userid_arr = array();
	foreach ($user_campaign_course as $key => $user) {
		$userid_arr[] = $user->userid;
	}
	$table = new html_table();
	$rightrows = [];

	$headrows = new html_table_row();
	$cell = new html_table_cell(get_string('course'));
	$cell->attributes['class'] = 'cell headingcell';
	$cell->header = true;
	$headrows->cells[] = $cell;
	$cell = new html_table_cell(get_string('shortname'));
	$cell->attributes['class'] = 'cell headingcell';
	$cell->header = true;
	$headrows->cells[] = $cell;
	$cell = new html_table_cell(get_string('action'));
	$cell->attributes['class'] = 'cell headingcell';
	$cell->header = true;
	$headrows->cells[] = $cell;

	$rightrows[] = $headrows;
	list($leftrows, $rows_ex_left) = get_left_rows($userid_arr, $user_arr);
	$soCot = count($leftrows[0]->cells);
	$config = get_config('local_thlib');
	$strLeft = trim($config->custom_fields1, ' ');
	$strRight = trim($config->custom_fields2, ' ');
	if ($strLeft == '') {
		$cellLeft = 0;
	} else {
		$cellLeft = count(explode(',', trim($config->custom_fields1)));
	}
	if ($strRight == '') {
		$cellRight = 0;
	} else {
		$cellRight = count(explode(',', trim($config->custom_fields2)));
	}
	foreach ($user_campaign_course as $key => $user_course) {
		$userid = $user_course->userid;
		$courseid = $user_course->courseid;
		$row = new html_table_row();

		$cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $courseid, th_vmc_campaign::get_courseid_name($courseid)));
		$cell->attributes['data-order'] = $user_course->id;
		$cell->attributes['data-search'] = th_vmc_campaign::get_courseid_name($courseid);
		$row->cells[] = $cell;

		$cell = new html_table_cell(get_shortname_course_by_id($courseid));
		$cell->attributes['data-order'] = $courseid;
		$cell->attributes['data-search'] = get_shortname_course_by_id($courseid);
		$row->cells[] = $cell;

		$urlparams = array('id' => $user_course->id, 'campaignid' => $campaign_id, 'returnurl' => $baseurl->out_as_local_url(false));
		$delete = html_writer::link(new moodle_url('/blocks/th_vmc_campaign/user_delete.php', $urlparams + array('delete' => 1)),
			$OUTPUT->pix_icon('t/delete', get_string('delete')),
			array('title' => get_string('delete')));

		$cell = new html_table_cell($delete);
		$row->cells[] = $cell;
		$rightrows[$userid . "_" . $courseid] = $row;
	}
	$stt = 0;
	foreach ($rightrows as $key => $row) {
		$userid = explode("_", $key);
		$row->cells = array_merge($leftrows[$userid[0]]->cells, $row->cells);
	}
	foreach ($rightrows as $key => $row) {
		if ($stt != 0) {
			$c = new html_table_cell($stt);
			$row->cells[0] = $c;
		}
		$stt++;
		$table->data[] = $row;
	}
	$headrows = array_shift($table->data);
	$table->head = $headrows->cells;
	$table->attributes = array('class' => 'table', 'border' => '1');
	$table->align[0] = 'center';
	$table->align[$soCot + 2] = 'center';
}

echo $OUTPUT->header();
$strheading = $strheading = get_string('total', 'block_th_vmc_campaign', array('name' => th_vmc_campaign::get_name_campaign($campaign_id), 'total' => $total));
echo $OUTPUT->heading($strheading);
// print_object($user_campaign_course);
if ($user_campaign_course != null) {
	$lang = current_language();
	echo '<link rel="stylesheet" type="text/css" href="<https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">';
	$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.table', "Báo cáo tài khoản", $lang));
	echo html_writer::table($table);
}
$urlparams = array('id' => $campaign_id, 'returnurl' => $baseurl->out_as_local_url(false));

$add = html_writer::link(new moodle_url('/blocks/th_vmc_campaign/user_edit.php', $urlparams + array('option' => 0)),
	get_string('add_users', 'block_th_vmc_campaign'),
	array('title' => get_string('add')));
$delete = html_writer::link(new moodle_url('/blocks/th_vmc_campaign/user_edit.php', $urlparams + array('option' => 1)),
	get_string('del_users', 'block_th_vmc_campaign'),
	array('title' => get_string('delete')));
echo '<br>' . html_writer::span($add, 'btn btn-primary') . "<br><br>" . html_writer::span($delete, 'btn btn-primary');
echo $OUTPUT->footer();