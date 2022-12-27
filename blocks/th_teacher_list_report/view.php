<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once 'th_teacher_list_report_form.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

// Check for all required variables.
$courseid = $COURSE->id;
$returnto = optional_param('returnto', 'course', PARAM_ALPHANUM); // Generic navigation return page switch.
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_teacher_list_report', $courseid);
}

require_login($courseid);
require_capability('block/th_teacher_list_report:view', context_course::instance($COURSE->id));

$pageurl = '/blocks/th_teacher_list_report/view.php';
$title = get_string('title', 'block_th_teacher_list_report');
$PAGE->set_url('/blocks/th_teacher_list_report/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_teacher_list_report', 'block_th_teacher_list_report'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_teacher_list_report'));

$editurl = new moodle_url('/blocks/th_teacher_list_report/view.php');
$settingsnode = $PAGE->navbar->add(get_string('breadcrumb', 'block_th_teacher_list_report'), $editurl);
$settingsnode->make_active();

$th_student_number = new th_teacher_list_report_form();

if ($th_student_number->is_cancelled()) {
	// Cancelled forms redirect to the course main page.
	$courseurl = new moodle_url('/my');
	redirect($courseurl);
} else if ($fromform = $th_student_number->get_data()) {

	if ($fromform->show_option == 0) {

		$ngay_mo = $fromform->ngay_mo;
		
		$list_course = $DB->get_records_sql("SELECT * FROM {course} WHERE startdate = $ngay_mo");

		$table = new html_table();
		$table->head = array('STT', 'Giảng viên', 'SĐT', 'Email', 'Khoa', 'Tên khóa học phụ trách', 'Ngày mở môn');
		$stt = 0;

		foreach($list_course as $course){
			$course_id = $course->id;
			$roleid = $DB->get_field_sql("SELECT id FROM {role} WHERE shortname = 'editingteacher'");
			$ds_giang_vien = $DB->get_records_sql("SELECT u.* FROM {enrol} as e, {user_enrolments} as ue, {user} as u, {context} as c, {role_assignments} as ra WHERE ue.status = 0 AND e.courseid = '$course_id' AND e.enrol = 'manual' AND e.id = ue.enrolid AND u.id = ue.userid AND c.instanceid = '$course_id' AND c.contextlevel = '50' AND c.id = ra.contextid AND ra.userid = u.id AND ra.roleid = '$roleid'");

			if (!empty($ds_giang_vien)) {

				foreach ($ds_giang_vien as $giang_vien) {

		        	$stt++;
					$row = new html_table_row();
					$cell = new html_table_cell($stt);
					$row->cells[] = $cell;
					$link = new moodle_url('/user/profile.php', ['id' => $giang_vien->id]);
					$link_user = html_writer::link($link, $giang_vien->firstname . ' ' . $giang_vien->lastname);
					$cell = new html_table_cell($link_user);
					$row->cells[] = $cell;
					$cell = new html_table_cell($giang_vien->phone2);
					$row->cells[] = $cell;
					$cell = new html_table_cell($giang_vien->email);
					$row->cells[] = $cell;
		        	$cell = new html_table_cell($giang_vien->department);
					$row->cells[] = $cell;
					$link1 = new moodle_url('/course/view.php', ['id' => $course->id]);
					$link_course = html_writer::link($link1, $course->fullname);
					$cell = new html_table_cell($link_course);
					$row->cells[] = $cell;
					$cell = new html_table_cell(date('d-m-Y', $course->startdate));
					$row->cells[] = $cell;
					$table->data[] = $row;
					        
				}
			}
		}

		$table->attributes = array('class' => 'th_teacher_list_report_table', 'border' => '1');
		$table->attributes['style'] = "width: 100%; text-align:center;";
		$html = html_writer::table($table);

		echo $OUTPUT->header();
		echo $OUTPUT->heading("<center>$title</center>");
		$th_student_number->display();
		echo $OUTPUT->heading("<center>Danh sách giảng viên phụ trách môn học</center>");
		echo $html;
		$lang = current_language();
		echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
		$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_teacher_list_report_table', 'LIST TEACHER', $lang));
		echo $OUTPUT->footer();

	}

	if ($fromform->show_option == 1) {

		$list_shortname = $fromform->course_id;
		$timestart = $fromform->startdate;
		$timeend = $fromform->enddate;

		$table = new html_table();
		$table->head = array('STT', 'Giảng viên', 'SĐT', 'Email', 'Khoa', 'Tên khóa học phụ trách', 'Ngày mở môn');
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
					$roleid = $DB->get_field_sql("SELECT id FROM {role} WHERE shortname = 'editingteacher'");
					$ds_giang_vien = $DB->get_records_sql("SELECT u.* FROM {enrol} as e, {user_enrolments} as ue, {user} as u, {context} as c, {role_assignments} as ra WHERE ue.status = 0 AND e.courseid = '$course_id' AND e.enrol = 'manual' AND e.id = ue.enrolid AND u.id = ue.userid AND c.instanceid = '$course_id' AND c.contextlevel = '50' AND c.id = ra.contextid AND ra.userid = u.id AND ra.roleid = '$roleid'");

					if (!empty($ds_giang_vien)) {

						foreach ($ds_giang_vien as $giang_vien) {

				        	$stt++;
							$row = new html_table_row();
							$cell = new html_table_cell($stt);
							$row->cells[] = $cell;
							$link = new moodle_url('/user/profile.php', ['id' => $giang_vien->id]);
							$link_user = html_writer::link($link, $giang_vien->firstname . ' ' . $giang_vien->lastname);
							$cell = new html_table_cell($link_user);
							$row->cells[] = $cell;
							$cell = new html_table_cell($giang_vien->phone2);
							$row->cells[] = $cell;
							$cell = new html_table_cell($giang_vien->email);
							$row->cells[] = $cell;
				        	$cell = new html_table_cell($giang_vien->department);
							$row->cells[] = $cell;
							$link1 = new moodle_url('/course/view.php', ['id' => $course->id]);
							$link_course = html_writer::link($link1, $course->fullname);
							$cell = new html_table_cell($link_course);
							$row->cells[] = $cell;
							$cell = new html_table_cell(date('d-m-Y', $course->startdate));
							$row->cells[] = $cell;
							$table->data[] = $row;
							        
						}
					}
				}
			}
		}

		$table->attributes = array('class' => 'th_teacher_list_report_table', 'border' => '1');
		$table->attributes['style'] = "width: 100%; text-align:center;";
		$html = html_writer::table($table);

		echo $OUTPUT->header();
		echo $OUTPUT->heading("<center>$title</center>");
		$th_student_number->display();
		echo $OUTPUT->heading("<center>Danh sách giảng viên phụ trách môn học</center>");
		echo $html;
		$lang = current_language();
		echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
		$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_teacher_list_report_table', 'LIST COURSE', $lang));
		echo $OUTPUT->footer();
	}

	if ($fromform->show_option == 2) {

		$timestart = $fromform->startdate;
		$timeend = $fromform->enddate;
		$ds_giang_vien = $fromform->giang_vien;

		if (empty($ds_giang_vien)) {
			$sql = "SELECT u.id,firstname,lastname,email,d.data
                FROM {user_info_data} d
                JOIN {user} u ON d.userid = u.id
                JOIN {user_info_field} f ON d.fieldid = f.id
                WHERE d.data LIKE 'Giảng viên' AND u.deleted = 0 AND u.suspended = 0";
	        $teachers = $DB->get_records_sql($sql);
	        $ds_giang_vien = array();
	        foreach ($teachers as $key => $teacher) {
	            $ds_giang_vien[] = $key;
	        }
		}

		$table = new html_table();
		$table->head = array('STT', 'Giảng viên', 'SĐT', 'Email', 'Khoa', 'Tên khóa học phụ trách', 'Ngày mở môn');
		$stt = 0;

		foreach ($ds_giang_vien as $giang_vien_id) {
        	$giang_vien = $DB->get_record_sql("SELECT * FROM {user} WHERE id = $giang_vien_id");

			$roleid = $DB->get_field_sql("SELECT id FROM {role} WHERE shortname = 'editingteacher'");
			$listcourses = $DB->get_records_sql("SELECT co.* FROM {enrol} as e, {user_enrolments} as ue, {context} as c, {role_assignments} as ra, {course} as co WHERE ue.status = 0 AND e.courseid = co.id AND e.enrol = 'manual' AND e.id = ue.enrolid AND ue.userid = '$giang_vien_id' AND c.instanceid = co.id AND c.contextlevel = '50' AND c.id = ra.contextid AND ra.userid = '$giang_vien_id' AND ra.roleid = '$roleid' AND co.startdate >= '$timestart' AND co.startdate <= '$timeend'");

        	foreach ($listcourses as $course) {
        		$stt++;
				$row = new html_table_row();
				$cell = new html_table_cell($stt);
				$row->cells[] = $cell;
				$link = new moodle_url('/user/profile.php', ['id' => $giang_vien->id]);
				$link_user = html_writer::link($link, $giang_vien->firstname . ' ' . $giang_vien->lastname);
				$cell = new html_table_cell($link_user);
				$row->cells[] = $cell;
				$cell = new html_table_cell($giang_vien->phone2);
				$row->cells[] = $cell;
				$cell = new html_table_cell($giang_vien->email);
				$row->cells[] = $cell;
	        	$cell = new html_table_cell($giang_vien->department);
				$row->cells[] = $cell;
				$link1 = new moodle_url('/course/view.php', ['id' => $course->id]);
				$link_course = html_writer::link($link1, $course->fullname);
				$cell = new html_table_cell($link_course);
				$row->cells[] = $cell;
				$cell = new html_table_cell(date('d-m-Y', $course->startdate));
				$row->cells[] = $cell;
				$table->data[] = $row;
        	}		        
		}

		$table->attributes = array('class' => 'th_teacher_list_report_table', 'border' => '1');
		$table->attributes['style'] = "width: 100%; text-align:center;";
		$html = html_writer::table($table);

		echo $OUTPUT->header();
		echo $OUTPUT->heading("<center>$title</center>");
		$th_student_number->display();
		echo $OUTPUT->heading("<center>Danh sách giảng viên phụ trách môn học</center>");
		echo $html;
		$lang = current_language();
		echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
		$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_teacher_list_report_table', 'LIST COURSE', $lang));
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
		$('#fitem_id_giang_vien .col-form-label').removeAttr('hidden');
		$('#fitem_id_giang_vien .col-form-label .word-break').removeAttr('hidden');
    });
});
</script>

