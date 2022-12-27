<?php

defined('MOODLE_INTERNAL') || die();

function th_clone_csv_parse_course($listcourses) {
	if (empty($listcourses)) {
		return array();
	} else {
		$rawlines = explode(PHP_EOL, $listcourses);
		$result = array();
		foreach ($rawlines as $rawline) {
			$result[] = trim($rawline);
		}
		return $result;
	}
}

function th_get_content($contents) {
	$max = count($contents) - 1;
	for ($i = 1; $i < $max; ++$i) {
		$listcourses_new[] = $contents[$i];
	}
	return $listcourses_new;
}

function get_startdate($shortname) {
	global $DB;
	$list_startdate = [];
	$sql = "SELECT startdate FROM {course} WHERE shortname LIKE BINARY '$shortname%' ORDER BY startdate DESC";
	$startdate = $DB->get_records_sql($sql);
	foreach ($startdate as $k => $course) {
		$list_startdate[] = $course->startdate;
	}

	return $list_startdate;
}

function get_category($course_id) {
	global $DB;
	$sql = "SELECT category FROM {course} WHERE id = $course_id";
	$category = $DB->get_field_sql($sql);
	return $category;
}

function th_clone_csv_check_courses($listcourses) {
	global $DB, $USER, $CFG;

	$checkedcourses = new stdClass();
	$checkedcourses->error_messages = array();
	$checkedcourses->courses_copy = array();
	$checkedcourses->validemailfound = 0;

	if (!empty($listcourses)) {

		foreach ($listcourses as $k => $listcourse) {
			$listcourse_arr = explode(",", $listcourse);
			$shortname = $listcourse_arr[0];

			$list_startdate = get_startdate($shortname);
			$line = $k + 2;

			if (empty($list_startdate)) {
				$checkedcourses->error_messages[] = "Không tìm thấy khóa học có shortname ($shortname).Vui lòng kiểm tra lại hàng $line.";
			} else {
				$startdate = $listcourse_arr[1];

				$pos = substr_count($startdate, '/');

				if ($pos == 2) {
					$startdate_arr = explode("/", $startdate);
					$startdate1 = $startdate_arr[2] . '-' . $startdate_arr[1] . '-' . $startdate_arr[0];
					$startdate_timstamp = strtotime("$startdate1");

					if (empty($startdate_timstamp)) {
						$checkedcourses->error_messages[] = "Ngày bắt đầu sai định dạng ($startdate).Vui lòng kiểm tra lại hàng $line.";
					} else {
						$startdate_copy = date('ymd', $startdate_timstamp);
						$startdate_max = max($list_startdate);
						$course_max = $DB->get_record_sql("SELECT * FROM {course} WHERE shortname LIKE BINARY '$shortname%' AND startdate = '$startdate_max'");
						$fullname_max = $course_max->fullname;
						$id_fullname_max = $course_max->id;
						$shortname_max = $course_max->shortname;

						if (preg_match('/.*( {1}- {1})[0-9]{6}/', $fullname_max, $matches1)) {
							if (preg_match('/.*[A-Z0-9]+-[0-9]{6}/', $shortname_max, $matches2)) {
								$shortname_copy = $shortname . '-' . $startdate_copy;
								$sql = "SELECT * FROM {course} WHERE shortname = '$shortname_copy'";
								$copies = \core_backup\copy\copy::get_copies($USER->id, $course_max->id);

								if ($DB->record_exists_sql($sql) == 1) {
									$checkedcourses->error_messages[] = "Hàng $line có shortname ($shortname) đã tồn tại với ngày bắt đầu $startdate.Vui lòng kiểm tra lại.";
								} else if (!empty($copies)) {
									$checkedcourses->error_messages[] = "Hàng $line có shortname ($shortname) đang được copy với ngày $startdate. Vui lòng kiểm tra lại.";
								} else {
									$checkedcourses->validemailfound += 1;
									$course_max->startdate1 = $startdate_copy;
									$course_max->startdate2 = $startdate;
									$course_max->startdate_timstamp = $startdate_timstamp;
									$checkedcourses->courses_copy[$k] = $course_max;
								}

							} else {
								$href = $CFG->wwwroot . "/course/edit.php?id=$id_fullname_max";
								$link = "<a href='$href'>$fullname_max</a>";
								$checkedcourses->error_messages[] = "Hàng $line có shortname ($shortname) có khóa học ban đầu ($link) sai định dạng tên rút gọn khóa học.Vui lòng kiểm tra lại.";
							}

						} else {
							$href = $CFG->wwwroot . "/course/edit.php?id=$id_fullname_max";
							$link = "<a href='$href'>$fullname_max</a>";
							$checkedcourses->error_messages[] = "Hàng $line có shortname ($shortname) có khóa học ban đầu ($link) sai định dạng tên khóa học.Vui lòng kiểm tra lại.";
						}
					}
				} else {
					$checkedcourses->error_messages[] = "Ngày bắt đầu sai định dạng ($startdate).Vui lòng kiểm tra lại hàng $line.";
				}

			}
		}
	}

	return $checkedcourses;
}

function th_display_table_clone($courses_copy) {
	global $DB;
	$table = new html_table();
	$table->head = array('STT', 'Tên khóa học', 'Tên rút gọn khóa học', 'Trạng thái', 'Ngày bắt đầu khóa học');
	$stt = 0;
	foreach ($courses_copy as $k => $course_copy) {
		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;

		$fullname = $course_copy->fullname;
		$link = new moodle_url('/course/view.php', ['id' => $course_copy->id]);
		$link_edit = html_writer::link($link, $fullname);
		$cell = new html_table_cell($link_edit);
		$row->cells[] = $cell;

		$cell = new html_table_cell($course_copy->shortname);
		$row->cells[] = $cell;
		$cell = new html_table_cell('Khóa học sẽ được copy');
		$row->cells[] = $cell;

		$cell = new html_table_cell($course_copy->startdate2);
		$row->cells[] = $cell;

		$table->data[] = $row;
	}

	$html = html_writer::table($table);
	return $html;
}

function th_display_table_error($errors) {
	$table1 = new html_table($errors);
	$table1->head = array(get_string('STT', 'block_th_clone_course'), get_string('Hints', 'block_th_clone_course'));
	$stt = 0;
	foreach ($errors as $k => $error) {
		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell($error);
		$row->cells[] = $cell;
		$table1->data[] = $row;
	}
	$html1 = html_writer::table($table1);
	return $html1;
}
