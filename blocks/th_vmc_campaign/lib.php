<?php

define('BLOCKth_bulkenrol_HINT', 'hint');
define('BLOCKth_bulkenrol_ENROLUSERS', 'enrolusers');

class th_vmc_campaign {

	public static function get_all_marketing_campaign() {

		global $DB;

		return $DB->get_records('marketing_campaign');
	}

	public static function get_one_marketing_campaign($id) {

		global $DB;
		$record_exists = self::check_campaign_exists($id);

		if (!$record_exists) {
			return;
		}

		return $DB->get_record('marketing_campaign', array('id' => $id));
	}

	public static function check_campaign_exists($id) {
		global $DB;
		$record_exists = $DB->record_exists('marketing_campaign', array('id' => $id));
		if (!$record_exists) {
			return false;
		}
		return true;
	}

	public static function add_marketing_campaign($dataobject) {

		global $DB;

		return $DB->insert_record('marketing_campaign', $dataobject, true, false);
	}

	public static function update_marketing_campaign($dataobject) {

		global $DB;

		return $DB->update_record('marketing_campaign', $dataobject, false);
	}

	public static function delete_marketing_campaign($id) {

		global $DB;

		$check = $DB->record_exists('user_campaign_course', array('campaignid' => $id));

		if ($check == false) {
			$DB->delete_records('marketing_campaign', array('id' => $id));
			return 1;
		}

		return 0;
	}

	public static function count_all_campaign() {
		global $DB;
		if (empty($DB->count_records('marketing_campaign'))) {
			return 0;
		}
		return $DB->count_records('marketing_campaign');
	}
	public static function get_name_campaign($id) {

		global $DB;

		$record_exists = self::check_campaign_exists($id);

		if (!$record_exists) {
			return;
		}

		return $DB->get_record('marketing_campaign', array('id' => $id), 'campaignname')->campaignname;
	}

	public static function get_courseid_name($courseid) {
		global $DB;
		$course = $DB->get_record('course', array('id' => $courseid));
		return $course->fullname;
	}
}
function campaign_edit_controls(context $context, moodle_url $currenturl) {
	$tabs = array();
	$currenttab = 'view';
	$viewurl = new moodle_url('/blocks/th_vmc_campaign/view.php', array('contextid' => $context->id));

	if ($context->contextlevel == CONTEXT_SYSTEM) {
		$tabs[] = new tabobject('view', new moodle_url($viewurl), get_string('allcampaigns', 'block_th_vmc_campaign'));
		if ($currenturl->get_param('contextid')) {
			$currenttab = 'view';
		}
	}
	if (has_capability('block/th_vmc_campaign:view', $context)) {
		$addurl = new moodle_url('/blocks/th_vmc_campaign/edit.php', array('contextid' => $context->id));
		$tabs[] = new tabobject('edit', $addurl, get_string('addcampaigns', 'block_th_vmc_campaign'));
		if ($currenturl->get_path() === $addurl->get_path() && !$currenturl->param('id')) {
			$currenttab = 'edit';
		}
	}
	if (count($tabs) > 1) {
		return new tabtree($tabs, $currenttab);
	}
	return null;
}

function get_id_course_by_shortname($shortname) {
	global $DB;

	$check = $DB->record_exists('course', array('shortname' => $shortname));

	if ($check == true) {
		return $DB->get_record('course', array('shortname' => $shortname), 'id')->id;
	}
	return;
}
function check_user_campaign_course($id) {
	global $DB;
	$record_exists = $DB->record_exists('user_campaign_course', array('id' => $id));
	if (!$record_exists) {
		return false;
	}
	return true;
}

function delete_user_campaign_course($id) {
	global $DB;

	$check = check_user_campaign_course($id);

	if ($check == 1) {
		return $DB->delete_records('user_campaign_course', array('id' => $id));
	}
	return;
}

function get_username($user_campaign_course_id) {
	global $DB;

	$sql = "SELECT u.username,c.fullname FROM {user_campaign_course} ucc
			JOIN {user} u ON u.id=ucc.userid
			JOIN {course} c ON c.id=ucc.courseid
			JOIN {marketing_campaign} mc ON mc.id=ucc.campaignid
			WHERE ucc.id =:user_campaign_course_id
		";
	$params = array('user_campaign_course_id' => $user_campaign_course_id);
	$record = $DB->get_record_sql($sql, $params);

	return $record;
}

function get_name_course_by_id($id) {
	global $DB;

	return $DB->get_record('course', array('id' => $id), 'fullname')->fullname;
}

function get_shortname_course_by_id($id) {
	global $DB;

	return $DB->get_record('course', array('id' => $id), 'shortname')->shortname;
}

function get_fullname_course_by_shortname($shortname) {
	global $DB;

	return $DB->get_record('course', array('shortname' => $shortname))->fullname;
}

function block_th_vmc_campaign_check_user_mails($emailstextfield, $campaignid, $option) {

	$checkedemails = new stdClass();
	$checkedemails->emails_to_ignore = array();
	$checkedemails->error_messages = array();
	$checkedemails->moodleusers_for_email = array();
	$checkedemails->user_enroled = array();
	$checkedemails->validemailfound = 0;

	$emaildelimiters = array(', ', ' ', ',');

	if (!empty($emailstextfield)) {

		$emailslines = block_th_vmc_campaign_parse_emails($emailstextfield);

		$linecnt = 0;

		// Process emails from textfield.
		foreach ($emailslines as $emailline) {
			$linecnt++;

			$error = '';

			$emailline = trim($emailline);
			$shortnameinlinecnt = substr_count($emailline, ',');
			// No email in row/line.
			if ($shortnameinlinecnt == 0) {

				$a = new stdClass();
				$a->line = $linecnt;
				$a->content = $emailline;
				$error = get_string('error_no_course', 'block_th_vmc_campaign', $a);
				$checkedemails->error_messages[$linecnt] = $error;
				continue;
				// One email in row/line.
			}
			if (!empty($emailline)) {
				$array = explode(',', $emailline);
				$emailline = $array[0];
				$shortname = $array[1];
				$courseid = get_id_course_by_shortname($shortname);
				if (empty($courseid)) {
					$a = new stdClass();
					$a->line = $linecnt;
					$a->content = $shortname;
					$error = get_string('error_no_course', 'block_th_vmc_campaign', $a);
					$checkedemails->error_messages[$linecnt] = $error;
					continue;
				}
			}

			// Check number of emails in current row/line.
			$emailsinlinecnt = substr_count($emailline, '@');

			// No email in row/line.
			if ($emailsinlinecnt == 0) {

				$a = new stdClass();
				$a->line = $linecnt;
				$a->content = $emailline;
				$error = get_string('error_no_email', 'block_th_vmc_campaign', $a);
				$checkedemails->error_messages[$linecnt] = $error;

				// One email in row/line.
			} else if ($emailsinlinecnt == 1) {
				$email = $emailline;
				block_th_vmc_campaign_check_email($email, $linecnt, $courseid, $campaignid, $option, $checkedemails);
			}
			// More than one email in row/line.
			if ($emailsinlinecnt > 1) {
				$delimiter = '';

				// Check delimiters.
				foreach ($emaildelimiters as $emaildelimiter) {
					$pos = strpos($emailline, $emaildelimiter);
					if ($pos) {
						$delimiter = $emaildelimiter;
						break;
					}
				}
				if (!empty($delimiter)) {
					$emailsinline = explode($delimiter, $emailline);

					// Iterate emails in row/line.
					foreach ($emailsinline as $emailinline) {

						$email = trim($emailinline);
						block_th_vmc_campaign_check_email($email, $linecnt, $courseid, $campaignid, $option, $checkedemails);
					}
				}
			}
		}
	}
	// print_object($checkedemails);
	// exit();
	return $checkedemails;
}

function block_th_vmc_campaign_parse_emails($emails) {
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

function block_th_vmc_campaign_check_email($email, $linecnt, $courseid, $campaignid, $option, &$checkedemails) {
	// Check for valid email.
	$emailisvalid = validate_email($email);
	// Email is not valid.
	if (!$emailisvalid) {
		$checkedemails->emails_to_ignore[] = $email;
		$a = new stdClass();
		$a->row = $linecnt;
		$a->email = $email;
		$error = get_string('error_invalid_email', 'block_th_vmc_campaign', $a);
		if (array_key_exists($linecnt, $checkedemails->error_messages)) {
			$errors = $checkedemails->error_messages[$linecnt];
			$errors .= "<br>" . $error;
			$checkedemails->error_messages[$linecnt] = $errors;
		} else {
			$checkedemails->error_messages[$linecnt] = $error;
		}

		// Email is valid.
	} else {
		// Check for moodle user with email.
		list($error, $userrecord) = block_th_vmc_campaign_get_user($email);
		if (!empty($error)) {
			$checkedemails->emails_to_ignore[] = $email;
			if (array_key_exists($linecnt, $checkedemails->error_messages)) {
				$errors = $checkedemails->error_messages[$linecnt];
				$errors .= "<br>" . $error;
				$checkedemails->error_messages[$linecnt] = $errors;
			} else {
				$checkedemails->error_messages[$linecnt] = $error;
			}
		} else if (!empty($userrecord) && !empty($userrecord->id)) {
			$checkedemails->validemailfound += 1;

			$useralreadyenroled = false;

			if (!empty($userrecord)) {
				$useralreadyenroled = block_th_vmc_campaign_check_user_course_campaign($userrecord->id, $courseid, $campaignid);
			}
			$checkedemails->moodleusers_for_email[$email . ',' . $courseid] = $userrecord;
			if (empty($useralreadyenroled) && $option === 0) {
				$checkedemails->user_enroled[$email . ',' . $courseid] = $userrecord;
			}
			if (!empty($useralreadyenroled) && $option === 1) {
				$checkedemails->user_enroled[$email . ',' . $courseid] = $userrecord;
			}
		}
	}
}

function block_th_vmc_campaign_check_user_course_campaign($userid, $courseid, $campaignid) {
	global $DB;

	$check = $DB->record_exists('user_campaign_course', array('userid' => $userid, 'courseid' => $courseid, 'campaignid' => $campaignid));
	return $check;
}
function block_th_vmc_campaign_get_user($email) {
	global $DB;

	$error = null;
	$userrecord = null;

	if (empty($email)) {
		return array($error, $userrecord);
	} else {
		// Get user records for email.
		try {
			$userrecords = $DB->get_records('user', array('email' => $email));
			$count = count($userrecords);
			if (!empty($count)) {
				// More than one user with email -> ignore email and don't enrol users later!
				if ($count > 1) {
					$error = get_string('error_more_than_one_record_for_email', 'block_th_vmc_campaign', $email);
				} else {
					$userrecord = current($userrecords);
				}
			} else {
				$error = get_string('error_no_record_found_for_email', 'block_th_vmc_campaign', $email);
			}
		} catch (Exception $e) {
			$error = get_string('error_getting_user_for_email', 'block_th_vmc_campaign', $email) . block_th_vmc_campaign_get_exception_info($e);
		}

		return array($error, $userrecord);
	}
}

function block_th_vmc_campaign_get_exception_info($e) {
	if (empty($e) || !($e instanceof Exception)) {
		return '';
	}

	return " " . get_string('error_exception_info', 'block_th_vmc_campaign') . ": " . $e->getMessage() . " -> " . $e->getTraceAsString();
}

function block_th_vmc_campaign_display_table($localbulkenroldata, $key) {
	global $OUTPUT;

	if (!empty($localbulkenroldata) && !empty($key)) {

		switch ($key) {
		case BLOCKth_bulkenrol_HINT:

			$data = array();

			if (!empty($localbulkenroldata->error_messages)) {
				foreach ($localbulkenroldata->error_messages as $line => $errormessages) {
					$row = array();

					$cell = new html_table_cell();
					$cell->text = $line;
					$row[] = $cell;

					$cell = new html_table_cell();
					$cell->text = $errormessages;
					$row[] = $cell;

					$data[] = $row;
				}
			}

			$table = new html_table();
			$table->id = "BLOCKth_bulkenrol_HINTs";
			$table->attributes['class'] = 'generaltable';
			$table->summary = get_string('hints', 'block_th_vmc_campaign');
			$table->size = array('10%', '90%');
			$table->head = array();
			$table->head[] = get_string('row', 'block_th_vmc_campaign');
			$table->head[] = get_string('hints', 'block_th_vmc_campaign');
			$table->data = $data;

			if (!empty($data)) {
				echo $OUTPUT->heading(get_string('hints', 'block_th_vmc_campaign'), 3);
				echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
			}

			break;

		case BLOCKth_bulkenrol_ENROLUSERS:
			$data = array();

			if (!empty($localbulkenroldata->moodleusers_for_email)) {
				foreach ($localbulkenroldata->moodleusers_for_email as $email => $user) {
					$row = array();

					$cell = new html_table_cell();
					$cell->text = $user->email;
					$row[] = $cell;

					$cell = new html_table_cell();
					$cell->text = $user->firstname;
					$row[] = $cell;

					$cell = new html_table_cell();
					$cell->text = $user->lastname;
					$row[] = $cell;

					$cell = new html_table_cell();
					$cell->text = '';
					// print_object($email);
					// exit();
					$array = explode(',', $email);
					//$emails = $array[0];
					$courseid = $array[1];
					//if (!empty($localbulkenroldata->user_groups[$email])) {
					$cell->text = get_name_course_by_id($courseid);
					//}
					$row[] = $cell;

					$cell = new html_table_cell();
					$cell->text = '';
					if (!empty($localbulkenroldata->user_enroled[$email])) {
						$cell->text = html_writer::tag('span',
							get_string('user_enroled_yes', 'block_th_vmc_campaign'),
							array('class' => 'badge badge-secondary'));
					} else {
						$cell->text = html_writer::tag('span',
							get_string('user_enroled_already', 'block_th_vmc_campaign'),
							array('class' => 'badge badge-secondary'));
					}
					$row[] = $cell;

					$data[] = $row;
				}
			}

			$table = new html_table();
			$table->id = "BLOCKth_bulkenrol_ENROLUSERS";
			$table->attributes['class'] = 'generaltable';
			$table->summary = get_string('users_to_enrol_in_course', 'block_th_vmc_campaign');
			$table->size = array('20%', '17%', '17%', '20%', '26%');
			$table->head = array();
			$table->head[] = get_string('email');
			$table->head[] = get_string('firstname');
			$table->head[] = get_string('lastname');
			$table->head[] = get_string('course');
			$table->head[] = get_string('status');
			$table->data = $data;

			if (!empty($data)) {
				echo $OUTPUT->heading(get_string('users_to_enrol_in_course', 'block_th_vmc_campaign'), 3);
				echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
			}
			break;

		default:
			break;
		}
	}
}
function block_th_bulkenrol_users($localth_bulkenrolkey) {
	global $DB, $SESSION;

	$time = time();

	$error = '';
	$exceptionsmsg = array();

	if (!empty($localth_bulkenrolkey)) {
		if (!empty($localth_bulkenrolkey) && !empty($SESSION->block_th_bulkenrol) &&
			array_key_exists($localth_bulkenrolkey, $SESSION->block_th_bulkenrol)) {
			$blockth_bulkenroldata = $SESSION->block_th_bulkenrol[$localth_bulkenrolkey];
			// print_object($blockth_bulkenroldata);
			// exit();
			if (!empty($blockth_bulkenroldata)) {
				$error = '';

				$campaignid = 0;

				$tmpdata = explode('_', $localth_bulkenrolkey);
				if (!empty($tmpdata)) {
					$campaignid = $tmpdata[0];
				}

				$userstoenrol = $blockth_bulkenroldata->moodleusers_for_email;

				if (!empty($campaignid) && !empty($userstoenrol)) {
					try {
						foreach ($userstoenrol as $key => $user) {
							try {
								$array = explode(",", $key);
								$courseid = $array[1];
								// Check if user is already enrolled with another enrolment method.

								$userisenrolled = block_th_vmc_campaign_check_user_course_campaign($user->id, $courseid, $campaignid);

								// If the user is already enrolled, continue to avoid a second enrolment for the user.
								if ($userisenrolled) {
									continue;

									// Otherwise.
								} else {

									$dataobject = new stdClass();
									$dataobject->userid = $user->id;
									$dataobject->courseid = $courseid;
									$dataobject->campaignid = $campaignid;
									$dataobject->timecreated = $time;
									$dataobject->timemodified = $time;
									$DB->insert_record('user_campaign_course', $dataobject);
								}
							} catch (Exception $e) {
								$a = new stdClass();
								$a->email = $user->email;

								$msg = get_string('error_enrol_user', 'block_th_vmc_campaign', $a) .
								block_th_vmc_campaign_get_exception_info($e);
								$exceptionsmsg[] = $msg;
							}
						}
					} catch (Exception $e) {
						$msg = get_string('error_enrol_users', 'block_th_vmc_campaign') . block_th_vmc_campaign_get_exception_info($e);
						$exceptionsmsg[] = $msg;
					}
				}
			}
		}
	}

	$retval = new stdClass();
	$retval->status = '';
	$retval->text = '';

	if (!empty($error) || !empty($exceptionsmsg)) {
		$retval->status = 'error';

		if (!empty($error)) {
			$msg = get_string($error, 'block_th_vmc_campaign');
			$retval->text = $msg;
		}

		if (!empty($exceptionsmsg)) {
			if (!empty($error)) {
				$retval->text .= '<br>';
			}
			$retval->text .= implode('<br>', $exceptionsmsg);
		}
	} else {
		$retval->status = 'success';
		$msg = get_string('enrol_users_successful', 'block_th_vmc_campaign');
		$retval->text = $msg;
	}

	return $retval;
}
function block_th_bulkunenrol_users($localth_bulkenrolkey) {
	global $DB, $SESSION;

	$error = '';
	$exceptionsmsg = array();

	if (!empty($localth_bulkenrolkey)) {
		if (!empty($localth_bulkenrolkey) && !empty($SESSION->block_th_bulkenrol) &&
			array_key_exists($localth_bulkenrolkey, $SESSION->block_th_bulkenrol)) {
			$blockth_bulkenroldata = $SESSION->block_th_bulkenrol[$localth_bulkenrolkey];
			// print_object($blockth_bulkenroldata);
			// exit();
			if (!empty($blockth_bulkenroldata)) {
				$error = '';

				$campaignid = 0;

				$tmpdata = explode('_', $localth_bulkenrolkey);
				if (!empty($tmpdata)) {
					$campaignid = $tmpdata[0];
				}

				$userstoenrol = $blockth_bulkenroldata->moodleusers_for_email;

				if (!empty($campaignid) && !empty($userstoenrol)) {
					try {
						foreach ($userstoenrol as $key => $user) {
							try {
								$array = explode(",", $key);
								$courseid = $array[1];
								// Check if user is already enrolled with another enrolment method.

								$userisenrolled = block_th_vmc_campaign_check_user_course_campaign($user->id, $courseid, $campaignid);

								// If the user is already enrolled, continue to avoid a second enrolment for the user.
								if (!$userisenrolled) {
									continue;

									// Otherwise.
								} else {

									$record = array('userid' => $user->id, 'courseid' => $courseid, 'campaignid' => $campaignid);
									$DB->delete_records('user_campaign_course', $record);
								}
							} catch (Exception $e) {
								$a = new stdClass();
								$a->email = $user->email;

								$msg = get_string('error_enrol_user', 'block_th_vmc_campaign', $a) .
								block_th_vmc_campaign_get_exception_info($e);
								$exceptionsmsg[] = $msg;
							}
						}
					} catch (Exception $e) {
						$msg = get_string('error_enrol_users', 'block_th_vmc_campaign') . block_th_vmc_campaign_get_exception_info($e);
						$exceptionsmsg[] = $msg;
					}
				}
			}
		}
	}

	$retval = new stdClass();
	$retval->status = '';
	$retval->text = '';

	if (!empty($error) || !empty($exceptionsmsg)) {
		$retval->status = 'error';

		if (!empty($error)) {
			$msg = get_string($error, 'block_th_vmc_campaign');
			$retval->text = $msg;
		}

		if (!empty($exceptionsmsg)) {
			if (!empty($error)) {
				$retval->text .= '<br>';
			}
			$retval->text .= implode('<br>', $exceptionsmsg);
		}
	} else {
		$retval->status = 'success';
		$msg = get_string('unenrol_users_successful', 'block_th_vmc_campaign');
		$retval->text = $msg;
	}

	return $retval;
}
function block_th_bulkunenrol_display_table($localbulkenroldata, $key) {
	global $OUTPUT;

	if (!empty($localbulkenroldata) && !empty($key)) {

		switch ($key) {
		case BLOCKth_bulkenrol_HINT:

			$data = array();

			if (!empty($localbulkenroldata->error_messages)) {
				foreach ($localbulkenroldata->error_messages as $line => $errormessages) {
					$row = array();

					$cell = new html_table_cell();
					$cell->text = $line;
					$row[] = $cell;

					$cell = new html_table_cell();
					$cell->text = $errormessages;
					$row[] = $cell;

					$data[] = $row;
				}
			}

			$table = new html_table();
			$table->id = "BLOCKth_bulkenrol_HINTs";
			$table->attributes['class'] = 'generaltable';
			$table->summary = get_string('hints', 'block_th_vmc_campaign');
			$table->size = array('10%', '90%');
			$table->head = array();
			$table->head[] = get_string('row', 'block_th_vmc_campaign');
			$table->head[] = get_string('hints', 'block_th_vmc_campaign');
			$table->data = $data;

			if (!empty($data)) {
				echo $OUTPUT->heading(get_string('hints', 'block_th_vmc_campaign'), 3);
				echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
			}

			break;

		case BLOCKth_bulkenrol_ENROLUSERS:
			$data = array();

			if (!empty($localbulkenroldata->moodleusers_for_email)) {
				foreach ($localbulkenroldata->moodleusers_for_email as $email => $user) {
					$row = array();

					$cell = new html_table_cell();
					$cell->text = $user->email;
					$row[] = $cell;

					$cell = new html_table_cell();
					$cell->text = $user->firstname;
					$row[] = $cell;

					$cell = new html_table_cell();
					$cell->text = $user->lastname;
					$row[] = $cell;

					$cell = new html_table_cell();
					$cell->text = '';

					$array = explode(',', $email);
					$courseid = $array[1];

					$cell->text = get_name_course_by_id($courseid);
					$row[] = $cell;

					$cell = new html_table_cell();
					$cell->text = '';
					if (!empty($localbulkenroldata->user_enroled[$email])) {
						$cell->text = html_writer::tag('span',
							get_string('user_will_be_unenrolled', 'block_th_vmc_campaign'),
							array('class' => 'badge badge-secondary'));
					} else {
						$cell->text = html_writer::tag('span',
							get_string('user_unenrolled_no', 'block_th_vmc_campaign'),
							array('class' => 'badge badge-secondary'));
					}
					$row[] = $cell;

					$data[] = $row;
				}
			}

			$table = new html_table();
			$table->id = "BLOCKth_bulkenrol_ENROLUSERS";
			$table->attributes['class'] = 'generaltable';
			$table->summary = get_string('users_to_unenrol_in_course', 'block_th_vmc_campaign');
			$table->size = array('20%', '17%', '17%', '20%', '26%');
			$table->head = array();
			$table->head[] = get_string('email');
			$table->head[] = get_string('firstname');
			$table->head[] = get_string('lastname');
			$table->head[] = get_string('course');
			$table->head[] = get_string('status');
			$table->data = $data;

			if (!empty($data)) {
				echo $OUTPUT->heading(get_string('users_to_unenrol_in_course', 'block_th_vmc_campaign'), 3);
				echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
			}
			break;

		default:
			break;
		}
	}
}