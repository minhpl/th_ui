<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/grade/lib.php';
require_once $CFG->libdir . '/mathslib.php';
require_once $CFG->dirroot . '/blocks/th_search_calculation/lib.php';
require_once 'th_update_calculation_form.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

$th_update_calculation_key = optional_param('key', 0, PARAM_ALPHANUMEXT);

// Check for all required variables.
$courseid = $COURSE->id;

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_search_calculation', $courseid);
}

require_login($courseid);
require_capability('block/th_search_calculation:view', context_course::instance($COURSE->id));

$pageurl = '/blocks/th_search_calculation/th_update_calculation.php';
$context = context_system::instance();
$title = 'Cập nhật công thức hàng loạt';
$PAGE->set_url('/blocks/th_search_calculation/th_update_calculation.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading('Cập nhật công thức hàng loạt');
$PAGE->set_title($SITE->fullname . ': ' . 'Cập nhật công thức hàng loạt');

$editurl = new moodle_url('/blocks/th_search_calculation/th_update_calculation.php');
$settingsnode = $PAGE->navbar->add('Cập nhật công thức hàng loạt', $editurl);
$settingsnode->make_active();

if (empty($th_update_calculation_key)) {

	$th_update_calculation_form = new th_update_calculation_form();

	if ($th_update_calculation_form->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/blocks/th_search_calculation/th_update_calculation.php');
		redirect($courseurl);
	} else if ($formdata = $th_update_calculation_form->get_data()) {

		$list_courses = $DB->get_records_sql("SELECT * FROM {course} WHERE NOT id = 1 AND visible <> 0");

		$ma_diem = $formdata->ma_diem;

		if ($ma_diem == 'kt') {
			$ten_diem = 'Điểm kiểm tra';
		} else {
			$ten_diem = 'Điểm tổng';
		}

		$checked = new stdClass();
		$checked->error_messages = array();
		$checked->data_calculation = array();
		$checked->validfound = 0;

		foreach ($list_courses as $courses) {
			$course_id = $courses->id;

			if ($ma_diem == 'tong') {
				$id = $DB->get_field_sql("SELECT id FROM {grade_items} WHERE courseid = '$course_id' AND sortorder = 1");
			} else if ($ma_diem == 'kt') {
				$id = $DB->get_field_sql("SELECT id FROM {grade_items} WHERE courseid = '$course_id' AND idnumber = '$ma_diem'");
			} else {
				redirect($CFG->wwwroot . "/blocks/th_search_calculation/th_update_calculation.php", 'Chỉ hỗ trợ cập nhật hàng loạt điểm kiểm tra (kt) và điểm tổng (tong)', null, \core\output\notification::NOTIFY_WARNING);
			}

			if (empty($id)) {
				$error = "Không tìm thấy điểm có mã (<strong>$ma_diem</strong>) và tên điểm (<strong>$ten_diem</strong>) trong khóa học (<strong>$courses->fullname</strong>). Khóa học này sẽ bị bỏ qua.";
				$checked->error_messages[] = $error;
			} else {

				$grade_item = grade_item::fetch(array('id' => $id, 'courseid' => $courses->id));
				$calculation = calc_formula::localize($grade_item->calculation);
				$calculation = trim(grade_item::denormalize_formula($calculation, $grade_item->courseid));

				$calculation1 = $formdata->calculation;
				$calculation_new = $formdata->calculation_new;

				if ($calculation == $calculation1) {

					$calculation_new_check = calc_formula::unlocalize($calculation_new);

					$result = $grade_item->validate_formula($calculation_new_check);
		            if ($result !== true) {
		            	redirect($CFG->wwwroot . "/blocks/th_search_calculation/th_update_calculation.php", $result, null, \core\output\notification::NOTIFY_WARNING);
		            }

					$checked->validfound += 1;
					$data_calculation = new stdClass();
					$data_calculation->id = $id;
					$data_calculation->calculation = $calculation1;
					$data_calculation->calculation_new = $calculation_new;
					$data_calculation->courses = $courses;
					$data_calculation->ma_diem = $ma_diem;
					$data_calculation->ten_diem = $ten_diem;
					$checked->data_calculation[] = $data_calculation;
				} else {
					$error = "Không tìm thấy công thức (<strong>$formdata->calculation</strong>) có mã điểm (<strong>$ma_diem</strong>) và tên điểm (<strong>$ten_diem</strong>) trong khóa học (<strong>$courses->fullname</strong>). Khóa học này sẽ bị bỏ qua.";
					$checked->error_messages[] = $error;
				}
			}
		}

		// Save data in Session.
		$th_update_calculation_key = $course->id . '_' . time();
		$SESSION->th_update_calculation[$th_update_calculation_key] = $checked;

	} else {
		echo $OUTPUT->header();

		$baseurl = new moodle_url('/blocks/th_search_calculation/th_update_calculation.php');
		if ($editcontrols = local_th_update_calculation_controls($context, $baseurl)) {
			echo $OUTPUT->render($editcontrols);
		}

		echo $OUTPUT->heading($title);
		$th_update_calculation_form->display();
		echo $OUTPUT->footer();
	}
}

if ($th_update_calculation_key) {
	$form2 = new confirm_form(null, array('th_update_calculation_key' => $th_update_calculation_key));

	if ($form2->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/blocks/th_search_calculation/th_update_calculation.php');
		redirect($courseurl);
	} else if ($formdata = $form2->get_data()) {
		if (!empty($th_update_calculation_key) && !empty($SESSION->th_update_calculation) &&
			array_key_exists($th_update_calculation_key, $SESSION->th_update_calculation)) {
			set_time_limit(600);
			$data = $SESSION->th_update_calculation[$th_update_calculation_key];

			$data_calculations = $data->data_calculation;

			foreach ($data_calculations as $data_calculation) {

				$id = $data_calculation->id;
				$courses = $data_calculation->courses;

				$grade_item = grade_item::fetch(array('id' => $id, 'courseid' => $courses->id));
				$calculation = calc_formula::localize($grade_item->calculation);
				$calculation = grade_item::denormalize_formula($calculation, $grade_item->courseid);
				$calculation = calc_formula::unlocalize($data_calculation->calculation_new);

				$grade_item->set_calculation($calculation);
				grade_regrade_final_grades($courses->id);

			}

			$link = "<a href='th_update_calculation.php'>tiếp tục cập nhật</a>";
			$link1 = $CFG->wwwroot . '/my';
			$home = "<a href='$link1'>trang chủ</a>";
			$wn = 'Cập nhật thành công bạn có muốn ' . $link . ' hoặc quay lại ' . $home;

			$notification = new \core\output\notification($wn
				,
				\core\output\notification::NOTIFY_WARNING);

			$notification->set_show_closebutton(false);

			echo $OUTPUT->header();
			echo $OUTPUT->heading('Cập nhật công thức hàng loạt');
			echo $OUTPUT->render($notification);
			echo $OUTPUT->footer();
		}
	} else {
		echo $OUTPUT->header();
		if (!empty($th_update_calculation_key) && !empty($checked)) {

			if (!empty($checked->error_messages)) {
				$errors = $checked->error_messages;
				$html1 = th_update_calculation_display_table_error($errors);
				echo $OUTPUT->heading('Gợi ý');
				echo '<style type="text/css">
				        #test{
				            width: 100%;
				            height:400px;
				            overflow-x:hidden;
				            overflow-y:auto;
				        }
				    </style>';
				echo '<div id="test">';
				echo $html1;
				echo '</div>';
			}

			if (!empty($checked->data_calculation)) {

				$data_calculation = $checked->data_calculation;

				$html = th_display_table_update_calculation($data_calculation);
				echo '<h2 class = "title"><center>Cập nhật công thức hàng loạt</center></h2>';
				echo $html;
				$lang = current_language();
				echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
				$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_update_calculation_table', 'Cập nhật công thức hàng loạt', $lang));
			}
		}

		// Show notification if there aren't any valid email addresses to enrol.
		if (!empty($checked) && isset($checked->validfound) &&
			empty($checked->validfound)) {
			$url = new moodle_url('/blocks/th_search_calculation/th_update_calculation.php');
			$wn = "Không tìm thấy giá trị hợp lệ.<br />Vui lòng <a href='$url'> quay lại và kiểm tra thông tin đầu vào của bạn.</a>";

			$notification = new \core\output\notification($wn
				,
				\core\output\notification::NOTIFY_WARNING);

			$notification->set_show_closebutton(false);
			echo $OUTPUT->render($notification);
		} else {
			echo $form2->display();
		}
		echo $OUTPUT->footer();
	}
}

?>