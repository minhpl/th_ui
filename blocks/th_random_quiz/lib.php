<?php

function get_list_courses_mau() {
	global $DB;
	$listcourses = [];
	$sql = "SELECT * FROM {course} WHERE NOT id = 1 AND fullname LIKE '%mẫu'";
	$courses = $DB->get_records_sql($sql);
	if (!empty($courses)) {
		foreach ($courses as $id => $course) {
			$listcourses[$id] = $course->fullname;
		}
	}
	return $listcourses;
}

function get_list_courses_random() {
	global $DB;
	$listcourses = [];
	$sql = "SELECT * FROM {course} WHERE NOT id = 1";
	$courses = $DB->get_records_sql($sql);
	if (!empty($courses)) {
		foreach ($courses as $id => $course) {
			$listcourses[$id] = $course->fullname;
		}
	}
	return $listcourses;
}

function get_list_courses_id() {
	global $DB;
	$listcourses = [];
	$sql = "SELECT * FROM {course} WHERE NOT id = 1 AND fullname LIKE '%mẫu'";
	$courses = $DB->get_records_sql($sql);
	if (!empty($courses)) {
		foreach ($courses as $id => $course) {
			$listcourses[] = $course->id;
		}
	}
	return $listcourses;
}

function get_question_count($id) {
	global $DB;
	$sql = "SELECT COUNT(id) FROM {question} WHERE category = '$id' AND parent = '0' AND hidden='0'";
	$questioncount = $DB->get_field_sql($sql);
	return $questioncount;
}

function data_quiz($i, $courseid, $module, $thoi_gian) {

	global $DB;

	$course_name = $DB->get_field_sql("SELECT fullname FROM {course} WHERE id = '$courseid'");

	$data_quiz = new stdClass();
	$data_quiz->name = $course_name . "_" . $thoi_gian . "_" . $i;
	$data_quiz->introeditor = [
		'text' => '',
		'format' => 1,
		'itemid' => '',
	];

	$data_quiz->showdescription = 0;
	$data_quiz->timeopen = 0;
	$data_quiz->timeclose = 0;
	$data_quiz->timelimit = 0;
	$data_quiz->overduehandling = 'autosubmit';
	$data_quiz->graceperiod = 0;
	$data_quiz->gradecat = '';
	$data_quiz->gradepass = '';
	$data_quiz->grade = 10;
	$data_quiz->attempts = 0;
	$data_quiz->grademethod = 1;
	$data_quiz->questionsperpage = 1;
	$data_quiz->navmethod = 'free';
	$data_quiz->shuffleanswers = 0;
	$data_quiz->preferredbehaviour = 'deferredfeedback';
	$data_quiz->canredoquestions = 0;
	$data_quiz->attemptonlast = 0;
	$data_quiz->attemptimmediately = 1;
	$data_quiz->correctnessimmediately = 1;
	$data_quiz->marksimmediately = 1;
	$data_quiz->specificfeedbackimmediately = 1;
	$data_quiz->generalfeedbackimmediately = 1;
	$data_quiz->rightanswerimmediately = 1;
	$data_quiz->overallfeedbackimmediately = 1;
	$data_quiz->attemptopen = 1;
	$data_quiz->correctnessopen = 1;
	$data_quiz->marksopen = 1;
	$data_quiz->specificfeedbackopen = 1;
	$data_quiz->generalfeedbackopen = 1;
	$data_quiz->rightansweropen = 1;
	$data_quiz->overallfeedbackopen = 1;
	$data_quiz->showuserpicture = 0;
	$data_quiz->decimalpoints = 2;
	$data_quiz->questiondecimalpoints = -1;
	$data_quiz->showblocks = 0;
	$data_quiz->seb_requiresafeexambrowser = 0;
	$data_quiz->filemanager_sebconfigfile = '';
	$data_quiz->seb_showsebdownloadlink = 1;
	$data_quiz->seb_linkquitseb = '';
	$data_quiz->seb_userconfirmquit = 1;
	$data_quiz->seb_allowuserquitseb = 1;
	$data_quiz->seb_quitpassword = '';
	$data_quiz->seb_allowreloadinexam = 1;
	$data_quiz->seb_showsebtaskbar = 1;
	$data_quiz->seb_showreloadbutton = 1;
	$data_quiz->seb_showtime = 1;
	$data_quiz->seb_showkeyboardlayout = 1;
	$data_quiz->seb_showwificontrol = 0;
	$data_quiz->seb_enableaudiocontrol = 0;
	$data_quiz->seb_muteonstartup = 0;
	$data_quiz->seb_allowspellchecking = 0;
	$data_quiz->seb_activateurlfiltering = 0;
	$data_quiz->seb_filterembeddedcontent = 0;
	$data_quiz->seb_expressionsallowed = '';
	$data_quiz->seb_regexallowed = '';
	$data_quiz->seb_expressionsblocked = '';
	$data_quiz->seb_regexblocked = '';
	$data_quiz->seb_allowedbrowserexamkeys = '';
	$data_quiz->quizpassword = '';
	$data_quiz->subnet = '';
	$data_quiz->delay1 = 0;
	$data_quiz->delay2 = 0;
	$data_quiz->browsersecurity = '-';
	$data_quiz->allowofflineattempts = 0;
	$data_quiz->boundary_repeats = 1;
	$data_quiz->feedbacktext = [
		'0' => [
			'text' => '',
			'format' => 1,
			'itemid' => '',
		],

		'1' => [
			'text' => '',
			'format' => 1,
			'itemid' => '',
		],
	];

	$data_quiz->feedbackboundaries =
		[
		'0' => '',
	];

	$data_quiz->visible = 1;
	$data_quiz->visibleoncoursepage = 1;
	$data_quiz->cmidnumber = '';
	$data_quiz->groupmode = 0;
	$data_quiz->groupingid = 0;
	$data_quiz->availabilityconditionsjson = null;
	$data_quiz->completionunlocked = 1;
	$data_quiz->completion = 1;
	$data_quiz->completionpass = 0;
	$data_quiz->completionattemptsexhausted = 0;
	$data_quiz->completionminattempts = 0;
	$data_quiz->completionexpected = 0;
	$data_quiz->tags = [];
	$data_quiz->course = $courseid;
	$data_quiz->coursemodule = 0;
	$data_quiz->section = 0;
	$data_quiz->module = $module;
	$data_quiz->modulename = 'quiz';
	$data_quiz->instance = 0;
	$data_quiz->add = 'quiz';
	$data_quiz->update = 0;
	$data_quiz->return = 0;
	$data_quiz->sr = 0;
	$data_quiz->competencies = [];
	$data_quiz->competency_rule = 0;

	return $data_quiz;
}

function th_display_table_random_quiz($random_quiz) {
	global $DB;

	$table = new html_table();
	$table->head = array('STT', 'Tên bài kiểm tra tạo', 'Số câu hỏi', 'Khóa học', 'Ngẫu nhiên câu hỏi theo', 'Khóa học lưu trữ', 'Trạng thái');
	$stt = 0;

	foreach ($random_quiz as $k => $random) {

		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell("<strong>" . $random->data_quiz->name . "</strong>");
		$row->cells[] = $cell;
		$cell = new html_table_cell($random->numbertoadd);
		$row->cells[] = $cell;

		$link_course = new moodle_url('/course/view.php', ['id' => $random->course->id]);
		$link = html_writer::link($link_course, $random->course->fullname);

		$cell = new html_table_cell($link);
		$row->cells[] = $cell;
		if ($random->option_add == 0) {
			$cell = new html_table_cell('Ngẫu nhiên cả kho');
			$row->cells[] = $cell;
		} else {
			$cell = new html_table_cell('20% khó, 40% trung bình, 40% dễ');
			$row->cells[] = $cell;
		}

		$course_save = $random->course_save;
		$link_course_save = new moodle_url('/course/view.php', ['id' => $course_save->id]);
		$link_save = html_writer::link($link_course_save, $course_save->fullname);

		$cell = new html_table_cell($link_save);
		$row->cells[] = $cell;

		$cell = new html_table_cell();
		$cell->text = html_writer::tag('span', 'Bài kiểm tra sẽ được tạo ngẫu nhiên câu hỏi',
			array('class' => 'badge badge-success'));
		$row->cells[] = $cell;
		$table->data[] = $row;
	}
	$table->attributes = array('class' => 'th_random_quiz_table', 'border' => '1');
	$table->attributes['style'] = "width: 100%; text-align:center;";
	$html = html_writer::table($table);
	return $html;
}

?>