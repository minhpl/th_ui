<?php
require '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/blocks/th_manage_activatecourses/classes/lib.php';

global $DB, $CFG, $COURSE;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', 'block_th_manage_activatecourses', $COURSE->id);
}
require_login($COURSE->id);
require_capability('block/th_manage_activatecourses:view', context_course::instance($COURSE->id));

$PAGE->set_url(new moodle_url('/blocks/th_manage_activatecourses/view.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('pluginname', 'block_th_manage_activatecourses'));
$PAGE->set_title(get_string('pluginname', 'block_th_manage_activatecourses'));
$editurl = new moodle_url('/blocks/th_manage_activatecourses/view.php');
$settingsnode = $PAGE->settingsnav->add(get_string('pluginname', 'block_th_manage_activatecourses'), $editurl);

echo $OUTPUT->header();

$page = optional_param('page', 0, PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);

$records = th_manage_activatecourses::get_all_user_register_courses();

$count = $DB->count_records('th_registeredcourses', array('timeactivated' => 0));
echo $OUTPUT->heading(get_string('view', 'block_th_manage_activatecourses') . $count);

$baseurl = new moodle_url('/blocks/th_manage_activatecourses/view.php');

if ($editcontrols = block_th_manage_activatecourses_controls($context, $baseurl)) {
	echo $OUTPUT->render($editcontrols);
}

if (!empty($records)) {
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
	foreach ($records as $key => $user) {
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
	foreach ($records as $key => $record) {
		$userid = $record->userid;
		$courseid = $record->courseid;
		$row = new html_table_row();

		$cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $courseid, th_manage_activatecourses::get_fullname_course($courseid)));
		$cell->attributes['data-order'] = $record->id;
		$cell->attributes['data-search'] = th_manage_activatecourses::get_fullname_course($courseid);
		$row->cells[] = $cell;

		$cell = new html_table_cell(th_manage_activatecourses::get_shortname_course($courseid));
		$cell->attributes['data-order'] = $courseid;
		$cell->attributes['data-search'] = th_manage_activatecourses::get_shortname_course($courseid);
		$row->cells[] = $cell;

		$urlparams = array('id' => $record->id, 'returnurl' => $baseurl->out_as_local_url(false));
		$edit = html_writer::link(new moodle_url('/blocks/th_manage_activatecourses/edit.php', $urlparams),
			$OUTPUT->pix_icon('t/edit', get_string('edit')),
			array('title' => get_string('edit')));
		$delete = html_writer::link(new moodle_url('/blocks/th_manage_activatecourses/edit.php', $urlparams + array('delete' => 1)),
			$OUTPUT->pix_icon('t/delete', get_string('delete')),
			array('title' => get_string('delete')));

		$cell = new html_table_cell($edit . $delete);
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
	$lang = current_language();
	echo '<link rel="stylesheet" type="text/css" href="<https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">';
	$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.table', "Báo cáo tài khoản", $lang));
	echo html_writer::table($table);
}
// $t = new block_th_manage_activatecourses\task\mail_sent_adhoc_task;
// $t->execute();
echo $OUTPUT->footer();