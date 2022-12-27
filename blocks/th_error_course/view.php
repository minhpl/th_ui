<?php

use \block_th_error_course\libs;
require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

// Check for all required variables.
$courseid = $COURSE->id;
$returnto = optional_param('returnto', 'course', PARAM_ALPHANUM); // Generic navigation return page switch.
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_error_course', $courseid);
}

require_login($courseid);
require_capability('block/th_error_course:view', context_course::instance($COURSE->id));

$pageurl = '/blocks/th_error_course/view.php';
$title = get_string('title', 'block_th_error_course');
$PAGE->set_url('/blocks/th_error_course/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_error_course', 'block_th_error_course'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_error_course'));
$PAGE->requires->js_call_amd('local_thlib/main', 'addAsteriskToCustomRequiredFieldForm', array($CFG->wwwroot));

$editurl = new moodle_url('/blocks/th_error_course/view.php');
$settingsnode = $PAGE->navbar->add(get_string('breadcrumb', 'block_th_error_course'), $editurl);
$settingsnode->make_active();

$libs = new libs();
$listcourses = $libs->get_list_courses();

$error_courses = [];

foreach ($listcourses as $k => $listcourse) {

	$arr_name = explode(',', $listcourse);

	$name_course = $arr_name[0];
	$shortname = $arr_name[1];
	$idnumber = $arr_name[2];

	if (preg_match('/.*( {0,}- {0,})[0-9]{6}/', $name_course, $matches)) {
		$allcourses[$k] = $name_course;
		if (preg_match('/.*[A-Z0-9]+-[0-9]{6}/', $shortname, $matches2)) {
			$all_shortnames[$k] = $matches2[0];
		} else {
			if (in_array($name_course, $error_courses) != true) {
				$error_courses[$k] = [$name_course, 2];
			}
		}

		//Check shortname va id trung nhau
		if ($shortname != $idnumber) {
			if (in_array($name_course, $error_courses) != true) {
				$error_courses[$k] = [$name_course, 1];
			}
		}
	}

	if (preg_match('/.*( {1}- {1})[0-9]{6}/', $name_course, $matches1)) {
		$courses[$k] = $name_course;
	}

}

foreach ($allcourses as $k => $allcourse) {
	if (in_array($allcourse, $courses) != true) {
		$error_courses[$k] = [$allcourse, 0];
	}
}

$table = new html_table();
$table->head = array('STT', 'Fullname', 'Notification', 'Action');
$stt = 0;

foreach ($error_courses as $k => $error_course) {

	$stt = $stt + 1;
	$link_edit = new moodle_url('/course/edit.php', ['id' => $k]);
	$edit = html_writer::link($link_edit, $OUTPUT->pix_icon('t/edit', get_string('edit')), array('title' => get_string('edit')));

	$row = new html_table_row();
	$cell = new html_table_cell($stt);
	$row->cells[] = $cell;
	$cell = new html_table_cell($error_course[0]);
	$row->cells[] = $cell;
	if ($error_course[1] == 0) {
		$cell = new html_table_cell(get_string('error_name', 'block_th_error_course'));
		$row->cells[] = $cell;
	} else if ($error_course[1] == 1) {
		$cell = new html_table_cell(get_string('error_shortname_id', 'block_th_error_course'));
		$row->cells[] = $cell;
	} else {
		$cell = new html_table_cell(get_string('error_shortname', 'block_th_error_course'));
		$row->cells[] = $cell;
	}

	$cell = new html_table_cell($edit);
	$row->cells[] = $cell;
	$table->data[] = $row;
}

$table->attributes = array('class' => 'th_error_course_table', 'border' => '1');
$table->attributes['style'] = "width: 100%; text-align:center;";
$html = html_writer::table($table);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
echo "</br>";
if (empty($error_courses)) {
	echo 'Không tìm thấy khóa học nào bị sai định dạng';
} else {
	echo $html;
}
$lang = current_language();
echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_error_course_table', 'LIST ERROR COURSE', $lang));
echo $OUTPUT->footer();
?>

