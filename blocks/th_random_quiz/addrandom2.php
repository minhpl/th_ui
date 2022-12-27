<?php

require_once __DIR__ . '/../../config.php';
require_once $CFG->dirroot . '/mod/quiz/locallib.php';
require_once $CFG->dirroot . '/blocks/th_random_quiz/addrandomform1.php';
require_once $CFG->dirroot . '/question/editlib.php';
require_once $CFG->dirroot . '/question/category_class.php';
require_once $CFG->dirroot . '/course/modlib.php';
require_once $CFG->dirroot . '/blocks/th_random_quiz/lib.php';
require_once $CFG->dirroot . '/lib/filelib.php';
require_once $CFG->dirroot . '/mod/exportexam/myattemplib.php';
require_once $CFG->dirroot . '/mod/exportexam/myqformat_wordtable.php';
require_once $CFG->dirroot . '/blocks/th_random_quiz/classes/FlxZipArchive.class.php';
include $CFG->dirroot . '/mod/exportexam/classes/PHPExcel/IOFactory.php';
include $CFG->dirroot . '/mod/exportexam/classes/PHPExcel.php';
require_once $CFG->dirroot . '/mod/exportexam/classes/phpdocx/classes/CreateDocx.php';
require_once $CFG->dirroot . '/course/lib.php';

global $CFG, $COURSE, $OUTPUT, $PAGE;

$th_random_quiz_key = optional_param('key', 0, PARAM_ALPHANUMEXT);
$th_random_quiz_key2 = optional_param('key2', 0, PARAM_ALPHANUMEXT);

$course_id = $COURSE->id;
// Get the course object and related bits.
if (!$course = $DB->get_record('course', array('id' => $course_id))) {
	print_error('invalidcourseid');
}

require_login($course_id);
require_capability('block/th_random_quiz:view', context_course::instance($course_id));

$pageurl = '/blocks/th_random_quiz/addrandom2.php';
$title = get_string('title', 'block_th_random_quiz');
$PAGE->set_url(new moodle_url("/blocks/th_random_quiz/addrandom2.php"));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_random_quiz', 'block_th_random_quiz'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_random_quiz'));

if (empty($th_random_quiz_key)) {

	$mform = new quiz_add_random_form1(new moodle_url("/blocks/th_random_quiz/addrandom2.php"));

	if ($mform->is_cancelled()) {
		redirect(new moodle_url("/my"));

	} else if ($data = $mform->get_data()) {

		$shortname_course_save = get_config('block_th_random_quiz', 'course_save');
		$course_save = $DB->get_record_sql("SELECT * FROM {course} WHERE shortname = '$shortname_course_save'");
		if ($course_save) {

			$data->course_save = $course_save;

			if (empty($data->course_id)) {
				$listcourses = get_list_courses_id();
			} else {
				$listcourses = $data->course_id;
			}

			$check_random = new stdClass();
			$check_random->error_messages = array();
			$check_random->random_quiz = array();
			$check_random->valid_random_found = 0;

			$thoi_gian = date("H:i d-m-Y", time());

			foreach ($listcourses as $courseid) {

				$so_bai_kt = $data->so_bai_kt;
				$module = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = 'quiz'");

				if(isset($data->export_all)){
					$numbertoadd = $DB->get_field_sql("SELECT COUNT(q.id) FROM {question_categories} as qc, {question} as q, {context} as c WHERE c.instanceid = '$courseid' AND c.id = qc.contextid AND qc.id = q.category AND q.parent = '0' AND q.hidden='0'");
				} else {
					$numbertoadd = $data->numbertoadd;
				}

				if ($data->option_add == 1) {
					
					$numbertoadd0 = (int) ($numbertoadd * 0.4);
					$numbertoadd1 = (int) ($numbertoadd * 0.4);
					$numbertoadd2 = $numbertoadd - 2 * (int) ($numbertoadd * 0.4);

					$list_category = $DB->get_records_sql("SELECT qc.id, qc.name FROM {question_categories} as qc, {context} as c WHERE (qc.name REGEXP BINARY '^Khó$' OR qc.name REGEXP BINARY '^Dễ$' OR qc.name REGEXP BINARY '^Trung bình$') AND qc.contextid = c.id AND c.contextlevel = '50' AND c.instanceid = '$courseid';");
					$category_kho = '';
					$category_tb = '';
					$category_de = '';

					foreach ($list_category as $category) {
						if ($category->name == 'Khó') {
							$category_kho = $category->id;
							$count_subcategory_kho = $DB->get_field_sql("SELECT COUNT(q.id) FROM {question_categories} as qc, {question} as q WHERE qc.parent = '$category_kho' AND qc.id = q.category");
						} else if ($category->name == 'Trung bình') {
							$category_tb = $category->id;
							$count_subcategory_tb = $DB->get_field_sql("SELECT COUNT(q.id) FROM {question_categories} as qc, {question} as q WHERE qc.parent = '$category_tb' AND qc.id = q.category");
						} else {
							$category_de = $category->id;
							$count_subcategory_de = $DB->get_field_sql("SELECT COUNT(q.id) FROM {question_categories} as qc, {question} as q WHERE qc.parent = '$category_de' AND qc.id = q.category");
						}
					}

					for ($i = 1; $i <= $so_bai_kt; $i++) {

						$data_quiz = data_quiz($i, $courseid, $module, $thoi_gian);
						$course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = '$courseid'");

						if (empty($category_kho) || empty($category_tb) || empty($category_de)) {

							$error_message = "Không tìm thấy danh mục Khó, Trung bình, Dễ trong NHCH của khóa học ($course->fullname). Vui lòng kiểm tra lại.";

							if (!in_array($error_message, $check_random->error_messages)) {
								$check_random->error_messages[] = $error_message;
							}

						} else {

							if ($count_subcategory_kho < $numbertoadd2 || $count_subcategory_tb < $numbertoadd1 || $count_subcategory_de < $numbertoadd0) {

								$error_message = "Không đủ câu hỏi để ramdom trong danh mục khó, trung bình, dễ của khóa học ($course->fullname). Vui lòng kiểm tra lại.";
								if (!in_array($error_message, $check_random->error_messages)) {
									$check_random->error_messages[] = $error_message;
								}

							} else {

								$check_random->valid_random_found += 1;
								$data_random = new stdClass();
								$data_random->data_quiz = $data_quiz;
								$data_random->course = $course;
								$data_random->category_kho = $category_kho;
								$data_random->category_tb = $category_tb;
								$data_random->category_de = $category_de;
								$data_random->numbertoadd = $numbertoadd;
								$data_random->numbertoadd0 = $numbertoadd0;
								$data_random->numbertoadd1 = $numbertoadd1;
								$data_random->numbertoadd2 = $numbertoadd2;
								$data_random->option_add = $data->option_add;
								$data_random->course_save = $data->course_save;
								$data_random->ma_bai_kt = $i;
								$check_random->random_quiz[] = $data_random;
							}
						}
					}

				} else {

					for ($i = 1; $i <= $so_bai_kt; $i++) {

						$data_quiz = data_quiz($i, $courseid, $module, $thoi_gian);
						$course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = '$courseid'");

						$link_course = new moodle_url('/course/view.php', ['id' => $courseid]);
						$link = html_writer::link($link_course, $course->fullname);

						$sql = "SELECT qc.id FROM {question_categories} as qc, {context} as c WHERE qc.name = 'top' AND qc.contextid = c.id AND c.contextlevel = '50' AND c.instanceid = '$courseid'";
						$category_top = $DB->get_field_sql($sql);
						$count_subcategory_top = $DB->get_field_sql("SELECT COUNT(q.id) FROM {question_categories} as qc, {question} as q, {context} as c WHERE c.instanceid = '$courseid' AND c.id = qc.contextid AND qc.id = q.category AND q.parent = '0' AND q.hidden='0'");

						if (empty($category_top)) {

							$error_message = "Không tìm thấy danh mục top trong NHCH của khóa học ($link). Vui lòng kiểm tra lại.";

							if (!in_array($error_message, $check_random->error_messages)) {
								$check_random->error_messages[] = $error_message;
							}

						} else {

							if ($count_subcategory_top < $numbertoadd || $count_subcategory_top == 0) {
								$error_message = "Không đủ câu hỏi để ramdom trong NHCH của khóa học ($link). Vui lòng kiểm tra lại.";

								if (!in_array($error_message, $check_random->error_messages)) {
									$check_random->error_messages[] = $error_message;
								}
							} else {

								$check_random->valid_random_found += 1;
								$data_random = new stdClass();
								$data_random->data_quiz = $data_quiz;
								$data_random->course = $course;
								$data_random->category_top = $category_top;
								$data_random->option_add = $data->option_add;
								$data_random->numbertoadd = $numbertoadd;
								$data_random->course_save = $data->course_save;
								$data_random->ma_bai_kt = $i;
								$check_random->random_quiz[] = $data_random;
							}
						}
					}
				}
			}

			// Save data in Session.
			$th_random_quiz_key = $course_id . '_' . time();
			$SESSION->block_th_random_quiz[$th_random_quiz_key] = $check_random;

		} else {
			redirect($CFG->wwwroot.'/blocks/th_random_quiz/addrandom2.php', 'Không tìm thấy khóa học lưu trữ trong cài đặt', null, \core\output\notification::NOTIFY_WARNING);
		}

	} else {

		if ($th_random_quiz_key2) {

			$data_arr = $SESSION->block_th_random_quiz2[$th_random_quiz_key2];

			function my_quiz_create_attempt_handling_errors($attemptid, $cmid = null) {
				try {
					$attempobj = myquiz_attempt::create($attemptid);
				} catch (moodle_exception $e) {
					if (!empty($cmid)) {
						list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'quiz');
						$continuelink = new moodle_url('/mod/quiz/view.php', array('id' => $cmid));
						$context = context_module::instance($cm->id);
						if (has_capability('mod/quiz:preview', $context)) {
							throw new moodle_exception('attempterrorcontentchange', 'quiz', $continuelink);
						} else {
							throw new moodle_exception('attempterrorcontentchangeforuser', 'quiz', $continuelink);
						}
					} else {
						throw new moodle_exception('attempterrorinvalid', 'quiz');
					}
				}

				if (!empty($cmid) && $attempobj->get_cmid() != $cmid) {
					throw new moodle_exception('invalidcoursemodule');
				} else {
					return $attempobj;
				}
			}

			foreach ($data_arr as $k => $data) {

				$attemptid = $data->attemptid;
				$cmid = $data->cmid;

				$attemptobj = my_quiz_create_attempt_handling_errors($attemptid, $cmid);

				$page = $attemptobj->force_page_number_into_range(0);

				if ($attemptobj->get_userid() != $USER->id) {
					if ($attemptobj->has_capability('mod/quiz:viewreports')) {
						redirect($attemptobj->review_url(null, $page));
					} else {
						throw new moodle_quiz_exception($attemptobj->get_quizobj(), 'notyourattempt');
					}
				}

				// Check capabilities and block settings.
				if (!$attemptobj->is_preview_user()) {
					$attemptobj->require_capability('mod/quiz:attempt');
					if (empty($attemptobj->get_quiz()->showblocks)) {
						$PAGE->blocks->show_only_fake_blocks();
					}

				} else {
					navigation_node::override_active_url($attemptobj->start_attempt_url());
				}

				// If the attempt is already closed, send them to the review page.
				if ($attemptobj->is_finished()) {
					redirect($attemptobj->review_url(null, $page));
				} else if ($attemptobj->get_state() == quiz_attempt::OVERDUE) {
					redirect($attemptobj->summary_url());
				}

				// Check the access rules.
				$accessmanager = $attemptobj->get_access_manager(time());
				$accessmanager->setup_attempt_page($PAGE);
				$output = $PAGE->get_renderer('mod_quiz');
				$messages = $accessmanager->prevent_access();

				if (!$attemptobj->is_preview_user() && $messages) {
					print_error('attempterror', 'quiz', $attemptobj->view_url(),
						$output->access_messages($messages));
				}

				if ($accessmanager->is_preflight_check_required($attemptobj->get_attemptid())) {
					redirect($attemptobj->start_attempt_url(null, $page));
				}

				// Set up auto-save if required.

				$autosaveperiod = get_config('quiz', 'autosaveperiod');
				if ($autosaveperiod) {
					$PAGE->requires->yui_module('moodle-mod_quiz-autosave',
						'M.mod_quiz.autosave.init', array($autosaveperiod));
				}

				// Log this page view.
				$attemptobj->fire_attempt_viewed_event();

				// Get the list of questions needed by this page.
				$slots = $attemptobj->get_slots($page);

				// Check.
				if (empty($slots)) {
					throw new moodle_quiz_exception($attemptobj->get_quizobj(), 'noquestionsfound');
				}

				// Update attempt page, redirecting the user if $page is not valid.
				if (!$attemptobj->set_currentpage($page)) {
					redirect($attemptobj->start_attempt_url(null, $attemptobj->get_currentpage()));
				}

				if ($attemptobj->is_last_page($page)) {
					$nextpage = -1;
				} else {
					$nextpage = $page + 1;
				}

				$questionattempts = $attemptobj->quba->questionattempts;
				$questionids = [];
				foreach ($questionattempts as $key => $qattemp) {
					$slot = $qattemp->slot;
					$questionids[$slot] = $qattemp->question->id;
				}

				$fullslots = $attemptobj->get_slots();

				$questionids_sorted = [];
				foreach ($fullslots as $key => $value) {
					$questionids_sorted[] = $questionids[$value];
				}

				$questionids = $questionids_sorted;

				if (count($questionids) > 0) {
					list($insql, $inparams) = $DB->get_in_or_equal($questionids);
				}

				$sql = "SELECT *
                    from {question}
                    where id $insql";

				$questions = $DB->get_records_sql($sql, $inparams);

				$qresults = array();
				$export = true;

				foreach ($questions as $key => $question) {
					$question->export_process = $export;
					$qtype = question_bank::get_qtype($question->qtype, false);
					if ($export && $qtype->name() == 'missingtype') {
						continue;
					}
					
					$qtype->get_question_options($question);
					$qresults[$key] = $question;
				}

				$qresults2 = [];
				$count = 0;

				$docfullname = $data->quiz->name;
				$course_name = $data->course->fullname;
				$ma_bai_kt = $data->ma_bai_kt;
				$course_name1 = mb_strtoupper($course_name);
				$pos = strpos($course_name1, ' - MẪU');
				
				if ($pos) {
				    $course_name1 = str_ireplace( ' - MẪU' , '', $course_name1);
				}
				
				$content = '
				<html xmlns="http://www.w3.org/1999/xhtml" lang="VI" dir="ltr">
				<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
				<meta name="ProgId" content="Word.Document"/>
				<meta name="Generator" content="Microsoft Word 11"/>
				<meta name="Originator" content="Microsoft Word 11"/>
				</head>';

				$content .= "
					<body lang='EN-UK'>
					<table style = 'border: 1px solid #fff; width: 100%'>
				        <tr>
				            <th style = 'font-size: 1em; text-align: center;'>ĐẠI HỌC THÁI NGUYÊN<p style = 'font-size: 1em; margin: 0.5em 0 0.5em 0;'>TRUNG TÂM ĐÀO TẠO TỪ XA</p></th>
				            <th style = 'font-size: 1em; '>CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM<p style = 'text-align: center; font-size: 1em; margin: 0.5em 0 0.5em 0;'>Độc lập - Tự do - Hạnh phúc</p></th>
				        </tr>
				    </table>

				    <div style = 'text-align: center;font-size: 1em;margin: 3em 0 1em 0'>
					    <p><strong>ĐỀ THI KẾT THÚC HỌC PHẦN</strong></p>
					    <p><strong>MÔN: $course_name1</strong></p>
					    <p><strong>Thời gian làm bài: 60 phút</strong></p>
					    <p><strong>Mã đề: 00$ma_bai_kt</strong></p>
				    </div>
				    <p style = 'margin: 0.5em 0 0.3em 0;font-size: 1em'><strong>NỘI DUNG ĐỀ:</strong></p>";

				$docx = new CreateDocx();
				$docx->setDefaultFont('Times New Roman');
				$text = array();

				foreach ($questionids as $key => $value) {

					$count++;

					$question = $qresults[$value];
					$questiontext = $question->questiontext;
					$contextid = context_course::instance($data->course->id)->id;

					$fs = get_file_storage();
					$files = $fs->get_area_files($contextid, 'question', 'questiontext', $question->id, '', false);

				    foreach ($files as $file){
				    	$contents = $file->get_content();
				    	$filename = $file->get_filename();
				    	$mimetype = $file->get_mimetype();
				    }

				    if(!empty($contents)){
					    if ($mimetype != 'audio/mp3') { 	
				    		$path = $CFG->dataroot;
							if (!file_exists($path . '/exportexam')) {
								mkdir($path . '/exportexam', 0777);
							}

							$filename_arr = explode('.', $filename);
							$filename1 = $filename_arr[0] . rand(1,10000) . '.' . $filename_arr[1];

							$url = "$CFG->dataroot/exportexam/$filename1";
						    file_put_contents($url, $contents);

						    $questiontext = urldecode($questiontext);
						    $questiontext = str_replace("@@PLUGINFILE@@/$filename", $url, $questiontext);
						}
					}

					try {
						if (preg_match('/(^<p[^>]*>)/i', $questiontext)) {
							$questiontext = preg_replace('/(^<p[^>]*)(>)/i', '<span>', $questiontext);
							$questiontext = preg_replace('/(<\/p[^>]*>)/i', '</span>', $questiontext, 1);
						}
						$questiontext = preg_replace('/(<\/{0,1}[aA][^>]*>)/i', '', $questiontext);

						// fix error vector
						$questiontext = str_replace('<mover accent="true">', '<mover accent="false">', $questiontext);


					} catch (Exception $e) {

					}

					$question->questiontext = "Câu $count. " . $questiontext;

					$content .= "<p style = 'margin: 0.5em 0 0.3em 0;font-size: 1em'>Câu $count. $questiontext</p>";
					$answers = $question->options->answers;
					$string = "ABCDEFGHI";
					$num = 0;

					foreach ($answers as $answer) {
						$answertext = $answer->answer;

						$files = $fs->get_area_files($contextid, 'question', 'answer', $answer->id, '', false);

					    foreach ($files as $file){
					    	$contents = $file->get_content();
					    	$filename = $file->get_filename();
					    	$mimetype = $file->get_mimetype();
					    }

					    if(!empty($contents)){
						    if ($mimetype != 'audio/mp3') { 	
					    		$path = $CFG->dataroot;
								if (!file_exists($path . '/exportexam')) {
									mkdir($path . '/exportexam', 0777);
								}

								$filename_arr = explode('.', $filename);
								$filename1 = $filename_arr[0] . rand(1,10000) . '.' . $filename_arr[1];

								$url = "$CFG->dataroot/exportexam/$filename";
							    file_put_contents($url, $contents);

							    $answertext = urldecode($answertext);
							    $answertext = str_replace("@@PLUGINFILE@@/$filename", $url, $answertext);
							}
						}

						try {
							if (preg_match('/(^<p[^>]*>)/i', $answertext)) {
								$answertext = preg_replace('/(^<p[^>]*)(>)/i', '<span>', $answertext);
								$answertext = preg_replace('/(<\/p[^>]*>)/i', '</span>', $answertext, 1);
							}

							$answertext = preg_replace('/(<\/{0,1}[aA][^>]*>)/i', '', $answertext);
							// fix error vector
							$answertext = str_replace('<mover accent="true">', '<mover accent="false">', $answertext);

						} catch (Exception $e) {

						}

						$substr = substr($string, $num, 1);
						
						$answer->answer = "$substr. " . $answertext;
						$content .= "<p style = 'margin: 1em 0 0.3em 2.5em;font-size: 1em'>$substr. $answertext</p>";
						$num++;
					}

					$qresults2[] = $question;
				}

				$content .= '</body></html>';

				$docx->setBackgroundColor('#FFF');

				// create a Word fragment to insdert in the default header
				$numbering = new WordFragment($docx, 'defaultHeader');
				// set some formatting options
				$options = array(
				    'textAlign' => 'right',
				    'bold' => false,
				    'sz' => 12,
				    'color' => '#000000',
				);
				$numbering->addPageNumber('page-of', $options);
				$docx->addFooter(array('default' => $numbering));

				$docx->embedHTML($content, array('useHTMLExtended' => true, 'forceNotTidy' => true));

				$files = glob("$CFG->dataroot/exportexam/*"); // get all file names

				foreach ($files as $file) {
					if (is_file($file)) {
						unlink($file); // delete file
					}
				}

				$path = $CFG->dataroot;

				if (!file_exists($path . '/random_quiz_uploads')) {
					mkdir($path . '/random_quiz_uploads', 0777);
				}

				$url = "$path/random_quiz_uploads/$docfullname.docx";

				$docx->createDocx($url);

				$course_save = $data->course_save;
				$id_course_save = $course_save->id;

				$check_section = $DB->get_record_sql("SELECT * FROM {course_sections} WHERE course REGEXP BINARY '^$id_course_save$' AND name = '$course_name' ORDER BY section DESC LIMIT 1");

				if (empty($check_section)) {
					$section = course_create_section($course_save, null);
				} else {
					$section = $check_section;
				}

				$data1 = new stdClass();

				$data1->name = $course_name;
				$data1->summary_editor = [

					'text' => '',
					'format' => 1,
					'itemid' => null,
				];

				$data1->id = $section->id;
				$data1->mform_isexpanded_id_availabilityconditions = 0;
				$data1->summarytrust = 0;
				$data1->summary = null;
				$data1->summaryformat = 1;
				$data1->availability = null;

				course_update_section($course_save, $section, $data1);

				$context = context_user::instance($USER->id);

				$draftitemid = file_get_unused_draft_itemid();
				$filerecord = array('component' => 'user', 'filearea' => 'draft',
					'contextid' => $context->id, 'itemid' => $draftitemid, 'filepath' => '/',
					'filename' => $docfullname . '.docx');

				$fs = get_file_storage();
				$fs->create_file_from_pathname($filerecord, $url);

				$module = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = 'resource'");

				$data2 = new stdClass();
				$data2->name = "$docfullname.docx";
				$data2->introeditor = [
					'text' => '',
					'format' => 1,
					'itemid' => $draftitemid,
				];

				$data2->showdescription = 0;
				$data2->files = $draftitemid;
				$data2->display = 0;
				$data2->popupwidth = 620;
				$data2->popupheight = 450;
				$data2->printintro = 1;
				$data2->filterfiles = 0;
				$data2->visible = 1;
				$data2->visibleoncoursepage = 1;
				$data2->cmidnumber = null;
				$data2->availabilityconditionsjson = null;
				$data2->completionunlocked = 1;
				$data2->completion = 1;
				$data2->completionexpected = 0;
				$data2->tags = [];
				$data2->course = $id_course_save;
				$data2->coursemodule = 0;
				$data2->section = $section->section;
				$data2->module = $module;
				$data2->modulename = 'resource';
				$data2->instance = 0;
				$data2->add = 'resource';
				$data2->update = 0;
				$data2->return = 0;
				$data2->sr = 0;
				$data2->competencies = [];
				$data2->competency_rule = 0;
				$data2->revision = 1;

				$moduleinfo = add_moduleinfo($data2, $course_save, '');

				//save file excel

				$PHPExcel = new PHPExcel();
				$PHPExcel->setActiveSheetIndex(0);
				$PHPExcel->getActiveSheet()->setTitle('sheet');
				$PHPExcel->getActiveSheet()->setCellValue('A1', 'Câu');
				$PHPExcel->getActiveSheet()->setCellValue('B1', 'Đáp án');
				$rowNumber = 2;
				$count1 = 0;

				foreach ($questionids as $key => $value) {
					$count1++;
					$question1 = $qresults[$value];
					$answers1 = $question1->options->answers;
					$string = "ABCDEFGHI";
					$num1 = 0;
					$stt = 1;
					foreach ($answers1 as $k => $answer1) {
						if ($answer1->fraction != 0.0000000) {
							$substr1 = substr($string, $num1, 1);
							$stt = $stt + 1;
							$PHPExcel->getActiveSheet()->setCellValue('A' . $rowNumber, "$count1");
							$PHPExcel->getActiveSheet()->setCellValue('B' . $rowNumber, "$substr1");
						}

						$num1++;
					}
					$rowNumber++;
				}

				$objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');

				if (isset($objWriter)) {
					$url = "$CFG->dataroot/random_quiz_uploads/$docfullname.xlsx";
					$objWriter->save($url);

					$context = context_user::instance($USER->id);
					$draftitemid = file_get_unused_draft_itemid();
					$filerecord = array('component' => 'user', 'filearea' => 'draft',
						'contextid' => $context->id, 'itemid' => $draftitemid, 'filepath' => '/',
						'filename' => $docfullname . '.xlsx');

					$fs = get_file_storage();
					$fs->create_file_from_pathname($filerecord, $url);

					$module = $DB->get_field_sql("SELECT id FROM {modules} WHERE name = 'resource'");

					$id_course_save = $course_save->id;

					$check_section = $DB->get_record_sql("SELECT * FROM {course_sections} WHERE course REGEXP BINARY '^$id_course_save$' AND name = '$course_name' ORDER BY section DESC LIMIT 1");
					if (empty($check_section)) {
						$section = course_create_section($course, null);
					} else {
						$section = $check_section;
					}

					$data3 = new stdClass();
					$data3->name = "$docfullname.xlsx";
					$data3->introeditor = [
						'text' => '',
						'format' => 1,
						'itemid' => $draftitemid,
					];

					$data3->showdescription = 0;
					$data3->files = $draftitemid;
					$data3->display = 0;
					$data3->popupwidth = 620;
					$data3->popupheight = 450;
					$data3->printintro = 1;
					$data3->filterfiles = 0;
					$data3->visible = 1;
					$data3->visibleoncoursepage = 1;
					$data3->cmidnumber = null;
					$data3->availabilityconditionsjson = null;
					$data3->completionunlocked = 1;
					$data3->completion = 1;
					$data3->completionexpected = 0;
					$data3->tags = [];
					$data3->course = $id_course_save;
					$data3->coursemodule = 0;
					$data3->section = $section->section;
					$data3->module = $module;
					$data3->modulename = 'resource';
					$data3->instance = 0;
					$data3->add = 'resource';
					$data3->update = 0;
					$data3->return = 0;
					$data3->sr = 0;
					$data3->competencies = [];
					$data3->competency_rule = 0;
					$data3->revision = 1;

					$moduleinfo = add_moduleinfo($data3, $course_save, '');
				}
			}

			$the_folder = "$CFG->dataroot/random_quiz_uploads";
			$zip_file_name = "$CFG->dataroot/danh_sach_de.zip";
			$za = new th_random_quiz_FlxZipArchive;
			$res = $za->open($zip_file_name, ZipArchive::CREATE);

			if ($res === TRUE) {
				$za->addDir($the_folder, basename($the_folder));
				$za->close();
			} else {
				echo 'Could not create a zip archive';
			}

			header('Content-Type: application/zip');
			header('Content-disposition: attachment; filename = danh_sach_de.zip');
			header('Content-Length: ' . filesize($zip_file_name));
			readfile($zip_file_name);

			$files = glob("$CFG->dataroot/random_quiz_uploads/*"); // get all file names

			foreach ($files as $file) {
				// iterate files
				if (is_file($file)) {
					unlink($file); // delete file
				}
			}

			sleep(2);

			unlink($zip_file_name);

		} else {
			echo $OUTPUT->header();
			echo $OUTPUT->heading('Thêm câu hỏi ngẫu nhiên vào bài kiểm tra');
			$mform->display();
			echo $OUTPUT->footer();
		}
	}
}

if ($th_random_quiz_key) {

	$form2 = new confirm_form(null, array('th_random_quiz_key' => $th_random_quiz_key));

	if ($form2->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/blocks/th_random_quiz/addrandom2.php');
		redirect($courseurl);
	} else if ($formdata = $form2->get_data()) {
		if (
			!empty($th_random_quiz_key) && !empty($SESSION->block_th_random_quiz) &&
			array_key_exists($th_random_quiz_key, $SESSION->block_th_random_quiz)
		) {

			$random_quiz_data = $SESSION->block_th_random_quiz[$th_random_quiz_key];
			$random_quiz = $random_quiz_data->random_quiz;

			foreach ($random_quiz as $k => $random) {

				if ($random->option_add == 1) {

					$data_quiz = $random->data_quiz;
					$course = $random->course;

					$category_kho = $random->category_kho;
					$category_tb = $random->category_tb;
					$category_de = $random->category_de;
					$numbertoadd = $random->numbertoadd;
					$numbertoadd0 = $random->numbertoadd0;
					$numbertoadd1 = $random->numbertoadd1;
					$numbertoadd2 = $random->numbertoadd2;

					$questioncount_kho = get_question_count($category_kho);
					$questioncount_tb = get_question_count($category_tb);
					$questioncount_de = get_question_count($category_de);

					$moduleinfo = add_moduleinfo($data_quiz, $course, '');
					$quiz = $moduleinfo;

					if (!empty($formdata->submitbutton)) {
						$categoryid = $category_kho;

						if (empty($questioncount_kho)) {
							$includesubcategories = 1;
						} else {
							$includesubcategories = 0;
						}

						if (!$includesubcategories) {
							// If the chosen category is a top category.
							$includesubcategories = $DB->record_exists('question_categories', ['id' => $categoryid, 'parent' => 0]);
						}

					} else {
						throw new coding_exception(
							'It seems a form was submitted without any button being pressed???');
					}

					$tagids = [];

					quiz_add_random_questions($quiz, 1, $categoryid, $numbertoadd2, $includesubcategories, $tagids);

					if (!empty($formdata->submitbutton)) {
						$categoryid1 = $category_tb;

						if (empty($questioncount_tb)) {
							$includesubcategories = 1;
						} else {
							$includesubcategories = 0;
						}
						if (!$includesubcategories) {
							// If the chosen category is a top category.
							$includesubcategories = $DB->record_exists('question_categories', ['id' => $categoryid1, 'parent' => 0]);
						}

					} else {
						throw new coding_exception(
							'It seems a form was submitted without any button being pressed???');
					}

					quiz_add_random_questions($quiz, 2, $categoryid1, $numbertoadd1, $includesubcategories, $tagids);

					if (!empty($formdata->submitbutton)) {
						$categoryid2 = $category_de;

						if (empty($questioncount_de)) {
							$includesubcategories = 1;
						} else {
							$includesubcategories = 0;
						}

						if (!$includesubcategories) {
							// If the chosen category is a top category.
							$includesubcategories = $DB->record_exists('question_categories', ['id' => $categoryid2, 'parent' => 0]);
						}

					} else {
						throw new coding_exception(
							'It seems a form was submitted without any button being pressed???');
					}

					quiz_add_random_questions($quiz, 3, $categoryid2, $numbertoadd0, $includesubcategories, $tagids);
					quiz_delete_previews($quiz);
					quiz_update_sumgrades($quiz);

					$cmid = $moduleinfo->coursemodule;
					$cm = $DB->get_record_sql("SELECT * FROM {course_modules} WHERE id = '$cmid'");
					$quizobj = quiz::create($cm->instance, $USER->id);
					$attempts = quiz_get_user_attempts($quizobj->get_quizid(), $USER->id, 'all', true);
					$lastattempt = end($attempts);
					$attempt = quiz_prepare_and_start_new_attempt($quizobj, $numbertoadd, $lastattempt);
					$attemptid = $attempt->id;

					$dataobject = new stdClass();
					$dataobject->cmid = $cmid;
					$dataobject->attemptid = $attemptid;
					$dataobject->course = $course;
					$dataobject->quiz = $quiz;
					$dataobject->course_save = $random->course_save;
					$dataobject->ma_bai_kt = $random->ma_bai_kt;
					$arr_data[$k + 1] = $dataobject;

				} else {

					$data_quiz = $random->data_quiz;
					$course = $random->course;
					$category_top = $random->category_top;
					$numbertoadd = $random->numbertoadd;
					$questioncount_top = get_question_count($category_top);

					$moduleinfo = add_moduleinfo($data_quiz, $course, '');
					$quiz = $moduleinfo;

					$sql = "SELECT COUNT(id) FROM {question} WHERE category = '$category_top' AND hidden = '0'";
					$questioncount = $DB->get_field_sql($sql);

					if (!empty($formdata->submitbutton)) {
						$categoryid = $category_top;
						$includesubcategories = $DB->record_exists('question_categories', ['id' => $categoryid, 'parent' => 0]);
					} else {
						throw new coding_exception(
							'It seems a form was submitted without any button being pressed???');
					}

					$tagids = [];

					quiz_add_random_questions($quiz, 1, $categoryid, $numbertoadd, $includesubcategories, $tagids);
					quiz_delete_previews($quiz);
					quiz_update_sumgrades($quiz);

					$cmid = $moduleinfo->coursemodule;
					$cm = $DB->get_record_sql("SELECT * FROM {course_modules} WHERE id = '$cmid'");
					$quizobj = quiz::create($cm->instance, $USER->id);
					$attempts = quiz_get_user_attempts($quizobj->get_quizid(), $USER->id, 'all', true);
					$lastattempt = end($attempts);
					$attempt = quiz_prepare_and_start_new_attempt($quizobj, $numbertoadd, $lastattempt);
					$attemptid = $attempt->id;

					$dataobject = new stdClass();
					$dataobject->cmid = $cmid;
					$dataobject->attemptid = $attemptid;
					$dataobject->course = $course;
					$dataobject->quiz = $quiz;
					$dataobject->course_save = $random->course_save;
					$dataobject->ma_bai_kt = $random->ma_bai_kt;
					$arr_data[$k + 1] = $dataobject;
				}
			}
			// Save data in Session.
			$th_random_quiz_key2 = $course_id . '_' . time();
			$SESSION->block_th_random_quiz2[$th_random_quiz_key2] = $arr_data;

			redirect(new moodle_url("/blocks/th_random_quiz/addrandom2.php?key2=$th_random_quiz_key2"));
		}
	} else {

		echo $OUTPUT->header();
		echo $OUTPUT->heading('<center>Thêm câu hỏi ngẫu nhiên vào bài kiểm tra</center>');

		if (
			!empty($th_random_quiz_key) && !empty($SESSION->block_th_random_quiz) &&
			array_key_exists($th_random_quiz_key, $SESSION->block_th_random_quiz)
		) {

			$random_quiz_data = $SESSION->block_th_random_quiz[$th_random_quiz_key];

			if (!empty($random_quiz_data->error_messages)) {
				$errors = $random_quiz_data->error_messages;

				$table = new html_table();
				$table->head = array('STT', 'Gợi ý');
				$stt = 0;

				foreach ($errors as $k => $error) {
					$stt = $stt + 1;
					$row = new html_table_row();
					$cell = new html_table_cell($stt);
					$row->cells[] = $cell;
					$cell = new html_table_cell($error);
					$row->cells[] = $cell;
					$table->data[] = $row;
				}

				$html = html_writer::table($table);
				echo $OUTPUT->heading("Gợi ý");
				echo $html;
			}

			if (!empty($random_quiz_data->random_quiz)) {
				$random_quiz = $random_quiz_data->random_quiz;
				$html1 = th_display_table_random_quiz($random_quiz);
				echo $OUTPUT->heading('<center><h3>Các bài kiểm tra sẽ được tạo và thêm ngẫu nhiên câu hỏi</h3></center>');
				echo $html1;
			}
		}

		if (empty($random_quiz_data->valid_random_found)) {
			$a = new stdClass();
			$url = new moodle_url('/blocks/th_random_quiz/addrandom2.php');
			$a->url = $url->out();
			$wn = get_string('error_no_valid_time_in_list', 'block_th_bulk_override', $a);
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

<script type="text/javascript">
    $(document).ready(function() {

    $('input[type=checkbox]').change(function() {
    	if ($(this).is(':checked')) {
            document.getElementById("id_numbertoadd").disabled = true;
        }
        else {
            document.getElementById("id_numbertoadd").disabled = false;
        }
		
    });
});
</script>