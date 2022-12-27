<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once 'th_import_audio_form.php';
require_once $CFG->dirroot . '/lib/filelib.php';
require_once $CFG->dirroot . '/lib/questionlib.php';
require_once "lib.php";

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

// Check for all required variables.
$courseid = $COURSE->id;
$th_import_audio_key = optional_param('key', 0, PARAM_ALPHANUMEXT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_import_audio', $courseid);
}

require_login($courseid);
require_capability('block/th_import_audio:view', context_course::instance($COURSE->id));

$pageurl = '/blocks/th_import_audio/view.php';
$title = get_string('title', 'block_th_import_audio');
$PAGE->set_url('/blocks/th_import_audio/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_import_audio', 'block_th_import_audio'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_import_audio'));

$editurl = new moodle_url('/blocks/th_import_audio/view.php');
$settingsnode = $PAGE->navbar->add(get_string('breadcrumb', 'block_th_import_audio'), $editurl);
$settingsnode->make_active();

if (empty($th_import_audio_key)) {

	$th_import_audio_form = new th_import_audio_form();

	if ($th_import_audio_form->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/my');
		redirect($courseurl);

	} else if ($fromform = $th_import_audio_form->get_data()) {

		$course_id = $fromform->course_id;
		$realfilename = $th_import_audio_form->get_new_filename('newfile');
		$importfile = make_request_directory() . "/{$realfilename}";

		if (!$result = $th_import_audio_form->save_file('newfile', $importfile, true)) {
			throw new moodle_exception('uploadproblem');
		}

		//save log

		$context = context_user::instance($USER->id);
		$log_import_zip = new stdClass();
		$log_import_zip->contextid = $context->id;
		$log_import_zip->option = 0;
		$log_import_zip->itemid = $fromform->newfile;
		$log_import_zip->filename = $realfilename;
		$log_import_zip->courseid = $course_id;
		$log_import_zip->timecreated = time();
		$DB->insert_record('th_log_import_audio', $log_import_zip);

		$zipres = zip_open($importfile);
		$zipentry = zip_read($zipres);
		$fs = get_file_storage();

		$stt = 0;
		$check_import = new stdClass();
		$check_import->error_messages = array();
		$check_import->import_audio = array();
		$check_import->valid_import_found = 0;

		while ($zipentry) {
			if (!zip_entry_open($zipres, $zipentry, "r")) {
				zip_close($zipres);
				throw new \moodle_exception('errorunzippingfiles', 'error');
			}
			$stt = $stt + 1;

			$zefilename = zip_entry_name($zipentry);
			$zefilesize = zip_entry_filesize($zipentry);
			$imagedata = zip_entry_read($zipentry, $zefilesize);
			$imagename = basename($zefilename);

			$filename = substr($imagename, 0, -4);

			$pos = strpos($imagename, '+');
			if ($pos !== false) {
				$filename_arr = explode('+', $filename);

				foreach($filename_arr as $filename){
					$list_question = $DB->get_record_sql("SELECT q.* FROM {question} as q, {context} as c, {question_categories} as qc WHERE q.name = '$filename' AND q.category= qc.id
						AND qc.contextid = c.id AND c.contextlevel = '50' AND c.instanceid = '$course_id'");

					if (!empty($list_question)) {

						$check_import->valid_import_found += 1;
						$data = new stdClass();
						$data->imagename = $filename . '.mp3';
						$data->imagedata = $imagedata;
						$data->list_question = $list_question;
						$data->filename = $filename;
						$data->courseid = $course_id;
						$check_import->import_audio[] = $data;

					} else {

						$sql = "SELECT fullname FROM {course} WHERE id = $course_id";
						$fullname_course = $DB->get_field_sql($sql);
						$link_course = new moodle_url('/course/view.php', ['id' => $course_id]);
						$link = html_writer::link($link_course, $fullname_course);
						$check_import->error_messages[] = "<p>Không tìm thấy câu hỏi có tên: <strong>$filename</strong> trong khóa học (<strong>$link</strong>)</p>";
					}
				}

				$zipentry = zip_read($zipres);

			} else {
				$list_question = $DB->get_record_sql("SELECT q.* FROM {question} as q, {context} as c, {question_categories} as qc WHERE q.name = '$filename' AND q.category= qc.id
						AND qc.contextid = c.id AND c.contextlevel = '50' AND c.instanceid = '$course_id'");

				if (!empty($list_question)) {

					$check_import->valid_import_found += 1;
					$data = new stdClass();
					$data->imagename = $imagename;
					$data->imagedata = $imagedata;
					$data->list_question = $list_question;
					$data->filename = $filename;
					$data->courseid = $course_id;
					$check_import->import_audio[] = $data;

				} else {

					$sql = "SELECT fullname FROM {course} WHERE id = $course_id";
					$fullname_course = $DB->get_field_sql($sql);
					$link_course = new moodle_url('/course/view.php', ['id' => $course_id]);
					$link = html_writer::link($link_course, $fullname_course);
					$check_import->error_messages[] = "<p>Không tìm thấy câu hỏi có tên: <strong>$filename</strong> trong khóa học (<strong>$link</strong>)</p>";
				}
				$zipentry = zip_read($zipres);
			}	
		}

		zip_close($zipres);

		// print_object($check_import);
		// exit;

		$th_import_audio_key = $courseid . '_' . time();
		$SESSION->block_th_import_audio[$th_import_audio_key] = $check_import;

	} else {
		echo $OUTPUT->header();
		echo $OUTPUT->heading('<center>THÊM AUDIO VÀO NGÂN HÀNG CÂU HỎI</center>');
		$th_import_audio_form->display();
		echo $OUTPUT->footer();
	}
}

if ($th_import_audio_key) {
	$form2 = new confirm_form(null, array('th_import_audio_key' => $th_import_audio_key));

	if ($form2->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/blocks/th_import_audio/view.php');
		redirect($courseurl);
	} else if ($formdata = $form2->get_data()) {

		if (
			!empty($th_import_audio_key) && !empty($SESSION->block_th_import_audio) &&
			array_key_exists($th_import_audio_key, $SESSION->block_th_import_audio)
		) {

			$import_audio_data = $SESSION->block_th_import_audio[$th_import_audio_key];
			$import_audio = $import_audio_data->import_audio;

			foreach ($import_audio as $import) {

				$imagename = $import->imagename;
				$imagedata = $import->imagedata;
				$filename = $import->filename;
				$list_question = $import->list_question;
				$question_id = $list_question->id;

				$usercontext = context_user::instance($USER->id);
				$contextid = $usercontext->id;
				$component = 'user';
				$filearea = 'draft';

				$fs = get_file_storage();
				$draftitemid = file_get_unused_draft_itemid();

				$filerecord = array(
					'contextid' => $contextid,
					'component' => 'user',
					'filearea' => 'draft',
					'itemid' => $draftitemid,
					'filepath' => '/',
					'filename' => $imagename,
				);

				$fs->create_file_from_string($filerecord, $imagedata, $USER->id);

				$contextid1 = context_course::instance($import->courseid)->id;

				file_save_draft_area_files($draftitemid, $contextid1, 'question', 'questiontext', $list_question->id);

				//save log
				$log_import_audio = new stdClass();
				$log_import_audio->contextid = $contextid1;
				$log_import_audio->option = 1;
				$log_import_audio->itemid = $list_question->id;
				$log_import_audio->filename = $imagename;
				$log_import_audio->courseid = $import->courseid;
				$log_import_audio->timecreated = time();
				$DB->insert_record('th_log_import_audio', $log_import_audio);

				$questiontext = "<audio controls='true'><source src='@@PLUGINFILE@@/$imagename'>@@PLUGINFILE@@/$imagename</audio>";
				$questiontext1 = file_rewrite_pluginfile_urls($questiontext, 'draftfile.php',
					context_user::instance($USER->id)->id, 'user', 'draft', $draftitemid);

				// save
				$question = $DB->get_record('question', array('id' => $list_question->id));
				get_question_options($question, true, [$COURSE]);
				$category = $DB->get_record('question_categories', array('id' => $question->category));

				$toform = fullclone($question); // send the question object and a few more parameters to the form
				$toform->category = "{$category->id},{$category->contextid}";
				$toform->scrollpos = 0;
				$toform->categorymoveto = $toform->category;
				$toform->appendqnumstring = null;
				$toform->returnurl = null;
				$toform->makecopy = 0;
				$toform->courseid = $import->courseid;
				$toform->inpopup = 0;
				$thiscontext = context_course::instance($import->courseid);
				$contexts = new question_edit_contexts($thiscontext);
				$categorycontext = context::instance_by_id($category->contextid);
				$addpermission = has_capability('moodle/question:add', $categorycontext);
				$question->formoptions = new stdClass();
				$question->formoptions->canedit = question_has_capability_on($question, 'edit');
				$question->formoptions->canmove = $addpermission && question_has_capability_on($question, 'move');
				$question->formoptions->cansaveasnew = $addpermission &&
					(question_has_capability_on($question, 'view') || $question->formoptions->canedit);
				$question->formoptions->repeatelements = $question->formoptions->canedit || $question->formoptions->cansaveasnew;
				$formeditable = $question->formoptions->canedit || $question->formoptions->cansaveasnew || $question->formoptions->canmove;
				$qtypeobj = question_bank::get_qtype($question->qtype);

				$mform = $qtypeobj->create_editing_form('question.php', $question, $category, $contexts, $formeditable);
				$mform->set_data($toform);

				$pos = strpos($list_question->questiontext, "[audio]");

				if ($pos !== false) {
					$question_text = str_replace("[audio]", '</br>' . $questiontext1 . '</br>', $list_question->questiontext);
					$toform->questiontext['text'] = $question_text;
					$toform->questiontext['itemid'] = $draftitemid;
				} else {
					$question_text = $list_question->questiontext . $questiontext1;
					$toform->questiontext['text'] = $question_text;
					$toform->questiontext['itemid'] = $draftitemid;
				}

				$question = $qtypeobj->save_question($question, $toform);
				// Purge this question from the cache.
				question_bank::notify_question_edited($question->id);
			}
			redirect($CFG->wwwroot . "/blocks/th_import_audio/view.php", 'Thêm audio vào câu hỏi thành công', null, \core\output\notification::NOTIFY_SUCCESS);
		} else {
			redirect($CFG->wwwroot . "/blocks/th_import_audio/view.php", 'Thêm audio vào câu hỏi thất bại', null, \core\output\notification::NOTIFY_ERROR);
		}

	} else {
		echo $OUTPUT->header();
		echo $OUTPUT->heading('<center>THÊM AUDIO VÀO NGÂN HÀNG CÂU HỎI</center>');

		if (
			!empty($th_import_audio_key) && !empty($SESSION->block_th_import_audio) &&
			array_key_exists($th_import_audio_key, $SESSION->block_th_import_audio)
		) {

			$import_audio_data = $SESSION->block_th_import_audio[$th_import_audio_key];

			if (!empty($import_audio_data->error_messages)) {
				$errors = $import_audio_data->error_messages;

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

			if (!empty($import_audio_data->import_audio)) {

				$import_audio = $import_audio_data->import_audio;
				$html1 = th_display_table_import_audio($import_audio);
				echo $OUTPUT->heading('<center><h3>CÁC CÂU HỎI SẼ ĐƯỢC THÊM AUDIO</h3></center>');
				echo $html1;
			}
		}

		if (empty($import_audio_data->valid_import_found)) {
			$a = new stdClass();
			$url = new moodle_url('/blocks/th_import_audio/view.php');
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

