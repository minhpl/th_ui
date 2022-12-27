<?php

function th_import_audio_list_courses() {
    global $DB;
    $listcourses = [];
    $listcourses[] = '';
    $sql = "SELECT * FROM {course} WHERE NOT id = 1";
    $courses = $DB->get_records_sql($sql);
    if (!empty($courses)) {
        foreach ($courses as $id => $course) {
            $listcourses[$id] = $course->fullname;
        }
    }
    return $listcourses;
}

function th_display_table_import_audio($import_audio){
	global $DB;
	
	$table       = new html_table();
	$table->head = array('STT', 'Tên audio', 'Tên câu hỏi', 'Câu hỏi', 'Khóa học', 'Trạng thái');
	$stt         = 0;

	foreach($import_audio as $k => $import) {
		$courseid = $import->courseid;
		$sql = "SELECT fullname FROM {course} WHERE id = $courseid";
		$fullname_course = $DB->get_field_sql($sql);

		$link_course = new moodle_url('/course/view.php', ['id' => $courseid]);
		$link = html_writer::link($link_course, $fullname_course);

		$stt            = $stt + 1;
		$row            = new html_table_row();
		$cell           = new html_table_cell($stt);
		$row->cells[]   = $cell;
		$cell           = new html_table_cell($import->imagename);
		$row->cells[]   = $cell;
		$cell           = new html_table_cell($import->filename);
		$row->cells[]   = $cell;
		$cell           = new html_table_cell($import->list_question->questiontext);
		$row->cells[]   = $cell;
		$cell           = new html_table_cell($link);
		$row->cells[]   = $cell;
		$cell           = new html_table_cell();
		$cell->text = html_writer::tag('span', 'Audio sẽ được thêm vào câu hỏi',
			array('class' => 'badge badge-success'));
		$row->cells[]   = $cell;
		$table->data[] = $row;
	}
	$table->attributes = array('class' => 'th_import_audio_table', 'border' => '1');
	$table->attributes['style'] = "width: 100%; text-align:center;";
	$html = html_writer::table($table);
	return $html;
}

?>