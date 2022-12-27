<?php

defined('MOODLE_INTERNAL') || die();

function th_parse_groups($list_groups)
{
	if (empty($list_groups)) {
		return array();
	} else {
		$rawlines = explode(PHP_EOL, $list_groups);
		$result = array();
		foreach ($rawlines as $rawline) {
			$result[] = trim($rawline);
		}
		return $result;
	}
}

function th_enrol_groups_get_content($contents)
{
	$max = count($contents) - 1;
	for ($i = 1; $i < $max; ++$i) {
		$list_new[] = $contents[$i];
	}
	return $list_new;
}

function th_check_groups($list_groups)
{
	global $DB, $CFG;

	$checkedgroups = new stdClass();
	$checkedgroups->error_messages = array();
	$checkedgroups->enrol_groups = array();
	$checkedgroups->valid_groups_found = 0;

	if (!empty($list_groups)) {

		foreach ($list_groups as $k => $list_group) {
			$line = $k + 2;
			$list_groups_arr = explode(",", $list_group);
			$shortname = trim($list_groups_arr[0]);
			$group_name = trim($list_groups_arr[1]);
			$email = trim($list_groups_arr[2]);
			$course = $DB->get_record_sql("SELECT * FROM {course} WHERE shortname = '$shortname'");
			$user = $DB->get_record_sql("SELECT * FROM {user} WHERE email = '$email'");

			if (empty($course)) {
				$checkedgroups->error_messages[] = "Không tìm thấy khóa học nào có shortname (<strong>$shortname</strong>). Vui lòng kiểm tra lại hàng $line";
			} else {
				$course_id = $course->id;
				$group = $DB->get_record_sql("SELECT * FROM {groups} WHERE name = '$group_name' AND courseid = '$course_id'");
				$href = $CFG->wwwroot . "/group/index.php?id=$course_id";
				$link = "<a href='$href'>$course->fullname,$course->shortname</a>";
				
				if (empty($group)) {
					$checkedgroups->error_messages[] = "Không tìm thấy nhóm nào có tên (<strong>$group_name</strong>) trong môn học (<strong>$link</strong>). Vui lòng kiểm tra lại hàng $line";
				} else {
					if (empty($user)) {
						$checkedgroups->error_messages[] = "Không tìm thấy người dùng nào có email (<strong>$email</strong>).Vui lòng kiểm tra lại hàng $line";
					} else {
						$context = context_course::instance($course_id, MUST_EXIST);
						$userenroled = is_enrolled($context, $user->id);
						if(empty($userenroled)){
							$checkedgroups->error_messages[] = "Không tìm thấy người dùng nào có email (<strong>$email</strong>) trong môn học (<strong>$link</strong>). Vui lòng kiểm tra lại hàng $line";
						} else {
							$user_id = $user->id;
							$group_id = $group->id;
							$user_enrol = $DB->get_records_sql("SELECT * FROM {groups_members} WHERE userid = '$user_id' AND groupid = '$group_id'");
							if (empty($user_enrol)){
								$checkedgroups->valid_groups_found += 1;
								$enrol_groups = new stdClass();
								$enrol_groups->group_id = $group->id;
								$enrol_groups->group_name = $group->name;
								$enrol_groups->user_id = $user->id;
								$enrol_groups->user_name = $user->firstname . ' ' . $user->lastname;
								$enrol_groups->course_id = $course->id;
								$enrol_groups->course_name = $course->fullname;
								$checkedgroups->enrol_groups[] = $enrol_groups;
							} else {
								$checkedgroups->error_messages[] = "Người dùng có email (<strong>$email</strong>) đã tồn tại trong nhóm (<strong>$group_name</strong>) trong môn học (<strong>$link</strong>). Vui lòng kiểm tra lại hàng $line";
							}
						}
					}
				}
			}
		}
	}
	return $checkedgroups;
}

function th_display_table_enrol_groups($data_enrol_group)
{
	global $DB;
	$table = new html_table();
	$table->head = array('STT', 'Tên khóa học', 'Nhóm gia hạn', 'Tên học viên', 'Trạng thái');
	$stt = 0;
	foreach ($data_enrol_group as $k => $data) {
		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;

		$link = new moodle_url('/course/view.php', ['id' => $data->course_id]);
		$link_edit = html_writer::link($link, $data->course_name);
		$cell = new html_table_cell($link_edit);
		$row->cells[] = $cell;
		$cell = new html_table_cell($data->group_name);
		$row->cells[] = $cell;
		$cell = new html_table_cell($data->user_name);
		$row->cells[] = $cell;
		$cell = new html_table_cell('Học viên sẽ được thêm vào nhóm gia hạn');
		$row->cells[] = $cell;
		$table->data[] = $row;
	}
	$html = html_writer::table($table);
	return $html;
}

function th_display_table_error($errors)
{
	$table1 = new html_table($errors);
	$table1->head = array('STT', 'Gợi ý');
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
