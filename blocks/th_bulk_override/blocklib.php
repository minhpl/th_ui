<?php

defined('MOODLE_INTERNAL') || die();

function th_override_csv_parse_time($list_time)
{
	if (empty($list_time)) {
		return array();
	} else {
		$rawlines = explode(PHP_EOL, $list_time);
		$result = array();
		foreach ($rawlines as $rawline) {
			$result[] = trim($rawline);
		}
		return $result;
	}
}

function th_get_content($contents)
{
	$max = count($contents) - 1;
	for ($i = 1; $i < $max; ++$i) {
		$listcourses_new[] = $contents[$i];
	}
	return $listcourses_new;
}

function th_get_timestamp($time)
{
	$time_arr = explode('-', $time);
	$day_arr = explode('/', $time_arr[1]);
	$day = $day_arr[2] . '/' . $day_arr[1] . '/' . $day_arr[0];

	$subject = $time_arr[0];
	$search = ['h', 'p', 's'];
	$replace = [':', ':', null];
	$date = str_replace($search, $replace, $subject);
	$timestamp = strtotime("$day $date");

	return $timestamp;
}

function th_override_check_times($listtimes)
{
	global $DB, $CFG;

	$checkedtimes = new stdClass();
	$checkedtimes->error_messages = array();
	$checkedtimes->quiz_override = array();
	$checkedtimes->valid_time_found = 0;

	if (!empty($listtimes)) {

		foreach ($listtimes as $k => $listtime) {
			$line = $k + 2;
			$listtime_arr = explode(",", $listtime);
			$shortname = trim($listtime_arr[0]);
			$sql = "SELECT * FROM {course} WHERE shortname = '$shortname'";
			$course = $DB->get_record_sql($sql);

			if (empty($course)) {
				$checkedtimes->error_messages[] = "Không tìm thấy khóa học nào có shortname ($shortname).Vui lòng kiểm tra lại hàng $line";
			} else {

				$href = $CFG->wwwroot . "/course/view.php?id=$course->id";
				$link = "<a href='$href'>$course->fullname,$course->shortname</a>";
				$date = trim($listtime_arr[3]);
				$pos1 = substr_count($date, ' -> ');

				if (empty($pos1)) {
					$checkedtimes->error_messages[] = "Thời gian gia hạn ($date) sai định dạng. Vui lòng kiểm tra lại hàng $line.";
				} else {
					$attempts = trim($listtime_arr[4]);
					if (empty($attempts)){
						$group_name = trim($listtime_arr[2]) . ' - ' . $date . ' - lần';
					} else {
						$group_name = trim($listtime_arr[2]) . ' - ' . $date . ' - ' . $attempts . ' lần';
					}
					$courseid = $course->id;

					$date_arr = explode(' -> ', $date);
					$timeopen = $date_arr[0];
					$timeclose = $date_arr[1];

					$pos2 = substr_count($timeopen, '-');
					$pos3 = substr_count($timeopen, '/');
					$pos4 = substr_count($timeclose, '-');
					$pos5 = substr_count($timeclose, '/');

					if ($pos2 == 1 && $pos3 == 2 && $pos4 == 1 && $pos5 == 2) {

						$timeopen_timestamp = th_get_timestamp($timeopen);
						$timeclose_timestamp = th_get_timestamp($timeclose);

						$list_quiz = $listtime_arr[1];
						$pos = substr_count($list_quiz, ';');

						if ($pos >= 1) {
							$list_quizs = explode(";", $list_quiz);

							foreach ($list_quizs as $k => $quizz) {

								$quiz = trim($quizz);
								$quiz_id = $DB->get_field_sql("SELECT id FROM {quiz} WHERE course = $courseid AND name = '$quiz'");

								if (empty($quiz_id)) {
									$checkedtimes->error_messages[] = "Không tìm thấy bài kiểm tra ($quiz) trong khóa học ($link). Vui lòng kiểm tra lại hàng $line.";
								} else {

									$checkedtimes->valid_time_found += 1;
									$course = $DB->get_record_sql($sql);
									$course->quiz_id = $quiz_id;
									$course->quiz_name = $quiz;
									$course->timeopen_timestamp = $timeopen_timestamp;
									$course->timeclose_timestamp = $timeclose_timestamp;
									$course->so_lan_lam = $attempts;
									$course->time_open_close = $date;
									$course->group_name = $group_name;
									$checkedtimes->quiz_override[] = $course;
								}
							}
						} else {
							$quiz = trim($list_quiz);
							$quiz_id = $DB->get_field_sql("SELECT id FROM {quiz} WHERE course = $courseid AND name = '$quiz'");
							if (empty($quiz_id)) {
								$checkedtimes->error_messages[] = "Không tìm thấy bài kiểm tra ($quiz) trong khóa học ($link). Vui lòng kiểm tra lại hàng $line";
							} else {
								$checkedtimes->valid_time_found += 1;
								$course->quiz_id = $quiz_id;
								$course->quiz_name = $quiz;
								$course->timeopen_timestamp = $timeopen_timestamp;
								$course->timeclose_timestamp = $timeclose_timestamp;
								$course->so_lan_lam = $attempts;
								$course->time_open_close = $date;
								$course->group_name = $group_name;
								$checkedtimes->quiz_override[] = $course;
							}
						}
					} else {
						$checkedtimes->error_messages[] = "Thời gian gia hạn ($date) sai định dạng. Vui lòng kiểm tra lại hàng $line.";
					}
				}
			}
		}
	}
	return $checkedtimes;
}

function th_override_check_times1($listtimes)
{
	global $DB, $CFG;
	$checkedtimes = new stdClass();
	$checkedtimes->error_messages = array();
	$checkedtimes->quiz_override = array();
	$checkedtimes->valid_time_found = 0;

	if (!empty($listtimes)) {
		foreach ($listtimes as $k => $listtime) {
			$line = $k + 2;
			$listtime_arr = explode(",", $listtime);
			$shortname = trim($listtime_arr[0]);
			$sql = "SELECT * FROM {course} WHERE shortname = '$shortname'";
			$course = $DB->get_record_sql($sql);

			if (empty($course)) {
				$checkedtimes->error_messages[] = "Không tìm thấy khóa học nào có shortname ($shortname).Vui lòng kiểm tra lại hàng $line";
			} else {
				$href = $CFG->wwwroot . "/course/view.php?id=$course->id";
				$link = "<a href='$href'>$course->fullname,$course->shortname</a>";

				$date = trim($listtime_arr[3]);
				$pos1 = substr_count($date, ' -> ');

				if (empty($pos1)) {
					$checkedtimes->error_messages[] = "Thời gian gia hạn ($date) sai định dạng. Vui lòng kiểm tra lại hàng $line.";
				} else {
					$attempts = trim($listtime_arr[4]);
					$group_name = trim($listtime_arr[2]) . ' - ' . $date . ' - ' . $attempts . ' lần';
					$courseid = $course->id;
					$date_arr = explode(' -> ', $date);
					$timeopen = $date_arr[0];
					$timeclose = $date_arr[1];

					$pos2 = substr_count($timeopen, '-');
					$pos3 = substr_count($timeopen, '/');
					$pos4 = substr_count($timeclose, '-');
					$pos5 = substr_count($timeclose, '/');

					if ($pos2 == 1 && $pos3 == 2 && $pos4 == 1 && $pos5 == 2) {
						$timeopen_timestamp = th_get_timestamp($timeopen);
						$timeclose_timestamp = th_get_timestamp($timeclose);
						$quiz = trim($listtime_arr[1]);
						$list_quiz = $DB->get_records_sql("SELECT id FROM {quiz} WHERE course = $courseid AND name LIKE '$quiz%' AND NOT name LIKE '%tổng hợp'");

						if (empty($list_quiz)) {
							$checkedtimes->error_messages[] = "Không tìm thấy bài kiểm tra ($quiz) trong khóa học ($link). Vui lòng kiểm tra lại hàng $line.";
						} else {
							foreach ($list_quiz as $k => $quizz) {
								$quiz_id = $quizz->id;
								$quiz1 = $DB->get_record_sql("SELECT * FROM {quiz} WHERE id = '$quiz_id'");

								$checkedtimes->valid_time_found += 1;
								$course = $DB->get_record_sql($sql);
								$course->quiz_id = $quiz_id;
								$course->quiz_name = $quiz1->name;
								$course->timeopen_timestamp = $timeopen_timestamp;
								$course->timeclose_timestamp = $timeclose_timestamp;
								$course->so_lan_lam = $attempts;
								$course->time_open_close = $date;
								$course->group_name = $group_name;
								$checkedtimes->quiz_override[] = $course;
							}
						}
					} else {
						$checkedtimes->error_messages[] = "Thời gian gia hạn ($date) sai định dạng. Vui lòng kiểm tra lại hàng $line.";
					}
				}
			}
		}
	}
	return $checkedtimes;
}

function th_display_table_override($data_override)
{
	global $DB;
	$table = new html_table();
	$table->head = array('STT', 'Tên khóa học', 'Tên rút gọn khóa học', 'Tên bài kiểm tra', 'Thời gian gia hạn', 'Số lần làm bài', 'Group');
	$stt = 0;
	foreach ($data_override as $k => $data) {
		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;

		$fullname = $data->fullname;
		$link = new moodle_url('/course/view.php', ['id' => $data->id]);
		$link_edit = html_writer::link($link, $fullname);
		$cell = new html_table_cell($link_edit);
		$row->cells[] = $cell;
		$cell = new html_table_cell($data->shortname);
		$row->cells[] = $cell;
		$cell = new html_table_cell($data->quiz_name);
		$row->cells[] = $cell;
		$cell = new html_table_cell($data->time_open_close);
		$row->cells[] = $cell;
		if (empty($data->so_lan_lam)){
			$cell = new html_table_cell('Không giới hạn');
			$row->cells[] = $cell;
		} else {
			$cell = new html_table_cell($data->so_lan_lam);
			$row->cells[] = $cell;
		}

		$cell = new html_table_cell($data->group_name);
		$row->cells[] = $cell;
		$table->data[] = $row;
	}
	$html = html_writer::table($table);
	return $html;
}

function th_display_table_error($errors)
{
	$table1 = new html_table($errors);
	$table1->head = array(get_string('STT', 'block_th_bulk_override'), get_string('Hints', 'block_th_bulk_override'));
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
