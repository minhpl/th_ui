<?php

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/enrol/locallib.php';

function th_bulkenrol_csv_parse_emails($emails) {
	if (empty($emails)) {
		return array();
	} else {
		$rawlines = explode(PHP_EOL, $emails);
		$result = array();
		foreach ($rawlines as $rawline) {
			$result[] = trim($rawline);
		}
		return $result;
	}
}

function th_bulkenrol_csv_get_content($contents) {
	$max = count($contents) - 1;
	for ($i = 1; $i < $max; ++$i) {
		$list_new[] = $contents[$i];
	}
	return $list_new;
}

function th_bulkenrol_excel_check_user_mails($emails) {
	global $DB;

	$checkedemails = new stdClass();
	$checkedemails->error_messages = array();
	$checkedemails->error_arrays = array();
	$checkedemails->moodleusers_for_email = array();
	$checkedemails->user_enroled = array();
	$checkedemails->validemailfound = 0;

	if (!empty($emails)) {

		$context = null;

		// Process emails from textfield.
		foreach ($emails as $k => $email) {
			$line = trim($email[3]);
			$sheet = trim($email[1]);
			$shortname_course = trim($email[2]);

			$sql = "SELECT id FROM {course} WHERE shortname = '$shortname_course'";
			if ($DB->record_exists_sql($sql) == 1) {
				$emailline = trim($email[0]);
				$courseid = $DB->get_field_sql($sql);
			} else {
				$emailline = trim($email[0]);

				//Show error course shortname
				$error = "Không tìm thấy tên viết tắt của khóa học ($shortname_course) trong hàng $line của sheet ($sheet) . Hàng này sẽ bị bỏ qua.";
				$error_messages = $checkedemails->error_messages;
				if (!in_array($error, $error_messages)) {
					$checkedemails->error_messages[] = $error;
				}
				$error_arr = new stdClass();
				$error_arr->email = $emailline;
				$error_arr->shortname_course = $shortname_course;
				$error_arr->error = $error;
				$error_arr->line = $line;
				$error_arr->sheet = $sheet;
				$error_arr->courseid = null;
				$checkedemails->error_arrays[] = $error_arr;
			}

			$roleid = 5;

			// Check number of emails in current row/line.
			$emailsinlinecnt = substr_count($emailline, '@');
			// No email in row/line.
			if ($emailsinlinecnt == 0) {
				if ($DB->record_exists_sql($sql) == 1) {
					$error = "Không tìm thấy địa chỉ e-mail ($emailline) nào trong hàng $line của sheet ($sheet). Hàng này sẽ bị bỏ qua.";
					$checkedemails->error_messages[] = $error;

					$error_arr = new stdClass();
					$error_arr->email = $emailline;
					$error_arr->shortname_course = $shortname_course;
					$error_arr->error = $error;
					$error_arr->line = $line;
					$error_arr->sheet = $sheet;
					$error_arr->courseid = $courseid;
					$checkedemails->error_arrays[] = $error_arr;
				}
				// One email in row/line.
			} else if ($emailsinlinecnt == 1) {
				$email = $emailline;

				if ($DB->record_exists_sql($sql) == 1) {
					if ($DB->record_exists_sql("SELECT * FROM {user} WHERE email = '$email'") != 1) {
						$error = "Không có tài khoản người dùng ($emailline) hiện có trong hàng $line của sheet ($sheet).<br />Hàng này sẽ bị bỏ qua.";
						$checkedemails->error_messages[] = $error;

						$error_arr = new stdClass();
						$error_arr->email = $emailline;
						$error_arr->shortname_course = $shortname_course;
						$error_arr->error = $error;
						$error_arr->line = $line;
						$error_arr->sheet = $sheet;
						$error_arr->courseid = $courseid;
						$checkedemails->error_arrays[] = $error_arr;
					} else {
						$userrecord = $DB->get_record('user', array('email' => $email));
						$userisenrolled = th_bulkenrol_csv_check_email($userrecord->id, $courseid, $roleid);
						if (empty($userisenrolled)) {
							$checkedemails->validemailfound += 1;
							$userrecord->courseid = $courseid;
							$userrecord->shortname_course = $shortname_course;
							$userrecord->roleid = $roleid;
							$userrecord->sheetname = $sheet;
							$userrecord->line = $line;
							$checkedemails->user_enroled[] = $userrecord;
						} else {
							$userrecord->courseid = $courseid;
							$userrecord->shortname_course = $shortname_course;
							$userrecord->roleid = $roleid;
							$userrecord->sheetname = $sheet;
							$userrecord->line = $line;
							$checkedemails->moodleusers_for_email[] = $userrecord;
						}
					}
				}
			}
		}
	}
	return $checkedemails;
}

function th_bulkenrol_csv_check_user_mails($emails) {
	global $DB;

	$checkedemails = new stdClass();
	$checkedemails->error_messages = array();
	$checkedemails->error_arrays = array();
	$checkedemails->moodleusers_for_email = array();
	$checkedemails->user_enroled = array();
	$checkedemails->validemailfound = 0;

	if (!empty($emails)) {

		$context = null;

		// Process emails from textfield.
		foreach ($emails as $k => $email) {

			$line = $k + 2;
			$email_arr = explode(',', $email);
			$user_email = trim($email_arr[0]);
			$shortname_course = trim($email_arr[1]);

			$sql = "SELECT id FROM {course} WHERE shortname = '$shortname_course'";
			$course = $DB->get_record_sql($sql);
			if (!empty($course)) {
				$emailline = $user_email;
				$courseid = $course->id;
			} else {
				$emailline = trim($email[0]);

				//Show error course shortname
				$error = "Không tìm thấy tên viết tắt của khóa học ($shortname_course) trong hàng $line. Hàng này sẽ bị bỏ qua.";
				$error_messages = $checkedemails->error_messages;
				if (!in_array($error, $error_messages)) {
					$checkedemails->error_messages[] = $error;
				}
			}

			$roleid = 5;

			// Check number of emails in current row/line.
			$emailsinlinecnt = substr_count($emailline, '@');
			// No email in row/line.
			if ($emailsinlinecnt == 0) {
				if (!empty($course)) {
					$error = "Không tìm thấy địa chỉ e-mail ($emailline) nào trong hàng $line. Hàng này sẽ bị bỏ qua.";
					$error_arr = new stdClass();
					$error_arr->email = $emailline;
					$error_arr->shortname_course = $shortname_course;
					$error_arr->error = $error;
					$error_arr->line = $line;
					$error_arr->courseid = $courseid;
					$checkedemails->error_arrays[] = $error_arr;
				}
				// One email in row/line.
			} else if ($emailsinlinecnt == 1) {
				$email = $emailline;

				if ($DB->record_exists_sql($sql) == 1) {
					if ($DB->record_exists_sql("SELECT * FROM {user} WHERE email = '$email'") != 1) {
						$error = "Không có tài khoản người dùng ($emailline) hiện có trong hàng $line.<br />Hàng này sẽ bị bỏ qua.";
						$error_arr = new stdClass();
						$error_arr->email = $emailline;
						$error_arr->shortname_course = $shortname_course;
						$error_arr->error = $error;
						$error_arr->line = $line;
						$error_arr->courseid = $courseid;
						$checkedemails->error_arrays[] = $error_arr;
					} else {
						$userrecord = $DB->get_record('user', array('email' => $email));
						$userisenrolled = th_bulkenrol_csv_check_email($userrecord->id, $courseid, $roleid);
						if (empty($userisenrolled)) {
							$checkedemails->validemailfound += 1;
							$userrecord->courseid = $courseid;
							$userrecord->shortname_course = $shortname_course;
							$userrecord->roleid = $roleid;
							$userrecord->line = $line;
							$checkedemails->user_enroled[] = $userrecord;
						} else {
							$userrecord->courseid = $courseid;
							$userrecord->shortname_course = $shortname_course;
							$userrecord->roleid = $roleid;
							$userrecord->line = $line;
							$checkedemails->moodleusers_for_email[] = $userrecord;
						}
					}
				}
			}
		}
	}
	return $checkedemails;
}

function th_bulkenrol_csv_check_email($userid, $courseid, $roleid) {
	global $DB;

	$instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'), '*', MUST_EXIST);

	$sql = "SELECT * FROM {user_enrolments} WHERE userid = $userid AND enrolid = $instance->id";
	$sql1 = "SELECT id FROM {context} WHERE instanceid = $courseid AND contextlevel = 50";
	$contextid = $DB->get_record_sql($sql1);
	$sql2 = "SELECT * FROM {role_assignments} WHERE $userid AND roleid = $roleid AND contextid = $contextid->id";

	if ($DB->record_exists_sql($sql) == 1 && $DB->record_exists_sql($sql2) == 1) {
		$userisenrolled = 1;
	} else {
		$userisenrolled = 0;
	}
	return $userisenrolled;
}

function th_display_table_enrol($enroleds, $no_enroleds, $errors) {
	global $DB;
	$table = new html_table();
	$table->head = array(get_string('STT', 'block_th_bulk_enrol_student'), get_string('Email', 'block_th_bulk_enrol_student'), get_string('Firstname', 'block_th_bulk_enrol_student'), get_string('Lastname', 'block_th_bulk_enrol_student'), get_string('Course', 'block_th_bulk_enrol_student'), get_string('Shortname_Course', 'block_th_bulk_enrol_student'), get_string('Status', 'block_th_bulk_enrol_student'), 'Ghi chú');
	$stt = 0;

	foreach ($no_enroleds as $k => $no_enroled) {

		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell($no_enroled->email);
		$row->cells[] = $cell;
		$cell = new html_table_cell($no_enroled->firstname);
		$row->cells[] = $cell;
		$cell = new html_table_cell($no_enroled->lastname);
		$row->cells[] = $cell;

		$course_name = $DB->get_field_sql("SELECT fullname FROM {course} WHERE id = '$no_enroled->courseid'");
		$link = new moodle_url('/user/index.php', ['id' => $no_enroled->courseid]);
		$link_edit = html_writer::link($link, $course_name);
		$cell = new html_table_cell($link_edit);
		$row->cells[] = $cell;

		$cell = new html_table_cell($no_enroled->shortname_course);
		$row->cells[] = $cell;

		$role_name = $DB->get_field_sql("SELECT shortname FROM {role} WHERE id = '$no_enroled->roleid'");
		$message = get_string('user_enroled_yes', 'block_th_bulk_enrol_student') . $role_name;
		$cell = new html_table_cell("<strong style = 'color: #fff'>$message</strong>");
		$row->cells[] = $cell;
		$cell->attributes = array('class' => "bg-success");
		$cell = new html_table_cell('');
		$row->cells[] = $cell;
		$table->data[] = $row;
	}

	foreach ($enroleds as $k => $enroled) {
		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell($enroled->email);
		$row->cells[] = $cell;
		$cell = new html_table_cell($enroled->firstname);
		$row->cells[] = $cell;
		$cell = new html_table_cell($enroled->lastname);
		$row->cells[] = $cell;

		$course_name = $DB->get_field_sql("SELECT fullname FROM {course} WHERE id = '$enroled->courseid'");
		$link = new moodle_url('/user/index.php', ['id' => $enroled->courseid]);
		$link_edit = html_writer::link($link, $course_name);
		$cell = new html_table_cell($link_edit);
		$row->cells[] = $cell;

		$cell = new html_table_cell($enroled->shortname_course);
		$row->cells[] = $cell;

		$role_name = $DB->get_field_sql("SELECT shortname FROM {role} WHERE id = '$enroled->roleid'");

		$message = get_string('user_enroled_already', 'block_th_bulk_enrol_student') . $role_name;
		$cell = new html_table_cell("<strong style = 'color: #fff'>$message</strong>");
		$row->cells[] = $cell;
		$cell->attributes = array('class' => "bg-danger");
		$cell = new html_table_cell('Người dùng đã ghi danh');
		$row->cells[] = $cell;
		$table->data[] = $row;
	}

	foreach ($errors as $k => $error) {
		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell($error->email);
		$row->cells[] = $cell;
		$cell = new html_table_cell('null');
		$row->cells[] = $cell;
		$cell = new html_table_cell('null');
		$row->cells[] = $cell;

		$course_name = $DB->get_field_sql("SELECT fullname FROM {course} WHERE id = '$error->courseid'");

		if (empty($course_name)) {
			$cell = new html_table_cell('null');
			$row->cells[] = $cell;
		} else {
			$link = new moodle_url('/user/index.php', ['id' => $error->courseid]);
			$link_edit = html_writer::link($link, $course_name);
			$cell = new html_table_cell($link_edit);
			$row->cells[] = $cell;
		}

		$cell = new html_table_cell($error->shortname_course);
		$row->cells[] = $cell;

		$message = $error->error;
		$cell = new html_table_cell("<strong style = 'color: #fff'>$message</strong>");
		$row->cells[] = $cell;
		$cell->attributes = array('class' => "bg-danger");

		if (empty($course_name)) {
			$cell = new html_table_cell('Sai tên rút gọn khóa học');
			$row->cells[] = $cell;
		} else {
			$cell = new html_table_cell('Sai email');
			$row->cells[] = $cell;
		}

		$table->data[] = $row;
	}

	$table->attributes = array('class' => 'th_enrol_table', 'border' => '1');
	$table->attributes['style'] = "width: 100%; text-align:center;";
	$html = html_writer::table($table);
	return $html;
}

function th_display_table_error($errors) {
	$table1 = new html_table($errors);
	$table1->head = array(get_string('STT', 'block_th_bulk_enrol_student'), get_string('Hints', 'block_th_bulk_enrol_student'));
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

function local_th_register_course_controls(context $context, moodle_url $currenturl) {
	$tabs = array();
	$currenttab = 'view';
	$view = new moodle_url('/blocks/th_bulk_enrol_student/view.php');

	if (has_capability('block/th_bulk_enrol_student:view', $context)) {
		$addurl = new moodle_url('/blocks/th_bulk_enrol_student/view.php');
		$tabs[] = new tabobject('view', $addurl, "Ghi danh theo file excel");
		if ($currenturl->get_path() === $addurl->get_path()) {
			$currenttab = 'view';
		}
	}

	if (has_capability('block/th_bulk_enrol_student:view', $context)) {
		$addurl = new moodle_url('/blocks/th_bulk_enrol_student/view2.php');
		$tabs[] = new tabobject('edit', $addurl, "Ghi danh theo file CSV");
		if ($currenturl->get_path() === $addurl->get_path()) {
			$currenttab = 'edit';
		}
	}

	if (count($tabs) > 1) {
		return new tabtree($tabs, $currenttab);
	}
	return null;
}
