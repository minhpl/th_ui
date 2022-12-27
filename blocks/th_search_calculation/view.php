<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->libdir.'/mathslib.php';
require_once $CFG->dirroot . '/blocks/th_search_calculation/lib.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

// Check for all required variables.
$courseid = $COURSE->id;

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_search_calculation', $courseid);
}

require_login($courseid);
require_capability('block/th_search_calculation:view', context_course::instance($COURSE->id));

$pageurl = '/blocks/th_search_calculation/view.php';
$context = context_system::instance();
$title = get_string('title', 'block_th_search_calculation');
$PAGE->set_url('/blocks/th_search_calculation/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_search_calculation', 'block_th_search_calculation'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_search_calculation'));

$editurl = new moodle_url('/blocks/th_search_calculation/view.php');
$settingsnode = $PAGE->navbar->add(get_string('breadcrumb', 'block_th_search_calculation'), $editurl);
$settingsnode->make_active();

$listcourses = $DB->get_records_sql("SELECT * FROM {course} WHERE NOT id = 1");

$table = new html_table();
$table->head = array('STT', 'Tên khóa học', 'Tên rút gọn khóa học', 'Công thức tổng khóa học');
$stt = 0;

foreach ($listcourses as $k => $course) {
	$course_id = $course->id;

	$id = $DB->get_field_sql("SELECT id FROM {grade_items} WHERE courseid = '$course_id' AND sortorder = 1");

	if (!empty($id)) {
		$gpr = new grade_plugin_return();
		$grade_item = grade_item::fetch(array('id'=>$id, 'courseid'=>$course->id));

		$calculation = calc_formula::localize($grade_item->calculation);
		$calculation = grade_item::denormalize_formula($calculation, $grade_item->courseid);

		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;

		$link = new moodle_url('/grade/edit/tree/index.php', ['id' => $course_id]);
		$link_setup_grade = html_writer::link($link, $course->fullname);

		$cell = new html_table_cell($link_setup_grade);
		$row->cells[] = $cell;
		$cell = new html_table_cell($course->shortname);
		$row->cells[] = $cell;
		if (!empty($calculation)) {
			$cell = new html_table_cell("<span style = 'color: #fff'>$calculation</span>");
			$row->cells[] = $cell;
			$cell->attributes = array('class' => "bg-success");
		} else {
			$cell = new html_table_cell("<span style = 'color: #fff'>Không tìm thấy công thức tổng</span>");
			$row->cells[] = $cell;
			$cell->attributes = array('class' => "bg-danger");
		}
	} else {
		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;

		$link = new moodle_url('/grade/edit/tree/index.php', ['id' => $course_id]);
		$link_setup_grade = html_writer::link($link, $course->fullname);

		$cell = new html_table_cell($link_setup_grade);
		$row->cells[] = $cell;
		$cell = new html_table_cell($course->shortname);
		$row->cells[] = $cell;
		$cell = new html_table_cell("<span style = 'color: #fff'>Không tìm thấy công thức tổng</span>");
		$row->cells[] = $cell;
		$cell->attributes = array('class' => "bg-danger");
	}

	$table->data[] = $row;
}

$table->attributes = array('class' => 'th_search_calculation', 'border' => '1');
$table->attributes['style'] = "width: 100%;"; 
$html = html_writer::table($table);

$lang = current_language();
echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_search_calculation', 'DANH SÁCH MÔN HỌC CHỨA CÔNG THỨC', $lang));

echo $OUTPUT->header();

$baseurl = new moodle_url('/blocks/th_search_calculation/view.php');
if ($editcontrols = local_th_update_calculation_controls($context, $baseurl)) {
	echo $OUTPUT->render($editcontrols);
}

echo "<center><h3>DANH SÁCH MÔN HỌC CHỨA CÔNG THỨC</h3></center>";
echo $html;
echo $OUTPUT->footer();

?>

