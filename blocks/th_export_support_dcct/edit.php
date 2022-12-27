<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->libdir.'/mathslib.php';
require_once $CFG->dirroot . '/blocks/th_export_support_dcct/edit_form.php';
require_once $CFG->dirroot . '/blocks/th_export_support_dcct/lib.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

// Check for all required variables.
$courseid = $COURSE->id;
$returnto = optional_param('returnto', 'course', PARAM_ALPHANUM); // Generic navigation return page switch.
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_export_support_dcct', $courseid);
}

require_login($courseid);
require_capability('block/th_export_support_dcct:view', context_course::instance($COURSE->id));

$pageurl = '/blocks/th_export_support_dcct/edit.php';
$title = get_string('title', 'block_th_export_support_dcct');
$context = context_system::instance();
$PAGE->set_url('/blocks/th_export_support_dcct/edit.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_export_support_dcct', 'block_th_export_support_dcct'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_export_support_dcct'));

$id        = optional_param('id', 0, PARAM_INT);
$contextid = optional_param('contextid', 0, PARAM_INT);
$delete    = optional_param('delete', 0, PARAM_BOOL);
$confirm   = optional_param('confirm', 0, PARAM_BOOL);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

$editurl = new moodle_url('/blocks/th_export_support_dcct/edit.php');
$settingsnode = $PAGE->navbar->add(get_string('breadcrumb', 'block_th_export_support_dcct'), $editurl);
$settingsnode->make_active();

if ($id) {
	$data_edit = $DB->get_record('th_export_support_dcct', array('id' => $id), '*', MUST_EXIST);
} else {
	$data_edit              = new stdClass();
	$data_edit->ma_lop      = '';
	$data_edit->ho_ten     = '';
	$data_edit->sdt  = '';
	$data_edit->email = '';
	$data_edit->role = '';
	$data_edit->gioi_tinh = '';
}

if ($returnurl) {
	$returnurl = new moodle_url($returnurl);
} else {
	$returnurl = new moodle_url('/blocks/th_export_support_dcct/index.php');
}

$edit = new edit_form();

$edit->set_data(array('ma_lop' => $data_edit->ma_lop, 'ho_ten' => $data_edit->ho_ten, 'sdt' => $data_edit->sdt, 'email' => $data_edit->email, 'role' => $data_edit->role, 'gioi_tinh' => $data_edit->gioi_tinh, 'id' => $id));

if ($delete) {
	$PAGE->url->param('delete', 1);
	if ($confirm and confirm_sesskey()) {

		$DB->delete_records('th_export_support_dcct', ['id' => $id]);
		redirect($CFG->wwwroot . '/blocks/th_export_support_dcct/index.php', get_string('success'), null, \core\output\notification::NOTIFY_SUCCESS);
	}
	$strheading = get_string('delete');
	$PAGE->navbar->add($strheading);
	$PAGE->set_title($strheading);
	$PAGE->set_heading($COURSE->fullname);
	echo $OUTPUT->header();
	echo $OUTPUT->heading($strheading);
	$record  = $DB->get_record('th_export_support_dcct', array('id' => $id));
	$yesurl  = new moodle_url('/blocks/th_export_support_dcct/edit.php', array('id' => $id, 'confirm' => 1, 'delete' => 1, 'sesskey' => sesskey()));
	$message = "Bạn có chắc chắn muốn xóa GVCN/QLHT " . $record->ho_ten;
	echo $OUTPUT->confirm($message, $yesurl, new moodle_url('/blocks/th_export_support_dcct/index.php'));
	echo $OUTPUT->footer();
	die;
}

if ($edit->is_cancelled()) {
	// Cancelled forms redirect to the course main page.
	$courseurl = new moodle_url('/blocks/th_export_support_dcct/index.php');
	redirect($courseurl);
} else if ($fromform = $edit->get_data()) {

	if ($id) {
		$ma_lop        = $fromform->ma_lop;
		$ho_ten = $fromform->ho_ten;
		$sdt = $fromform->sdt;
		$email = $fromform->email;
		$role = $fromform->role;
		$gioi_tinh = $fromform->gioi_tinh;

		$dataobjects             = new stdClass();
		$dataobjects->id     = $id;
		$dataobjects->ma_lop     = $ma_lop;
		$dataobjects->ho_ten    = $ho_ten;
		$dataobjects->sdt = $sdt;
		$dataobjects->email   = $email;
		$dataobjects->role  = $role;
		$dataobjects->gioi_tinh   = $gioi_tinh;
		$DB->update_record('th_export_support_dcct', $dataobjects);

		redirect($returnurl, 'Cập nhật thành công', null, \core\output\notification::NOTIFY_SUCCESS);
	} else {
		$ma_lop        = $fromform->ma_lop;
		$ho_ten = $fromform->ho_ten;
		$sdt = $fromform->sdt;
		$email = $fromform->email;
		$role = $fromform->role;
		$gioi_tinh = $fromform->gioi_tinh;

		$dataobjects             = new stdClass();
		$dataobjects->ma_lop     = $ma_lop;
		$dataobjects->ho_ten    = $ho_ten;
		$dataobjects->sdt = $sdt;
		$dataobjects->email   = $email;
		$dataobjects->role  = $role;
		$dataobjects->gioi_tinh   = $gioi_tinh;
		$DB->insert_record('th_export_support_dcct', $dataobjects, false);

		redirect($returnurl, 'Thêm thành công', null, \core\output\notification::NOTIFY_SUCCESS);
	}

} else {
	// form didn't validate or this is the first display
	echo $OUTPUT->header();

	if ($id) {
		echo $OUTPUT->heading('<center>SỬA GVCN/QLHT</center>');
	} else {
		echo $OUTPUT->heading('<center>THÊM GVCN/QLHT</center>');
	}

	echo "</br>";

	$baseurl = new moodle_url('/blocks/th_export_support_dcct/edit.php');
	if ($editcontrols = local_th_export_support_dcct_controls($context, $baseurl)) {
		echo $OUTPUT->render($editcontrols);
	}
	$edit->display();
	echo $OUTPUT->footer();
}

?>

