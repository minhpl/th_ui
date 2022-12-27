<?php

require_once '../../config.php';
require_once 'blocklib.php';
require_once 'confirm_form.php';
require_once $CFG->dirroot . '/enrol/manual/locallib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';
require_once 'th_bulk_enrol_student_form.php';
include 'classes/PHPExcel/IOFactory.php';
include 'classes/PHPExcel.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $_FILES;

$th_bulkenrol_csvkey = optional_param('key', 0, PARAM_ALPHANUMEXT);

// Check for all required variables.
$courseid = $COURSE->id;

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_bulk_enrol_student', $courseid);
}

require_login($courseid);
require_capability('block/th_bulk_enrol_student:view', context_course::instance($COURSE->id));
$context = context_system::instance();
$pageurl = '/blocks/th_bulk_enrol_student/view.php';
$title = get_string('enrolcoursetitle', 'block_th_bulk_enrol_student');
$PAGE->set_url('/blocks/th_bulk_enrol_student/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_bulk_enrol_student', 'block_th_bulk_enrol_student'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_bulk_enrol_student'));

$editurl = new moodle_url('/blocks/th_bulk_enrol_student/view.php');
$settingsnode = $PAGE->settingsnav->add(get_string('breadcrumb', 'block_th_bulk_enrol_student'), $editurl);
$settingsnode->make_active();

if (!$enrol_manual = enrol_get_plugin('manual')) {
	throw new coding_exception('Can not instantiate enrol_manual');
}

if (empty($th_bulkenrol_csvkey)) {

	$path = $CFG->dataroot;
	if (!file_exists($path . '/th_enrol_student_uploads')) {
		mkdir($path . '/th_enrol_student_uploads', 0744);
	}

	$link_home = $CFG->wwwroot . '/my';

	echo $OUTPUT->header();

	$baseurl = new moodle_url('/blocks/th_bulk_enrol_student/view.php');
	if ($editcontrols = local_th_register_course_controls($context, $baseurl)) {
		echo $OUTPUT->render($editcontrols);
	}

	echo $OUTPUT->heading("<center>$title</center>");
	echo "</br>";
	echo "<div class = 'title1' id = 'title1'>
			Tệp tin mẫu:   <a href='example.xlsx'>example.xlsx</a>
			<form action='' method='post' enctype='multipart/form-data'>
			    <div style='border-top: 1px solid #d3d3e1; border-bottom: 1px solid #d3d3e1; height: 45px'>
			    	<div style='display:flex'>
			    		<label style='margin-top:10px'>Tệp tin:</label>
	                	<input type='file' name='fileUpload' value='' style='margin-top: 6px;''>
			    	</div>
			    </div>
			    <div style='margin-top:10px ; margin-left:82px'>
			    	<input style='color:#ffff' type='submit' name='up' value='Upload' onclick='click_Function()'>
			    	<a href='$link_home' style='color:#ffff;background-color:#c23a34;padding:7px 18px;position: relative;top: -3px;margin-left: 5px;''  id='cancel1'>Hủy bỏ</a>
			    </div>
			    <p><strong>Lưu ý:</strong> Chỉ cho phép định dạng .xlxs</p>
			</form>
		</div>";

	if (isset($_POST['up']) && isset($_FILES['fileUpload'])) {
		if ($_FILES['fileUpload']['error'] > 0) {
			echo '<strong>Không có dữ liệu upload</strong>';
			echo '<script type ="text/JavaScript">alert("Không có dữ liệu upload")</script>';
		} else {
			move_uploaded_file($_FILES['fileUpload']['tmp_name'], $path . '/th_enrol_student_uploads/' . $_FILES['fileUpload']['name']);

			$inputFileName = $path . '/th_enrol_student_uploads/' . $_FILES['fileUpload']['name'];

			try {

				$FileType = pathinfo($inputFileName, PATHINFO_EXTENSION);
				$allowtypes = array('xlsx');

				$allowUpload = true;

				// Kiểm tra kiểu file
				if (!in_array($FileType, $allowtypes)) {
					echo "<strong>Chỉ được upload các định dạng .xlsx</strong>";
					echo '<script type ="text/JavaScript">alert("Chỉ được upload các định dạng .xlsx")</script>';
					$allowUpload = false;
				}

				if ($allowUpload) {
					$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
					$objReader = PHPExcel_IOFactory::createReader($inputFileType);
					$objPHPExcel = $objReader->load($inputFileName);

					$allsheet = $objPHPExcel->getAllSheets();
					$a = $objPHPExcel->getActiveSheet();

					//Dem so luong sheet.
					$count_sheet = count($allsheet);

					for ($i = 0; $i < $count_sheet; ++$i) {

						$sheet = $objPHPExcel->getSheet($i);
						$highestRow = $sheet->getHighestRow();

						$allsheet_name = $objPHPExcel->getSheetNames();
						$sheet_name = $allsheet_name[$i];

						// Lấy tổng số cột của file.
						$highestColumn = $sheet->getHighestColumn();
						//  Thực hiện việc lặp qua từng dòng của file, để lấy thông tin
						for ($row = 2; $row <= $highestRow; $row++) {
							// Lấy dữ liệu từng dòng và đưa vào mảng $rowData

							$rowData[$i] = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
							$shortname = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1, NULL, TRUE, FALSE);
							$email[] = [$rowData[$i][0][0], $sheet_name, $shortname[0][0], $row];
						}
					}

					$emails = new stdClass();
					$emails = $email;

					$checkedemails = th_bulkenrol_excel_check_user_mails($emails);

					// Save data in Session.
					$th_bulkenrol_csvkey = $courseid . '_' . time();
					$SESSION->th_bulkenrol_csv[$th_bulkenrol_csvkey] = $checkedemails;

					$form2 = new confirm_form(null, array('th_bulkenrol_csv_key' => $th_bulkenrol_csvkey));

					if (!empty($th_bulkenrol_csvkey) && !empty($checkedemails)) {

						if (!empty($checkedemails->user_enroled) || !empty($checkedemails->moodleusers_for_email) || !empty($checkedemails->error_messages)) {

							$enroleds = $checkedemails->moodleusers_for_email;
							$no_enroleds = $checkedemails->user_enroled;
							$error_arrays = $checkedemails->error_arrays;

							$html = th_display_table_enrol($enroleds, $no_enroleds, $error_arrays);
							echo '<h2 class = "title"><center>DANH SÁCH GHI DANH HỌC VIÊN</center></h2>';
							echo $html;
							$lang = current_language();
							echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
							$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_enrol_table', 'BÁO CÁO GHI DANH THEO LÔ HỌC VIÊN VÀO KHÓA HỌC', $lang));
						}
					}

					// Show notification if there aren't any valid email addresses to enrol.
					if (!empty($checkedemails) && isset($checkedemails->validemailfound) &&
						empty($checkedemails->validemailfound)) {
						$a = new stdClass();
						$url = new moodle_url('/blocks/th_bulk_enrol_student/view.php', array('id' => $courseid, 'editlist' => $th_bulkenrol_csvkey));
						$a->url = $url->out();

						$wn = get_string('error_no_valid_email_in_list', 'block_th_bulk_enrol_student', $a);

						$notification = new \core\output\notification($wn
							,
							\core\output\notification::NOTIFY_WARNING);

						$notification->set_show_closebutton(false);
						echo $OUTPUT->render($notification);
					} else {
						echo $form2->display();
					}
				}
			} catch (Exception $e) {
				die('Lỗi không thể đọc file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
			}
		}
	}
	echo $OUTPUT->footer();
}

if ($th_bulkenrol_csvkey) {
	$form2 = new confirm_form(null, array('th_bulkenrol_csv_key' => $th_bulkenrol_csvkey));

	if ($form2->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/blocks/th_bulk_enrol_student/view.php');
		redirect($courseurl);
	} else if ($formdata = $form2->get_data()) {
		if (!empty($th_bulkenrol_csvkey) && !empty($SESSION->th_bulkenrol_csv) &&
			array_key_exists($th_bulkenrol_csvkey, $SESSION->th_bulkenrol_csv)) {
			set_time_limit(600);
			$data = $SESSION->th_bulkenrol_csv[$th_bulkenrol_csvkey];
			$no_enroleds = $data->user_enroled;

			foreach ($no_enroleds as $k => $no_enroled) {
				$courseid = $no_enroled->courseid;
				$userid = $no_enroled->id;
				$roleid = $no_enroled->roleid;
				$instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
				$timestart = time();
				$timeend = 0;
				$enrol_manual->enrol_user($instance, $userid, $roleid, $timestart, $timeend);
			}

			$link = "<a href='view.php'>tiếp tục ghi danh</a>";
			$link1 = $CFG->wwwroot . '/my';
			$home = "<a href='$link1'>trang chủ</a>";
			$wn = 'Ghi danh thành công bạn có muốn ' . $link . ' hoặc quay lại ' . $home;

			$notification = new \core\output\notification($wn
				,
				\core\output\notification::NOTIFY_WARNING);

			$notification->set_show_closebutton(false);

			echo $OUTPUT->header();
			echo $OUTPUT->heading(get_string('pluginname', 'block_th_bulk_enrol_student'));
			echo $OUTPUT->render($notification);
			echo $OUTPUT->footer();
		}
	}
}
