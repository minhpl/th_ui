<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';
require_once 'th_report_hide_course_form.php';
require_once $CFG->dirroot . '/blocks/th_report_hide_course/classes/libs.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

// Check for all required variables.
$courseid = $COURSE->id;
$returnto = optional_param('returnto', 'course', PARAM_ALPHANUM); // Generic navigation return page switch.
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_report_hide_course', $courseid);
}

require_login($courseid);
require_capability('block/th_report_hide_course:view', context_course::instance($COURSE->id));

$pageurl = '/blocks/th_report_hide_course/view.php';
$title = get_string('title', 'block_th_report_hide_course');
$PAGE->set_url('/blocks/th_report_hide_course/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_report_hide_course', 'block_th_report_hide_course'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_report_hide_course'));

$editurl = new moodle_url('/blocks/th_report_hide_course/view.php');
$settingsnode = $PAGE->navbar->add(get_string('breadcrumb', 'block_th_report_hide_course'), $editurl);
$settingsnode->make_active();

$th_report_hide_course = new th_report_hide_course_form();

if ($th_report_hide_course->is_cancelled()) {
	// Cancelled forms redirect to the course main page.
	$courseurl = new moodle_url('/my');
	redirect($courseurl);
} else if ($fromform = $th_report_hide_course->get_data()) {

	$startdate = $fromform->startdate;

	$listcourses = get_list_hide_courses($startdate);

	$table = new html_table();
	// $table->head = array('STT', 'Tên khóa học', 'Tên rút gọn', 'Giảng viên', 'SDT 1', 'SDT 2', 'email', 'QLHT AUM', 'QLHT TNU', 'GVCN', 'Trạng thái');
	$table->head = array('STT', 'Tên khóa học', 'Tên rút gọn', 'Giảng viên', 'SDT', 'email', 'AUM QLHT', 'GVCN', 'Trạng thái');
	$stt = 0;

	foreach ($listcourses as $k => $courses) {

		$stt++;

		$link_course = new moodle_url('/course/view.php', ['id' => $courses->id]);
		$link = html_writer::link($link_course, $courses->fullname);


		$role_teacher = $DB->get_record('role', array('shortname' => 'editingteacher'));
		$role_qlht_aum = $DB->get_record('role', array('shortname' => 'qlht_aum'));
		$role_gvcn = $DB->get_record('role', array('shortname' => 'teacher'));
		$context = context_course::instance($courses->id);
		
		$teachers = get_role_users($role_teacher->id, $context);
		$ds_qlht_aum = get_role_users($role_qlht_aum->id, $context);
		$ds_gvcn = get_role_users($role_gvcn->id, $context);

		$giang_vien = '';
		$ds_sdt2 = '';
		$ds_email = '';
		$list_email_qlht_aum = '';
		$list_email_gvcn = '';

		if (!empty($teachers)){
		
			foreach ($teachers as $k => $teacher) {

				$teacher_id = $teacher->id;
				$teacher = $DB->get_record_sql("SELECT * FROM {user} WHERE id = '$teacher_id'");

				$name = $teacher->firstname . ' ' . $teacher->lastname;
				$giang_vien = $giang_vien . $name . '<br>';
				
				$sdt2 = $teacher->phone2;
				$ds_sdt2 = $ds_sdt2 . $sdt2 . '<br>';

				$email = $teacher->email;
				$ds_email = $ds_email . $email . '<br>';
			}
		}

		if(!empty($ds_qlht_aum)){
			foreach ($ds_qlht_aum as $k => $qlht_aum) {

				$email_qlht_aum = $qlht_aum->email;
				$list_email_qlht_aum = $list_email_qlht_aum . $email_qlht_aum . ',<br>';
			}
		}


		if(!empty($ds_gvcn)){
			foreach ($ds_gvcn as $k => $gvcn) {

				$email_gvcn = $gvcn->email;
				$list_email_gvcn = $list_email_gvcn . $email_gvcn . ',<br>';
			}
		}

		
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell($link);
		$row->cells[] = $cell;
		$cell = new html_table_cell($courses->shortname);
		$row->cells[] = $cell;
		$cell = new html_table_cell($giang_vien);
		$row->cells[] = $cell;
		$cell = new html_table_cell($ds_sdt2);
		$row->cells[] = $cell;
		$cell = new html_table_cell($ds_email);
		$row->cells[] = $cell;
		$cell = new html_table_cell($list_email_qlht_aum);
		$row->cells[] = $cell;
		$cell = new html_table_cell($list_email_gvcn);
		$row->cells[] = $cell;
		$cell = new html_table_cell();

		if ($courses->visible == 0) {
			$cell->text = html_writer::tag('span', 'Khóa học hiện tại chưa được mở',
			array('class' => 'badge badge-warning'));
		} else {
			$cell->text = html_writer::tag('span', 'Khóa học hiện tại đã được mở',
			array('class' => 'badge badge-success'));
		}
		
		$row->cells[] = $cell;
		$table->data[] = $row;
	}

	$table->attributes = array('class' => 'th_hide_course_table', 'border' => '1');
	$table->attributes['style'] = "width: 100%; text-align:center;";
	$html = html_writer::table($table);

	echo $OUTPUT->header();
	echo $OUTPUT->heading("<center>$title</center>");
	$th_report_hide_course->display();
	echo $OUTPUT->heading('<center>DANH SÁCH KHÓA HỌC</center>');
	echo $html;
	$lang = current_language();
	echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
	$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_hide_course_table', 'LIST HIDE COURSE', $lang));
	echo $OUTPUT->footer();

} else {
	echo $OUTPUT->header();
	echo $OUTPUT->heading("<center>$title</center>");
	$th_report_hide_course->display();
	echo $OUTPUT->footer();
}

?>

