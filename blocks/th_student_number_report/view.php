<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once 'th_student_number_report_form.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

// Check for all required variables.
$courseid = $COURSE->id;
$returnto = optional_param('returnto', 'course', PARAM_ALPHANUM); // Generic navigation return page switch.
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_student_number_report', $courseid);
}

require_login($courseid);
require_capability('block/th_student_number_report:view', context_course::instance($COURSE->id));

$pageurl = '/blocks/th_student_number_report/view.php';
$title = get_string('title', 'block_th_student_number_report');
$PAGE->set_url('/blocks/th_student_number_report/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_student_number_report', 'block_th_student_number_report'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_student_number_report'));

$editurl = new moodle_url('/blocks/th_student_number_report/view.php');
$settingsnode = $PAGE->navbar->add(get_string('breadcrumb', 'block_th_student_number_report'), $editurl);
$settingsnode->make_active();

$th_student_number = new th_student_number_report_form();

if ($th_student_number->is_cancelled()) {
	// Cancelled forms redirect to the course main page.
	$courseurl = new moodle_url('/my');
	redirect($courseurl);
} else if ($fromform = $th_student_number->get_data()) {

	if ($fromform->show_option == 0) {

		$list_shortname = $fromform->course_id;
		$timestart = $fromform->startdate;
		$timeend = $fromform->enddate;

		$table = new html_table();
		$table->head = array('STT', 'Tên môn', 'Ngày mở môn', 'Ngành học', 'Số học viên');
		$stt = 0;

		if (empty($list_shortname)) {
			$list_course = ds_course();

			$list_shortname = [];
			
			foreach($list_course as $k => $shortname){
				$pos = strpos($shortname, '-');
				
				if ($pos !== false) {
					$shortname_arr = explode('-', $shortname);
					$shortname = $shortname_arr[0];
				}

				if (!in_array($shortname, $list_shortname)){
					$list_shortname[] = $shortname;
				}
			}
		}

		foreach($list_shortname as $shortname){

			$listcourses = $DB->get_records_sql("SELECT * FROM {course} WHERE shortname LIKE '$shortname%' AND NOT category = 1 AND NOT id = 1 AND visible <> 0 AND  fullname NOT LIKE '% - mẫu' AND startdate >= '$timestart' AND startdate <= '$timeend'");

			if (!empty($listcourses)) {

				foreach ($listcourses as $course) {
					$course_id = $course->id;
					$id_nganh = $course->category;
					$nganh = $DB->get_record_sql("SELECT * FROM {course_categories} WHERE id = '$id_nganh'"); 
					$so_luong = list_student_of_course($course_id);
					switch ($fromform->option) {
					    case 0:
					        if($so_luong > $fromform->so_luong) {
					        	$stt++;
								$row = new html_table_row();
								$cell = new html_table_cell($stt);
								$row->cells[] = $cell;
								$link = new moodle_url('/user/index.php', ['id' => $course_id]);
								$link_course = html_writer::link($link, $course->fullname);
								$cell = new html_table_cell($link_course);
								$row->cells[] = $cell;
								$cell = new html_table_cell(date('d-m-Y', $course->startdate));
								$row->cells[] = $cell;
								$cell = new html_table_cell($nganh->name);
								$row->cells[] = $cell;
					        	$cell = new html_table_cell($so_luong);
								$row->cells[] = $cell;
								$table->data[] = $row;
					        }
					        
					        break;
					    case 1:
					        if($so_luong < $fromform->so_luong) {
					        	$stt++;
								$row = new html_table_row();
								$cell = new html_table_cell($stt);
								$row->cells[] = $cell;
								$link = new moodle_url('/user/index.php', ['id' => $course_id]);
								$link_course = html_writer::link($link, $course->fullname);
								$cell = new html_table_cell($link_course);
								$row->cells[] = $cell;
								$cell = new html_table_cell(date('d-m-Y', $course->startdate));
								$row->cells[] = $cell;
								$cell = new html_table_cell($nganh->name);
								$row->cells[] = $cell;
					        	$cell = new html_table_cell($so_luong);
								$row->cells[] = $cell;
								$table->data[] = $row;
					        }
					        break;
					    case 2:
					        if($so_luong == $fromform->so_luong) {
					        	$stt++;
								$row = new html_table_row();
								$cell = new html_table_cell($stt);
								$row->cells[] = $cell;
								$link = new moodle_url('/user/index.php', ['id' => $course_id]);
								$link_course = html_writer::link($link, $course->fullname);
								$cell = new html_table_cell($link_course);
								$row->cells[] = $cell;
								$cell = new html_table_cell(date('d-m-Y', $course->startdate));
								$row->cells[] = $cell;
								$cell = new html_table_cell($nganh->name);
								$row->cells[] = $cell;
					        	$cell = new html_table_cell($so_luong);
								$row->cells[] = $cell;
								$table->data[] = $row;
					        }
					        break;
					}
				}
			}
		}

		$table->attributes = array('class' => 'th_student_number_report_table', 'border' => '1');
		$table->attributes['style'] = "width: 100%; text-align:center;";
		$html = html_writer::table($table);

		echo $OUTPUT->header();
		echo $OUTPUT->heading("<center>$title</center>");
		$th_student_number->display();
		echo $OUTPUT->heading("<center>Số lượng học viên theo môn học</center>");
		echo $html;
		$lang = current_language();
		echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
		$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_student_number_report_table', 'LIST COURSE', $lang));
		echo $OUTPUT->footer();

	}

	if ($fromform->show_option == 1) {

		$ds_nganh = $fromform->nganh;
		$timestart = $fromform->startdate;
		$timeend = $fromform->enddate;

		$table = new html_table();
		$table->head = array('STT', 'Tên môn', 'Ngày mở môn', 'Ngành học', 'Số học viên');
		$stt = 0;

		if (empty($ds_nganh)) {
			$ds_nganh = ds_nganh2();
		}

		foreach($ds_nganh as $nganh){

			$id_nganh = $nganh;
			$nganh = $DB->get_record_sql("SELECT * FROM {course_categories} WHERE id = '$id_nganh'");
			$listcourses = $DB->get_records_sql("SELECT * FROM {course} WHERE category = '$id_nganh' AND startdate >= '$timestart' AND startdate <= '$timeend' AND NOT id = 1 AND NOT category = 1 AND visible <> 0 AND fullname NOT LIKE '% - mẫu'");

			foreach ($listcourses as $course) {
				$course_id = $course->id;
				$so_luong = list_student_of_course($course_id);
				switch ($fromform->option) {
				    case 0:
				        if($so_luong > $fromform->so_luong) {
				        	$stt++;
							$row = new html_table_row();
							$cell = new html_table_cell($stt);
							$row->cells[] = $cell;
							$link = new moodle_url('/user/index.php', ['id' => $course_id]);
							$link_course = html_writer::link($link, $course->fullname);
							$cell = new html_table_cell($link_course);
							$row->cells[] = $cell;
							$cell = new html_table_cell(date('d-m-Y', $course->startdate));
							$row->cells[] = $cell;
							$cell = new html_table_cell($nganh->name);
							$row->cells[] = $cell;
				        	$cell = new html_table_cell($so_luong);
							$row->cells[] = $cell;
							$table->data[] = $row;
				        }
				        break;
				    case 1:
				        if($so_luong < $fromform->so_luong) {
				        	$stt++;
							$row = new html_table_row();
							$cell = new html_table_cell($stt);
							$row->cells[] = $cell;
							$link = new moodle_url('/user/index.php', ['id' => $course_id]);
							$link_course = html_writer::link($link, $course->fullname);
							$cell = new html_table_cell($link_course);
							$row->cells[] = $cell;
							$cell = new html_table_cell(date('d-m-Y', $course->startdate));
							$row->cells[] = $cell;
							$cell = new html_table_cell($nganh->name);
							$row->cells[] = $cell;
				        	$cell = new html_table_cell($so_luong);
							$row->cells[] = $cell;
							$table->data[] = $row;
				        }
				        break;
				    case 2:
				        if($so_luong == $fromform->so_luong) {
				        	$stt++;
							$row = new html_table_row();
							$cell = new html_table_cell($stt);
							$row->cells[] = $cell;
							$link = new moodle_url('/user/index.php', ['id' => $course_id]);
							$link_course = html_writer::link($link, $course->fullname);
							$cell = new html_table_cell($link_course);
							$row->cells[] = $cell;
							$cell = new html_table_cell(date('d-m-Y', $course->startdate));
							$row->cells[] = $cell;
							$cell = new html_table_cell($nganh->name);
							$row->cells[] = $cell;
				        	$cell = new html_table_cell($so_luong);
							$row->cells[] = $cell;
							$table->data[] = $row;
				        }
				        break;
				}
			}
		}

		$table->attributes = array('class' => 'th_student_number_report_table', 'border' => '1');
		$table->attributes['style'] = "width: 100%; text-align:center;";
		$html = html_writer::table($table);

		echo $OUTPUT->header();
		echo $OUTPUT->heading("<center>$title</center>");
		$th_student_number->display();
		echo $OUTPUT->heading("<center>Số lượng học viên theo ngành</center>");
		echo $html;
		$lang = current_language();
		echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
		$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_student_number_report_table', 'LIST COURSE', $lang));
		echo $OUTPUT->footer();
	}

	if ($fromform->show_option == 2) {

		$timestart = $fromform->startdate;
		$timeend = $fromform->enddate;

		$listcourses = $DB->get_records_sql("SELECT c.* FROM {course} as c, {course_categories} as ca WHERE c.startdate >= '$timestart' AND c.startdate <= '$timeend' AND NOT c.id = 1 AND NOT c.category = 1 AND c.visible <> 0 AND c.fullname NOT LIKE '% - mẫu' AND c.category = ca.id AND ca.visible = 1");

		$table = new html_table();
		$table->head = array('STT', 'Tên môn', 'Ngày mở môn', 'Ngành học', 'Số học viên');
		$stt = 0;

		foreach ($listcourses as $course) {
			$course_id = $course->id;
			$id_nganh = $course->category;
			$nganh = $DB->get_record_sql("SELECT * FROM {course_categories} WHERE id = '$id_nganh'");
			$so_luong = list_student_of_course($course_id);
			
			switch ($fromform->option) {
			    case 0:
			        if($so_luong > $fromform->so_luong) {
			        	$stt++;
						$row = new html_table_row();
						$cell = new html_table_cell($stt);
						$row->cells[] = $cell;
						$link = new moodle_url('/user/index.php', ['id' => $course_id]);
						$link_course = html_writer::link($link, $course->fullname);
						$cell = new html_table_cell($link_course);
						$row->cells[] = $cell;
						$cell = new html_table_cell(date('d-m-Y', $course->startdate));
						$row->cells[] = $cell;
						$cell = new html_table_cell($nganh->name);
						$row->cells[] = $cell;
			        	$cell = new html_table_cell($so_luong);
						$row->cells[] = $cell;
						$table->data[] = $row;
			        }
			        break;
			    case 1:
			        if($so_luong < $fromform->so_luong) {
			        	$stt++;
						$row = new html_table_row();
						$cell = new html_table_cell($stt);
						$row->cells[] = $cell;
						$link = new moodle_url('/user/index.php', ['id' => $course_id]);
						$link_course = html_writer::link($link, $course->fullname);
						$cell = new html_table_cell($link_course);
						$row->cells[] = $cell;
						$cell = new html_table_cell(date('d-m-Y', $course->startdate));
						$row->cells[] = $cell;
						$cell = new html_table_cell($nganh->name);
						$row->cells[] = $cell;
			        	$cell = new html_table_cell($so_luong);
						$row->cells[] = $cell;
						$table->data[] = $row;
			        }
			        break;
			    case 2:
			        if($so_luong == $fromform->so_luong) {
			        	$stt++;
						$row = new html_table_row();
						$cell = new html_table_cell($stt);
						$row->cells[] = $cell;
						$link = new moodle_url('/user/index.php', ['id' => $course_id]);
						$link_course = html_writer::link($link, $course->fullname);
						$cell = new html_table_cell($link_course);
						$row->cells[] = $cell;
						$cell = new html_table_cell(date('d-m-Y', $course->startdate));
						$row->cells[] = $cell;
						$cell = new html_table_cell($nganh->name);
						$row->cells[] = $cell;
			        	$cell = new html_table_cell($so_luong);
						$row->cells[] = $cell;
						$table->data[] = $row;
			        }
			        break;
			}
		}

		$table->attributes = array('class' => 'th_student_number_report_table', 'border' => '1');
		$table->attributes['style'] = "width: 100%; text-align:center;";
		$html = html_writer::table($table);

		echo $OUTPUT->header();
		echo $OUTPUT->heading("<center>$title</center>");
		$th_student_number->display();
		echo $OUTPUT->heading("<center>Số lượng học viên theo đợt mở</center>");
		echo $html;
		$lang = current_language();
		echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
		$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_student_number_report_table', 'LIST COURSE', $lang));
		echo $OUTPUT->footer();
	}

} else {
	echo $OUTPUT->header();
	echo $OUTPUT->heading("<center>$title</center>");
	$th_student_number->display();
	echo $OUTPUT->footer();
}

?>

<script type="text/javascript">
    $(document).ready(function() {
    $('input[type=radio][name=show_option]').change(function() {
		$('#fitem_id_course_id .col-form-label').removeAttr('hidden');
		$('#fitem_id_course_id .col-form-label .word-break').removeAttr('hidden');
		$('#fitem_id_nganh .col-form-label').removeAttr('hidden');
		$('#fitem_id_nganh .col-form-label .word-break').removeAttr('hidden');
    });
});
</script>

