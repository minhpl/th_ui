<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->libdir.'/mathslib.php';
require_once $CFG->dirroot . '/blocks/th_export_support_dcct/upload_form.php';
require_once $CFG->dirroot . '/blocks/th_export_support_dcct/lib.php';
require_once $CFG->dirroot . '/mod/exportexam/classes/phpdocx/classes/CreateDocx.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

// Check for all required variables.
$courseid = $COURSE->id;
$returnto = optional_param('returnto', 'course', PARAM_ALPHANUM); // Generic navigation return page switch.
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$th_export_support_dcct_key = optional_param('key', 0, PARAM_ALPHANUMEXT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_export_support_dcct', $courseid);
}

require_login($courseid);
require_capability('block/th_export_support_dcct:view', context_course::instance($COURSE->id));

$pageurl = '/blocks/th_export_support_dcct/view.php';
$title = get_string('title', 'block_th_export_support_dcct');
$context = context_system::instance();
$PAGE->set_url('/blocks/th_export_support_dcct/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_export_support_dcct', 'block_th_export_support_dcct'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_export_support_dcct'));

$editurl = new moodle_url('/blocks/th_export_support_dcct/view.php');
$settingsnode = $PAGE->navbar->add(get_string('breadcrumb', 'block_th_export_support_dcct'), $editurl);
$settingsnode->make_active();

if (empty($th_export_support_dcct_key)) {

	$upload_form = new upload_form();

	if ($upload_form->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/my');
		redirect($courseurl);
	} else if ($fromform = $upload_form->get_data()) {

		$content = $upload_form->get_file_content('data_file');
		$content_arr = th_export_parse($content);
		$list_data = th_export_get_content($content_arr);

		$checked = new stdClass();
		$checked->error_messages = array();
		$checked->support_dcct = array();
		$checked->valid_found = 0;

		foreach($list_data as $k => $data) {

			$line = $k + 2;
			$data_arr = explode(',', $data);

			$ten_mon = trim($data_arr[0]);
			$list_ma_lop = explode('-', $data_arr[1]);
			$ten_gvcm = trim($data_arr[2]);
			$hocvi_gvcm = trim($data_arr[3]);
			$sdt_gvcm = trim($data_arr[4]);
			$email_gvcm = trim($data_arr[5]);

			// $check = [];
			$ds_support = array();
			
			foreach($list_ma_lop as $ma_lop) {

				if(!empty($ma_lop)){
					$ma_lop = trim($ma_lop);
					
					if ($SITE->shortname == 'eTNU') {
						$pos = strpos($ma_lop, 'AUM0120');
						$pos1 = strpos($ma_lop, 'AUM0220');
						$pos2 = strpos($ma_lop, 'AUM0520');
						$pos3 = strpos($ma_lop, 'AUM0320');
						$pos4 = strpos($ma_lop, 'AUM0420');
						
						if ($pos !== false || $pos1 !== false || $pos2 !== false || $pos3 !== false || $pos4 !== false) {
							$ma_lop1 = substr($ma_lop, 0, 7);
						} else {
							$ma_lop1 = substr($ma_lop, 0, 9);
						}
						
					} else {
						$ma_lop1 = substr($ma_lop, 0, 7);
					}

					$gvcn = $DB->get_record_sql("SELECT * FROM {th_export_support_dcct} WHERE ma_lop LIKE '%$ma_lop1%' AND role = '1'");

					if (!empty($gvcn)){
						$qlht = $DB->get_record_sql("SELECT * FROM {th_export_support_dcct} WHERE ma_lop LIKE '%$ma_lop1%' AND role = '2'");
						if (!empty($qlht)){
							$checked->valid_found ++;
							$data1 = new stdClass();
							$data1->ma_lop = $ma_lop;
							$data1->ma_lop1 = $ma_lop1;
							$data1->qlht = $qlht;
							$data1->gvcn = $gvcn;
							$ds_support[] = $data1;

						} else {
							$checked->error_messages[] = "Không tìm thấy qlht nào thuộc mã lớp ($ma_lop - $ma_lop1). Vui lòng kiểm tra lại hàng $line.";
						}
					} else {
						$checked->error_messages[] = "Không tìm thấy gvcn nào thuộc mã lớp ($ma_lop - $ma_lop1). Vui lòng kiểm tra lại hàng $line.";
					}
				} else {
					$checked->error_messages[] = "Không tìm thấy mã khóa học nào. Vui lòng kiểm tra lại hàng $line.";
				}
			}

			$data = new stdClass();
			$data->ten_mon = $ten_mon;
			$data->gvcm = $ten_gvcm;
			$data->hocvi_gvcm = $hocvi_gvcm;
			$data->sdt_gvcm = $sdt_gvcm;
			$data->email_gvcm = $email_gvcm;
			$data->ds_support = $ds_support;
			$checked->support_dcct[] = $data;
		}

		// Save data in Session.
		$th_export_support_dcct_key = $courseid . '_' . time();
		$SESSION->th_export_support_dcct[$th_export_support_dcct_key] = $checked;

	} else {
		// form didn't validate or this is the first display
		echo $OUTPUT->header();
		echo $OUTPUT->heading('<center>XUẤT DANH SÁCH HỖ TRỢ DCCT</center>');
		echo "</br>";

		$baseurl = new moodle_url('/blocks/th_export_support_dcct/view.php');
		if ($editcontrols = local_th_export_support_dcct_controls($context, $baseurl)) {
			echo $OUTPUT->render($editcontrols);
		}

		$upload_form->display();
		echo $OUTPUT->footer();
	}
}

if ($th_export_support_dcct_key) {

	$form2 = new confirm_form(null, array('th_export_support_dcct_key' => $th_export_support_dcct_key));

	if ($form2->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/blocks/th_export_support_dcct/view.php');
		redirect($courseurl);
	} else if ($formdata = $form2->get_data()) {
		if (
			!empty($th_export_support_dcct_key) && !empty($SESSION->th_export_support_dcct) &&
			array_key_exists($th_export_support_dcct_key, $SESSION->th_export_support_dcct)
		) {

			$data = $SESSION->th_export_support_dcct[$th_export_support_dcct_key];

			if (!empty($data->support_dcct)) {

				$support_dcct = $data->support_dcct;
				
				$stt = 0;

				$html = '
					<html xmlns="http://www.w3.org/1999/xhtml" lang="VI" dir="ltr">
					<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
					<meta name="ProgId" content="Word.Document"/>
					<meta name="Generator" content="Microsoft Word 11"/>
					<meta name="Originator" content="Microsoft Word 11"/>
					</head>';

				$html .= "<body lang='EN-UK'>";

				foreach ($support_dcct as $k => $support_dcct1) {

					$stt = $stt + 1;
					$ten_mon = $support_dcct1->ten_mon;
					$ten_gvcm = $support_dcct1->gvcm;

					$hocvi_gvcm = $support_dcct1->hocvi_gvcm;

					if($hocvi_gvcm == 'Thạc sĩ'){
						$hocvi_gvcm = 'Th.s';
					} 

					if ($hocvi_gvcm == 'Tiến sĩ') {
						$hocvi_gvcm = 'T.s';
					}
					$sdt_gvcm = $support_dcct1->sdt_gvcm;
					$email_gvcm = $support_dcct1->email_gvcm;
					$ds_support = $support_dcct1->ds_support;	
					$check = array();

					$html .= "<h1 style = 'color: red; font-size: 1.5em'>$stt. $ten_mon</h1>
						<p><strong>- Giảng viên chuyên môn: $hocvi_gvcm</strong> $ten_gvcm, SĐT: 0$sdt_gvcm,</p>
						<p style = 'margin-left: 50px'>Email: <a href =''>$email_gvcm</a></p>
						<p><strong>- Hỗ trợ giải đáp thắc mắc:</strong></p>";

					$html .= "<table style = 'border: 1px solid black; border-collapse: collapse; width: 100%;'>
							<tr style = 'text-align: center; background-color: #4472C4; color: #fff';>
							    <th style = 'border: 1px solid black;border-collapse: collapse; padding: 5px;'>Lớp</td>
							    <th style = 'border: 1px solid black;border-collapse: collapse; padding: 5px;'>GVCN</td>
							    <th style = 'border: 1px solid black;border-collapse: collapse; padding: 5px;'>Điện thoại</td>
							    <th style = 'border: 1px solid black;border-collapse: collapse; padding: 5px;'>Email</td>
							</tr>";
					
					foreach ($ds_support as $support) {
						$ma_lop = $support->ma_lop1;
						$gvcn = $support->gvcn;
						if(!in_array($ma_lop, $check)){
							$check[] = $ma_lop;

							$html .= "
								<tr style = 'text-align: center;'>
								    <td style = 'border: 1px solid black;border-collapse: collapse; padding: 5px;'>$ma_lop</td>
								    <td style = 'border: 1px solid black;border-collapse: collapse; padding: 5px;'>$gvcn->ho_ten</td>
								    <td style = 'border: 1px solid black;border-collapse: collapse; padding: 5px;'>0$gvcn->sdt</td>
								    <td style = 'border: 1px solid black;border-collapse: collapse; padding: 5px;'><a href =''>$gvcn->email</a></td>
								</tr>";
						}
					}
					$html .= '</table>';

					$check1 = array();

					foreach ($ds_support as $support) {

						$id_qlht = $support->qlht->id;
						$qlht = $support->qlht;

						$str = '';
						$list_ma_lop = array();
						foreach ($ds_support as $k => $support1) {
							$id_qlht1 = $support1->qlht->id;
							if ($id_qlht1 == $id_qlht) {
								$ma_lop1 = $support1->ma_lop1;

								unset($ds_support[$k]);
								if(!in_array($ma_lop1, $list_ma_lop)){
									$list_ma_lop[] = $ma_lop1;
									$str = $str . $support1->ma_lop1 . ', ';
								}
							}
						}

						$str = substr($str, 0, -2);
						
						if(!in_array($str, $check1)){
							$check1[] = $str;

							if ($qlht->gioi_tinh == 2){
								$gioi_tinh = 'Cô';
							} else {
								$gioi_tinh = 'Thầy';
							}

							if(!empty($str)) {
								$html .= "<p><strong>- Quản lý học tập</strong> ($str): </p>
										<p style = 'margin-left: 50px'>$gioi_tinh $qlht->ho_ten, SĐT: 0$qlht->sdt, Email: <a href =''>$qlht->email</a></p>";
							}
						}
					}

					// foreach ($ds_support as $support) {
					// 	$ma_lop = $support->ma_lop1;
					// 	$qlht = $support->qlht;
					// 	if(!in_array($ma_lop, $check1)){
					// 		$check1[] = $ma_lop;

					// 		if ($qlht->gioi_tinh == 2){
					// 			$gioi_tinh = 'Cô';
					// 		} else {
					// 			$gioi_tinh = 'Thầy';
					// 		}

					// 		if (in_array($qlht, $list_qlht)) {
					// 			$html .= "<p><strong style = 'color: #FFC000'>- Quản lý học tập ($ma_lop):</strong> $gioi_tinh $qlht->ho_ten, SĐT: 0$qlht->sdt,</p> 
					// 			<p style = 'margin-left: 50px'>mail: $qlht->email</p>";
					// 		} else {
					// 			$list_qlht[] = $qlht;
					// 			$html .= "<p><strong>- Quản lý học tập ($ma_lop):</strong> $gioi_tinh $qlht->ho_ten, SĐT: 0$qlht->sdt,</p>
					// 			<p style = 'margin-left: 50px'>Email: $qlht->email</p>";
					// 		}
					// 	}
					// }
				}

				$html .= '</body></html>';

				$docx = new CreateDocx();
				$docx->setBackgroundColor('#FFF');
				$docx->setDefaultFont('Times New Roman');
				$docx->embedHTML($html, array('useHTMLExtended' => true, 'forceNotTidy' => true));

				$docx->createDocxAndDownload("Danh sach GVCN-QLHT", true);
			}
		}

	} else {
		echo $OUTPUT->header();
		echo $OUTPUT->heading('<center>XUẤT DANH SÁCH HỖ TRỢ ĐCCT</center>');
		if (
			!empty($th_export_support_dcct_key) && !empty($SESSION->th_export_support_dcct) &&
			array_key_exists($th_export_support_dcct_key, $SESSION->th_export_support_dcct)
		) {

			$data = $SESSION->th_export_support_dcct[$th_export_support_dcct_key];

			if (!empty($data->error_messages)) {

				$errors = $data->error_messages;
				$html1 = th_display_table_export_dcct_error($errors);
				echo $OUTPUT->heading(get_string('Hints', 'block_th_bulk_override'));
				echo $html1;
			}

			if (!empty($data->support_dcct)) {
				$support_dcct = $data->support_dcct;
				$html = th_display_table_export_dcct($support_dcct);
				echo $OUTPUT->heading('<center><h3>DANH SÁCH GVCN,QLHT</h3></center>');
				echo $html;
			}
		}

		if (
			!empty($data) && isset($data->valid_found) &&
			empty($data->valid_found)
		) {
			$url    = new moodle_url('/blocks/th_export_support_dcct/view2.php');
			$wn = "Không tìm thấy giá trị hợp lệ.<br />Vui lòng <a href='$url'>quay lại và kiểm tra thông tin đầu vào của bạn.</a>.";
			$notification = new \core\output\notification(
				$wn,
				\core\output\notification::NOTIFY_WARNING
			);
			$notification->set_show_closebutton(false);
			echo $OUTPUT->render($notification);
		} else {
			echo $form2->display();
		}
		echo $OUTPUT->footer();
	}
}

?>

