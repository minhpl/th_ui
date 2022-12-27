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

function th_bulkenrol_csv_check_user_mails($emailstextfield) {
	global $DB;

	$checkedemails = new stdClass();
	$checkedemails->error_messages = array();
	$checkedemails->moodleusers_for_email = array();
	$checkedemails->user_enroled = array();
	$checkedemails->validemailfound = 0;

	if (!empty($emailstextfield)) {

		$emailslines = th_bulkenrol_csv_parse_emails($emailstextfield);

		$context = null;

		// Process emails from textfield.
		foreach ($emailslines as $emailline) {

			$error = '';

			$line = trim($emailline);

			if (substr_count($line, ',') == 3) {
				$line_arr = explode(",", $line);
				$shortname_course = $line_arr[1];
				$sql = "SELECT id FROM {course} WHERE shortname = '$shortname_course'";
				if ($DB->record_exists_sql($sql) == 1) {
					$emailline = $line_arr[0];
					$stt = $line_arr[3];
					$courseid = $DB->get_field_sql($sql);
				} else {
					$emailline = $line_arr[0];
					$stt = $line_arr[3];

					//Show error course shortname
					$a = new stdClass();
					$a->line = $stt;
					$a->content = $shortname_course;
					$error = get_string('error_no_shortnamecourse', 'block_th_bulkenrol_course', $a);
					$error_messages = $checkedemails->error_messages;
					if (!in_array($error, $error_messages)) {
						$checkedemails->error_messages[] = $error;
					}

				}
				$roleid = $line_arr[2];

			}

			// Check number of emails in current row/line.
			$emailsinlinecnt = substr_count($emailline, '@');
			// No email in row/line.
			if ($emailsinlinecnt == 0) {
				if ($DB->record_exists_sql($sql) == 1) {
					$a = new stdClass();
					$a->line = $stt;
					$a->content = $emailline;
					$error = get_string('error_no_email', 'block_th_bulkenrol_course', $a);
					$checkedemails->error_messages[] = $error;
				}

				// $a = new stdClass();
				// $a->line = $stt;
				// $a->content = $emailline;
				// $error = get_string('error_no_email', 'block_th_bulkenrol_course', $a);
				// $checkedemails->error_messages[] = $error;

				// One email in row/line.
			} else if ($emailsinlinecnt == 1) {
				$email = $emailline;
				$a = new stdClass();
				$a->line = $stt;
				$a->content = $emailline;
				if ($DB->record_exists_sql($sql) == 1) {
					if ($DB->record_exists_sql("SELECT * FROM {user} WHERE email = '$email'") != 1) {
						$error = get_string('error_no_record_found_for_email', 'block_th_bulkenrol_course', $a);
						$checkedemails->error_messages[] = $error;
					} else {
						$userrecord = $DB->get_record('user', array('email' => $email));
						$userisenrolled = th_bulkenrol_csv_check_email($userrecord->id, $courseid, $roleid, $stt);
						if (empty($userisenrolled)) {
							$checkedemails->validemailfound += 1;
							$userrecord->courseid = $courseid;
							$userrecord->roleid = $roleid;
							$checkedemails->user_enroled[] = $userrecord;
						} else {
							$userrecord->courseid = $courseid;
							$userrecord->roleid = $roleid;
							$checkedemails->moodleusers_for_email[] = $userrecord;
						}

					}

				}
				// else {
				// 	if ($DB->record_exists_sql("SELECT * FROM {user} WHERE email = '$email'") != 1){
				// 		$error = get_string('error_no_record_found_for_email', 'block_th_bulkenrol_course', $a);
				// 		$checkedemails->error_messages[] = $error;
				// 	}
				// }
			}
		}
	}

	return $checkedemails;
}

function th_bulkenrol_csv_check_email($userid, $courseid, $roleid, $stt) {
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

function th_get_content($contents) {
	$roleid_gvcm = get_config('block_th_bulkenrol_course', 'role1');
	$roleid_gvcn = get_config('block_th_bulkenrol_course', 'role2');
	$roleid_qlht = get_config('block_th_bulkenrol_course', 'role3');
	$roleid_course_suppoter = get_config('block_th_bulkenrol_course', 'role3');

	$max = count($contents) - 1;
	for ($i = 1; $i < $max; ++$i) {
		$line = $contents[$i];
		$lines = explode(",", $line);
		$shortname = $lines[0];

		$gvcm = $lines[1];
		$gvcn = $lines[2];
		$qlht = $lines[3];
		$course_suppoter = $lines[4];

		$gvcm_pos = substr_count($gvcm, ';');
		if ($gvcm_pos >= 1) {
			$gvcm_lines = explode(";", $gvcm);
			foreach ($gvcm_lines as $k => $gvcm_line) {
				$arr1 = [$gvcm_line, $shortname, $roleid_gvcm, $i + 1];
				$userfile[] = implode(',', $arr1);
			}
		} else {
			$arr1 = [$gvcm, $shortname, $roleid_gvcm, $i + 1];
			$userfile[] = implode(',', $arr1);
		}

		$gvcn_pos = substr_count($gvcn, ';');
		if ($gvcn_pos >= 1) {
			$gvcn_lines = explode(";", $gvcn);
			foreach ($gvcn_lines as $k => $gvcn_line) {
				$arr2 = [$gvcn_line, $shortname, $roleid_gvcn, $i + 1];
				$userfile[] = implode(',', $arr2);
			}
		} else {
			$arr2 = [$gvcn, $shortname, $roleid_gvcn, $i + 1];
			$userfile[] = implode(',', $arr2);
		}

		$qlht_pos = substr_count($qlht, ';');
		if ($qlht_pos >= 1) {
			$qlht_lines = explode(";", $qlht);
			foreach ($qlht_lines as $k => $qlht_line) {
				$arr3 = [$qlht_line, $shortname, $roleid_qlht, $i + 1];
				$userfile[] = implode(',', $arr3);
			}
		} else {
			$arr3 = [$qlht, $shortname, $roleid_qlht, $i + 1];
			$userfile[] = implode(',', $arr3);
		}

		$course_suppoter_pos = substr_count($course_suppoter, ';');
		if ($course_suppoter_pos >= 1) {
			$course_suppoter_lines = explode(";", $course_suppoter);
			foreach ($course_suppoter_lines as $k => $course_suppoter_line) {
				$arr3 = [$course_suppoter_line, $shortname, $roleid_course_suppoter, $i + 1];
				$userfile[] = implode(',', $arr3);
			}
		} else {
			$arr3 = [$course_suppoter, $shortname, $roleid_course_suppoter, $i + 1];
			$userfile[] = implode(',', $arr3);
		}
	}

	$email = implode(PHP_EOL, $userfile);
	$emails = trim($email);
	return $emails;
}

function th_display_table_enrol($enroleds, $no_enroleds) {
	global $DB;
	$table = new html_table();
	$table->head = array(get_string('STT', 'block_th_bulkenrol_course'), get_string('Email', 'block_th_bulkenrol_course'), get_string('Firstname', 'block_th_bulkenrol_course'), get_string('Lastname', 'block_th_bulkenrol_course'), get_string('Course', 'block_th_bulkenrol_course'), get_string('Status', 'block_th_bulkenrol_course'));
	$stt = 0;
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

		$role_name = $DB->get_field_sql("SELECT shortname FROM {role} WHERE id = '$enroled->roleid'");
		$message = get_string('user_enroled_already', 'block_th_bulkenrol_course') . $role_name;
		$cell = new html_table_cell("<p style='color: #fff'>$message</p>");
		$row->cells[] = $cell;
		$cell->attributes = array('class' => "bg-danger");
		$table->data[] = $row;
	}

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

		$role_name = $DB->get_field_sql("SELECT shortname FROM {role} WHERE id = '$no_enroled->roleid'");
		$message = get_string('user_enroled_yes', 'block_th_bulkenrol_course') . $role_name;
		$cell = new html_table_cell("<p style='color: #fff'>$message</p>");
		$row->cells[] = $cell;
		$cell->attributes = array('class' => "bg-success");
		$table->data[] = $row;
	}

	$table->attributes = array('class' => 'th_enrol_table', 'border' => '1');
	$table->attributes['style'] = "width: 100%; text-align:center;";
	$html = html_writer::table($table);
	return $html;
}

function th_display_table_error($errors) {
	$table1 = new html_table($errors);
	$table1->head = array(get_string('STT', 'block_th_bulkenrol_course'), get_string('Hints', 'block_th_bulkenrol_course'));
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
