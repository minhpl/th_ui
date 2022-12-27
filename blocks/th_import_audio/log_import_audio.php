<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';
require_once 'th_import_audio_form.php';
require_once $CFG->dirroot . '/lib/filelib.php';
require_once "lib.php";

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

// Check for all required variables.
$courseid = $COURSE->id;
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$id = optional_param('id', 0, PARAM_INT);
$contextid = optional_param('contextid', '', PARAM_RAW);
$option = optional_param('option', '', PARAM_INT);
$filename = optional_param('filename', '', PARAM_RAW);
$filearea = optional_param('filearea', '', PARAM_RAW);
$itemid = optional_param('itemid', '', PARAM_RAW);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_import_audio', $courseid);
}

require_login($courseid);
require_capability('block/th_import_audio:view', context_course::instance($COURSE->id));

$pageurl = '/blocks/th_import_audio/log_import_audio.php';
$title = get_string('title', 'block_th_import_audio');
$PAGE->set_url('/blocks/th_import_audio/log_import_audio.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_import_audio', 'block_th_import_audio'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_import_audio'));

$editurl = new moodle_url('/blocks/th_import_audio/log_import_audio.php');
$settingsnode = $PAGE->navbar->add(get_string('breadcrumb', 'block_th_import_audio'), $editurl);
$settingsnode->make_active();

if ($delete) {
	$PAGE->url->param('delete', 1);
	if ($confirm and confirm_sesskey()) {

		$fs = get_file_storage();

		if ($option == 0) {
			$fs->delete_area_files($contextid, 'user', 'draft', $itemid);
			$DB->delete_records('th_log_import_audio', ['id' => $id]);
		} else {

			// $contextid_arr = explode(',', $contextid);
			// $contextid1 = $contextid_arr[0];
			// $contextid2 = $contextid_arr[1];

			// $itemid_arr = explode(',', $itemid);

			// $itemid1 = $itemid_arr[0];
			// $itemid2 = $itemid_arr[1];

			// $fs->delete_area_files($contextid1, 'user', 'draft', $itemid1);
			$fs->delete_area_files($contextid, 'question', 'questiontext', $itemid);
			$DB->delete_records('th_log_import_audio', ['id' => $id]);
		}

		redirect($CFG->wwwroot . '/blocks/th_import_audio/log_import_audio.php', "Xóa file <strong>$filename</strong> thành công", null, \core\output\notification::NOTIFY_SUCCESS);
	}
	$strheading = get_string('delete');
	$PAGE->navbar->add($strheading);
	$PAGE->set_title($strheading);
	$PAGE->set_heading($COURSE->fullname);
	echo $OUTPUT->header();
	echo $OUTPUT->heading($strheading);
	$yesurl = new moodle_url('/blocks/th_import_audio/log_import_audio.php', array('id' => $id, 'contextid' => $contextid, 'option' => $option, 'filename' => $filename, 'itemid' => $itemid, 'confirm' => 1, 'delete' => 1, 'sesskey' => sesskey()));
	$message = "Bạn có chắc chắn muốn xóa file <strong>$filename</strong>";
	echo $OUTPUT->confirm($message, $yesurl, new moodle_url('/blocks/th_import_audio/log_import_audio.php'));
	echo $OUTPUT->footer();
	die;
}

$log_import = $DB->get_records_sql("SELECT * FROM {th_log_import_audio}");

$baseurl = new moodle_url('/blocks/th_import_audio/log_import_audio.php');

$table = new html_table();
// $table->head = array('STT', 'Tên file', 'Tên khóa học', 'draftitemid', 'Thời gian tạo', 'Xóa');
$table->head = array('STT', 'Tên file', 'Tên khóa học', 'Thời gian tạo', 'Xóa');
$stt = 0;

foreach ($log_import as $log) {

	$stt++;
	$course_name = $DB->get_field_sql("SELECT fullname FROM {course} WHERE id = '$log->courseid'");

	$urlparams = array('id' => $log->id, 'contextid' => $log->contextid, 'option' => $log->option, 'filename' => $log->filename, 'itemid' => $log->itemid, 'returnurl' => $baseurl->out_as_local_url(false));
	$link_delete = new moodle_url('/blocks/th_import_audio/log_import_audio.php', $urlparams + array('delete' => 1));
	$delete = html_writer::link(
		$link_delete,
		$OUTPUT->pix_icon('t/delete', get_string('delete')),
		array('title' => get_string('delete'))
	);

	$row = new html_table_row();
	$cell = new html_table_cell($stt);
	$row->cells[] = $cell;
	$cell = new html_table_cell($log->filename);
	$row->cells[] = $cell;
	$cell = new html_table_cell($course_name);
	$row->cells[] = $cell;
	// $cell           = new html_table_cell($log->itemid);
	// $row->cells[]   = $cell;
	$cell = new html_table_cell(date("d-m-Y H:i:s", $log->timecreated));
	$row->cells[] = $cell;
	$cell = new html_table_cell($delete);
	$row->cells[] = $cell;
	$table->data[] = $row;
}

$table->attributes = array('class' => 'th_log_import_audio_table', 'border' => '1');
$table->attributes['style'] = "width: 100%; text-align:center;";
$html = html_writer::table($table);

echo $OUTPUT->header();
echo $OUTPUT->heading("<center>LOG IMPORT AUDIO</center></br>");
echo $html;
$lang = current_language();
echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_log_import_audio_table', 'LOG IMPORT AUDIO', $lang));
echo $OUTPUT->footer();

?>