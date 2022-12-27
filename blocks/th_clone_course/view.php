<?php

use \block_th_clone_course\libs;
use \core_backup\copy;
require_once '../../config.php';
require_once 'blocklib.php';
require_once 'th_clone_course_form.php';
require_once 'confirm_form.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/' . $CFG->admin . '/tool/uploaduser/locallib.php';
require_once $CFG->dirroot . '/' . $CFG->admin . '/tool/uploaduser/user_form.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

$th_clone_csvkey = optional_param('key', 0, PARAM_ALPHANUMEXT);

// Check for all required variables.
$courseid = $COURSE->id;
$returnto = optional_param('returnto', 'course', PARAM_ALPHANUM); // Generic navigation return page switch.
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_clone_course', $courseid);
}

require_login($courseid);
require_capability('block/th_clone_course:view', context_course::instance($COURSE->id));

$pageurl = '/blocks/th_clone_course/view.php';
$title = get_string('copycoursetitle', 'block_th_clone_course');
$PAGE->set_url('/blocks/th_clone_course/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_clone_course', 'block_th_clone_course'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_clone_course'));
$PAGE->requires->js_call_amd('local_thlib/main', 'addAsteriskToCustomRequiredFieldForm', array($CFG->wwwroot));

$editurl = new moodle_url('/blocks/th_clone_course/view.php');
$settingsnode = $PAGE->navbar->add(get_string('breadcrumb', 'block_th_clone_course'), $editurl);
$settingsnode->make_active();
// admin_externalpage_setup('th_clone_course');

if (empty($th_clone_csvkey)) {

	$th_clone_course = new th_clone_course_form();

	if ($th_clone_course->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/my');
		redirect($courseurl);
	} else if ($fromform = $th_clone_course->get_data()) {

		$course_id = [];
		if ($fromform) {
			$course_id = $fromform->course_id;
		}

		if (sizeof($course_id)) {
			$a = count($course_id);
			$table1 = new html_table();
			$table1->head = array('STT', 'Fullname', 'Notifications');

			for ($i = 0; $i < $a; ++$i) {

				$libs = new libs();
				$startdate = $fromform->startdate;
				$course_id = $fromform->course_id[$i];
				$full_name = $libs->get_fullname($course_id);
				$short_name = $libs->get_shortname($course_id);
				$category = $libs->get_category($course_id);
				$date = date('ymd', $startdate);

				if ($returnurl != '') {
					$returnurl = new moodle_url($returnurl);
				} else if ($returnto == 'catmanage') {
					// Redirect to category management page.
					$returnurl = new moodle_url('/course/management.php', array('categoryid' => $course->category));
				} else {
					// Redirect back to course page if we came from there.
					$returnurl = new moodle_url('/course/view.php', array('id' => $course_id));
				}

				$str1 = substr($full_name, -6);
				$str2 = substr($full_name, -9, 3);
				$str3 = substr($short_name, -6);
				$str4 = substr($short_name, -9, 3);
				$str5 = substr($short_name, -7, 1);

				if ((int) $str1 == '0' && $str2 != ' - ' || (int) $str3 == '0' || $str5 != '-' || $str4 == ' - ') {
					$full_name_new = $full_name;
					$short_name_new = $short_name;
				} else {
					$pos = strripos($full_name, '-', 0);
					$pos1 = strripos($short_name, '-', 0);
					$full_name_new = substr($full_name, 0, $pos - 1);
					$short_name_new = substr($short_name, 0, $pos1);
				}

				$fromform->returnto = $returnto;
				$fromform->courseid = $course_id;
				$fromform->returnurl = $returnurl;
				$fromform->fullname = $full_name_new . ' - ' . $date;
				$fromform->shortname = $short_name_new . '-' . $date;
				$fromform->category = $category;
				$fromform->idnumber = $short_name_new . '-' . $date;
				$fromform->visible = 0;
				$fromform->visibleold = 0;
				$fromform->enddate = 0;
				$fromform->userdata = 0;

				$fullname = $fromform->fullname;
				$sql = "SELECT fullname FROM {course} WHERE fullname REGEXP BINARY '^$fullname$'";
				$arr_name = $DB->get_records_sql($sql);

				$copies = \core_backup\copy\copy::get_copies($USER->id, $course_id);

				if ((int) $str1 == '0' && $str2 != ' - ' || (int) $str3 == '0' || $str5 != '-' || $str4 == ' - ') {
					$fromform->status = 0;
				} else if ($DB->record_exists_sql($sql) == 1 || $str1 == $date) {
					$fromform->status = 1;
				} else if (!empty($copies)) {
					$fromform->status = 3;
				} else {
					$fromform->status = 2;
					$backupcopy = new \core_backup\copy\copy($fromform);
					$backupcopy->create_copy();
				}

				if ($fromform->status == 1) {
					$status = 'Khóa học bạn muốn copy đã tồn tại';
				} else if ($fromform->status == 0) {
					$status = 'Khóa học ban đầu không đúng định dạng';
				} else if ($fromform->status == 3) {
					$status = 'Khóa học ban đầu đang được copy';
				} else {
					$status = 'Copy thành công.Tên Khóa học mới là: ' . $fullname . '';
				}

				$stt = $i + 1;
				$id = $libs->get_id($full_name);
				$link = new moodle_url('/course/edit.php', ['id' => $id]);
				$link_edit = html_writer::link($link, $full_name);

				$row = new html_table_row();
				$cell = new html_table_cell($stt);
				$row->cells[] = $cell;
				$cell = new html_table_cell($link_edit);
				$row->cells[] = $cell;
				$cell = new html_table_cell($status);
				$row->cells[] = $cell;

				if ($fromform->status == 0 || $fromform->status == 1 || $fromform->status == 3) {
					$cell->attributes = array('class' => "bg-danger");
				} else {
					$cell->attributes = array('class' => "bg-success");
				}

				$table1->data[] = $row;

			}
			$table1->attributes = array('class' => 'th_clone_course_table', 'border' => '1');
			$table1->attributes['style'] = "width: 100%; text-align:center;";
			$html = html_writer::table($table1);
			echo $OUTPUT->header();
			echo $OUTPUT->heading($title);
			echo "</br>";
			$th_clone_course->display();
			echo "</br>";
			echo $OUTPUT->heading(get_string('copycourseprocess', 'block_th_clone_course'));
			echo "</br>";
			echo $html;
			$lang = current_language();

			echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
			$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_clone_course_table', 'COPY KHÓA HỌC', $lang));
			echo "</br>";
			echo $OUTPUT->footer();
		} else {
			$content = $th_clone_course->get_file_content('listcourses');
			$contents = th_clone_csv_parse_course($content);

			$listcourses_new = th_get_content($contents);

			$checkedcourses = th_clone_csv_check_courses($listcourses_new);

			// print_object($checkedcourses);
			// exit;

			// Save data in Session.
			$th_clone_csvkey = $courseid . '_' . time();
			$SESSION->block_th_clone_csv[$th_clone_csvkey] = $checkedcourses;

		}

	} else {
		// form didn't validate or this is the first display
		echo $OUTPUT->header();
		echo $OUTPUT->heading($title);
		echo "</br>";
		$th_clone_course->display();
		echo $OUTPUT->footer();
	}
}

if ($th_clone_csvkey) {
	$form2 = new confirm_form(null, array('th_clone_csvkey' => $th_clone_csvkey));

	if ($form2->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/blocks/th_clone_course/view.php');
		redirect($courseurl);

	} else if ($formdata = $form2->get_data()) {
		if (!empty($th_clone_csvkey) && !empty($SESSION->block_th_clone_csv) &&
			array_key_exists($th_clone_csvkey, $SESSION->block_th_clone_csv)) {

			$data = $SESSION->block_th_clone_csv[$th_clone_csvkey];
			$courses_copy = $data->courses_copy;

			foreach ($courses_copy as $k => $course_copy) {

				$course_id = $course_copy->id;
				$category = get_category($course_id);
				$full_name = $course_copy->fullname;
				$short_name = $course_copy->shortname;
				$pos = strripos($full_name, '-', 0);
				$pos1 = strripos($short_name, '-', 0);
				$full_name_new = substr($full_name, 0, $pos - 1);
				$short_name_new = substr($short_name, 0, $pos1);
				$date = $course_copy->startdate1;

				$form = new stdClass();
				$form->courseid = $course_id;
				$form->fullname = $full_name_new . ' - ' . $date;
				$form->shortname = $short_name_new . '-' . $date;
				$form->category = $category;
				$form->idnumber = $short_name_new . '-' . $date;
				$form->visible = 0;
				$form->visibleold = 0;
				$form->enddate = 0;
				$form->userdata = 0;
				$form->startdate = $course_copy->startdate_timstamp;

				$copies = \core_backup\copy\copy::get_copies($USER->id, $course_id);

				if (empty($copies)) {
					$backupcopy = new \core_backup\copy\copy($form);
					$backupcopy->create_copy();
				}
			}

			$link = "<a href='view.php'>tiếp tục clone khóa học</a>";
			$link1 = $CFG->wwwroot . '/my';
			$home = "<a href='$link1'>trang chủ</a>";
			$wn = 'Clone khóa học thành công bạn có muốn ' . $link . ' hoặc quay lại ' . $home;

			$notification = new \core\output\notification($wn
				,
				\core\output\notification::NOTIFY_WARNING);

			$notification->set_show_closebutton(false);

			echo $OUTPUT->header();
			echo $OUTPUT->heading(get_string('pluginname', 'block_th_clone_course'));
			echo $OUTPUT->render($notification);
			echo $OUTPUT->footer();
		}

	} else {
		echo $OUTPUT->header();
		echo $OUTPUT->heading(get_string('pluginname', 'block_th_clone_course'));
		if (!empty($th_clone_csvkey) && !empty($SESSION->block_th_clone_csv) &&
			array_key_exists($th_clone_csvkey, $SESSION->block_th_clone_csv)) {

			$blockth_clone_csvdata = $SESSION->block_th_clone_csv[$th_clone_csvkey];

			if (!empty($blockth_clone_csvdata->error_messages)) {

				$errors = $blockth_clone_csvdata->error_messages;

				$html1 = th_display_table_error($errors);
				echo $OUTPUT->heading(get_string('Hints', 'block_th_clone_course'));
				echo $html1;

			}

			if (!empty($blockth_clone_csvdata->courses_copy)) {

				$courses_copy = $blockth_clone_csvdata->courses_copy;

				$html = th_display_table_clone($courses_copy);
				echo $OUTPUT->heading('CÁC KHÓA HỌC SẼ ĐƯỢC CLONE');
				echo $html;
			}
		}

		if (!empty($blockth_clone_csvdata) && $blockth_clone_csvdata->validemailfound > 10) {
			$a = new stdClass();
			$url = new moodle_url('/blocks/th_clone_course/view.php');
			$a->url = $url->out();

			$wn = get_string('error_max_shortname_in_list', 'block_th_clone_course', $a);

			$notification = new \core\output\notification($wn
				,
				\core\output\notification::NOTIFY_WARNING);

			$notification->set_show_closebutton(false);
			echo $OUTPUT->render($notification);
		}

		if (!empty($blockth_clone_csvdata) && isset($blockth_clone_csvdata->validemailfound) &&
			empty($blockth_clone_csvdata->validemailfound)) {
			$a = new stdClass();
			$url = new moodle_url('/blocks/th_clone_course/view.php');
			$a->url = $url->out();

			$wn = get_string('error_no_valid_shortname_in_list', 'block_th_clone_course', $a);

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

<script type="text/javascript">
    $(document).ready(function() {
	var element = document.getElementById('fitem_id_example');
	element.setAttribute("hidden", "hidden");

    $('input[type=radio][value=0]').change(function() {
		element.setAttribute("hidden", "hidden");
    });

    $('input[type=radio][value=1]').change(function() {
		element.removeAttribute("hidden");
    });
});
</script>

