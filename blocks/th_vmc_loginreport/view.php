<?php
require '../../config.php';
require_once $CFG->libdir . "/formslib.php";
require_once $CFG->dirroot . "/lib/enrollib.php";
require_once $CFG->dirroot . "/blocks/th_vmc_loginreport/th_vmc_loginreport_form.php";
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . "/blocks/th_vmc_loginreport/lib.php";
require_once "{$CFG->libdir}/completionlib.php";

global $DB, $CFG, $COURSE;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', 'block_th_vmc_loginreport', $COURSE->id);
}
require_login($COURSE->id);
require_capability('block/th_vmc_loginreport:view', context_course::instance($COURSE->id));

$PAGE->set_url(new moodle_url('/blocks/th_vmc_loginreport/view.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('pluginname', 'block_th_vmc_loginreport'));
$PAGE->set_title(get_string('pluginname', 'block_th_vmc_loginreport'));
$editurl = new moodle_url('/blocks/th_vmc_loginreport/view.php');
$settingsnode = $PAGE->settingsnav->add(get_string('pluginname', 'block_th_vmc_loginreport'), $editurl);
echo $OUTPUT->header();

$mform = new th_vmc_loginreport_form();
$mform->display();

$lang = current_language();
echo '<link rel="stylesheet" type="text/css" href="<https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">';
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.loginreport-table', "Báo cáo Đăng nhập", $lang));

if ($mform->is_submitted()) {

	$fromform = $mform->get_data();
	$option = $fromform->show_option;

	if ($option == 0) {
		$courseid_arr = $fromform->courseidarr;
		if (empty($courseid_arr)) {
			$courses = $DB->get_records_sql('SELECT id FROM {course} WHERE summaryformat=1');
			foreach ($courses as $key => $course) {
				$courseid_arr[$key] = $course->id;
			}
		}
		$sql_enrol = "SELECT DISTINCT ue.userid
				FROM {course} c, {user} u, {enrol} e, {user_enrolments} ue, {role_assignments} ra
				WHERE
					c.id=e.courseid AND u.id=ue.userid AND e.id=ue.enrolid AND u.id=ra.userid AND ra.roleid=5 AND u.deleted=0 AND c.id=:courseid AND u.suspended = 0";
		$sql_registeredcourses = "SELECT u.id
								FROM {user} u JOIN {th_registeredcourses} r ON u.id =  r.userid
								WHERE r.courseid = :courseid AND r.timeactivated=0 AND u.deleted=0 AND u.suspended=0";

		foreach ($courseid_arr as $key => $courseid) {

			$enrol_userid_arr = $DB->get_records_sql($sql_enrol, ['courseid' => $courseid]);
			$registeredcourses_userid_arr = $DB->get_records_sql($sql_registeredcourses, ['courseid' => $courseid]);

			$userid_arr = [];
			if (!empty($enrol_userid_arr)) {
				foreach ($enrol_userid_arr as $student) {
					$userid_arr[] = $student->userid;
				}
			}
			if (!empty($registeredcourses_userid_arr)) {
				foreach ($registeredcourses_userid_arr as $student) {
					$userid_arr[] = $student->id;
				}
			}
			if (!$userid_arr) {
				continue;
			}

			$extra = array_filter(explode(',', $CFG->showuseridentity));
			$userfields = array_values($extra);

			$usernamefield = get_all_user_name_fields();
			$usernamefield = implode(",", $usernamefield);
			$alluserfields = "id," . $usernamefield;

			if (count($userfields) > 0) {
				$alluserfields .= "," . implode(',', $userfields);
			}

			$alluserfields .= "," . "email";
			$user_arr = $DB->get_records('user', array('deleted' => 0, 'suspended' => 0), "", $alluserfields);

			$table = new html_table();
			$table->attributes = array('class' => 'loginreport-table', 'border' => '1');

			$headrows = new html_table_row();
			$cell = new html_table_cell(get_string('lastaccess'));
			$cell->attributes['class'] = 'cell headingcell';
			$cell->header = true;
			$headrows->cells[] = $cell;
			$cell = new html_table_cell(get_string('complete_status', 'block_th_vmc_loginreport'));
			$cell->attributes['class'] = 'cell headingcell';
			$cell->header = true;
			$headrows->cells[] = $cell;
			$cell = new html_table_cell(get_string('status'));
			$cell->attributes['class'] = 'cell headingcell';
			$cell->header = true;
			$headrows->cells[] = $cell;

			$rightrows = [];
			$rightrows[] = $headrows;
			list($leftrows, $rows_ex_left) = get_left_rows($userid_arr, $user_arr);
			$soCot = count($leftrows[0]->cells);
			$config = get_config('local_thlib');
			$strLeft = trim($config->custom_fields1, ' ');
			$strRight = trim($config->custom_fields2, ' ');
			if ($strLeft == '') {
				$cellLeft = 0;
			} else {
				$cellLeft = count(explode(',', trim($config->custom_fields1)));
			}
			if ($strRight == '') {
				$cellRight = 0;
			} else {
				$cellRight = count(explode(',', trim($config->custom_fields2)));
			}

			foreach ($userid_arr as $userid) {
				$row = new html_table_row();
				if (is_enrolled(context_course::instance($courseid), $userid)) {
					$last_access = $DB->record_exists('user_lastaccess', array('userid' => $userid, 'courseid' => $courseid));
					if (empty($last_access)) {
						$cell = new html_table_cell('Chưa bao giờ');
					} else {
						$last_access = th_loginreport_get_last_access($userid, $courseid);
						$cell = new html_table_cell(date("d/m/Y H:i:s", $last_access->timeaccess));
						$cell->attributes['data-order'] = $last_access->timeaccess;
					}
					$row->cells[] = $cell;
					$course = $DB->get_record('course', ['id' => $courseid]);
					$info = new completion_info($course);
					$coursecomplete = $info->is_course_complete($userid);

					if ($coursecomplete) {
						$completion_status = get_string('completion', 'block_th_vmc_loginreport');
					} else {
						$completion_status = get_string('no_completion', 'block_th_vmc_loginreport');
					}
					// $user = $DB->get_record('user', array('id' => $userid), 'firstname,lastname');
					// $firstname = $user->firstname;
					// $lastname = $user->lastname;
					// $sifirst = $firstname[0];
					// $silast = $lastname[0];
					// print_object(mb_detect_encoding($silast, "UTF-8"));
					// $str = "ábrêcWtë";
					// echo 'Original :', ("$str"), PHP_EOL;
					// echo 'Plain :', iconv("UTF-8", "ISO-8859-1", $silast), PHP_EOL;
					// print_object($silast);
					// var_dump($firstname);
					// echo $firstname[0];
					// $initialsbarurl = new moodle_url($CFG->wwwroot . '/report/progress/index.php?course=' . $courseid . '&sifirst=' . $sifirst . '&silast=' . $silast);
					// print_object($initialsbarurl);
					// $link = html_writer::link($CFG->wwwroot . '/report/progress/index.php?course=' . $courseid . '&sifirst=' . $sifirst . '&silast=' . $silast,
					// 	$completion_status
					// );
					$link = html_writer::link($CFG->wwwroot . '/report/progress/index.php?course=' . $courseid, $completion_status);
					$cell = new html_table_cell($link);
					$row->cells[] = $cell;
					$enrol_get_enrolment_end = enrol_get_enrolment_end($courseid, $userid);
					if (gettype($enrol_get_enrolment_end) === false) {
						$cell = new html_table_cell(get_string('deactived', 'block_th_vmc_loginreport'));
					} else {
						if ($enrol_get_enrolment_end === 0 || $enrol_get_enrolment_end > time()) {
							$actived = html_writer::tag('span',
								get_string('actived', 'block_th_vmc_loginreport'),
								array('class' => 'badge badge-success')
							);
							$cell = new html_table_cell($actived);
						} else {
							$deactived = html_writer::tag('span',
								get_string('deactived', 'block_th_vmc_loginreport'),
								array('class' => 'badge badge-error')
							);
							$cell = new html_table_cell($deactived);
						}
					}
				} else {
					$cell = new html_table_cell();
					$row->cells[] = $cell;
					$cell = new html_table_cell();
					$row->cells[] = $cell;
					$not_activated = html_writer::tag('span',
						get_string('not_activated', 'block_th_vmc_loginreport'),
						array('class' => 'badge badge-warning')
					);
					$cell = new html_table_cell($not_activated);
				}
				$row->cells[] = $cell;
				$rightrows[$userid] = $row;
			}
			//  print_object($rightrows);
			// print_object($leftrows);
			foreach ($rightrows as $key => $row) {
				if (!array_key_exists($key, $leftrows)) {
					$row->cells = array_merge(array(), $row->cells);
				} else {
					$row->cells = array_merge($leftrows[$key]->cells, $row->cells);
				}
				$table->data[] = $row;
			}
			$headrows = array_shift($table->data);
			$table->head = $headrows->cells;
			$table->align[0] = 'center';
			echo html_writer::tag('h2',
				html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $courseid,
					th_loginreport_get_fullname_course($courseid))
			);
			echo html_writer::table($table);
			// echo '</br>';
		}
	}
	if ($option == 1) {
		$userid_arr = $fromform->userid;
		if (empty($userid_arr)) {
			$users = $DB->get_records_sql('SELECT id FROM {user} WHERE deleted=0 AND suspended=0');
			foreach ($users as $user) {
				$userid_arr[] = $user->id;
			}
		}

		$extra = array_filter(explode(',', $CFG->showuseridentity));
		$userfields = array_values($extra);

		$usernamefield = get_all_user_name_fields();
		$usernamefield = implode(",", $usernamefield);
		$alluserfields = "id," . $usernamefield;

		if (count($userfields) > 0) {
			$alluserfields .= "," . implode(',', $userfields);
		}

		$alluserfields .= "," . "email";
		$user_arr = $DB->get_records('user', array('deleted' => 0), "", $alluserfields);

		$table = new html_table();
		$table->attributes = array('class' => 'loginreport-table', 'border' => '1');

		$headrows = new html_table_row();
		$cell = new html_table_cell(get_string('course'));
		$cell->attributes['class'] = 'cell headingcell';
		$cell->header = true;
		$headrows->cells[] = $cell;
		$cell = new html_table_cell(get_string('lastaccess'));
		$cell->attributes['class'] = 'cell headingcell';
		$cell->header = true;
		$headrows->cells[] = $cell;
		$cell = new html_table_cell(get_string('accessnumber', 'block_th_vmc_loginreport'));
		$cell->attributes['class'] = 'cell headingcell';
		$cell->header = true;
		$headrows->cells[] = $cell;
		$cell = new html_table_cell(get_string('complete_status', 'block_th_vmc_loginreport'));
		$cell->attributes['class'] = 'cell headingcell';
		$cell->header = true;
		$headrows->cells[] = $cell;
		$cell = new html_table_cell(get_string('status'));
		$cell->attributes['class'] = 'cell headingcell';
		$cell->header = true;
		$headrows->cells[] = $cell;

		$rightrows = [];
		$rightrows[] = $headrows;
		list($leftrows, $rows_ex_left) = get_left_rows($userid_arr, $user_arr);
		$soCot = count($leftrows[0]->cells);
		$config = get_config('local_thlib');
		$strLeft = trim($config->custom_fields1, ' ');
		$strRight = trim($config->custom_fields2, ' ');
		if ($strLeft == '') {
			$cellLeft = 0;
		} else {
			$cellLeft = count(explode(',', trim($config->custom_fields1)));
		}
		if ($strRight == '') {
			$cellRight = 0;
		} else {
			$cellRight = count(explode(',', trim($config->custom_fields2)));
		}
		$sql_enrol = "SELECT m.id, m.userid, m.fullname, ul.timeaccess
					FROM {user_lastaccess} ul
					RIGHT JOIN (
						SELECT DISTINCT ue.userid, c.fullname, c.id
						FROM
						{course} c, {user} u, {enrol} e, {user_enrolments} ue, {role_assignments} ra
						WHERE
						c.id=e.courseid AND u.id=ue.userid AND e.id=ue.enrolid AND u.id=ra.userid AND ra.roleid=5 AND u.deleted=0 AND u.id=:userid AND e.status = 0 AND u.suspended = 0
					) m
					ON ul.courseid = m.id AND ul.userid = m.userid ORDER BY ul.timeaccess DESC";
		$sql_registeredcourses = "SELECT r.courseid
								FROM {user} u JOIN {th_registeredcourses} r ON u.id =  r.userid
								WHERE r.userid = :userid AND r.timeactivated=0 AND u.deleted=0 AND u.suspended=0";
		foreach ($userid_arr as $uid => $userid) {

			$params = array('userid' => $userid);
			$records = $DB->get_records_sql($sql_enrol, $params);

			foreach ($records as $courseid => $record) {

				$row = new html_table_row();
				$linkCourse = html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $courseid, $record->fullname);
				$cell = new html_table_cell($linkCourse);
				$row->cells[] = $cell;
				if ($record->timeaccess == null) {
					$cell = new html_table_cell('Chưa bao giờ');
				} else {
					$cell = new html_table_cell(date("d/m/Y H:i:s", $record->timeaccess));
					$cell->attributes['data-order'] = $record->timeaccess;
				}
				$row->cells[] = $cell;
				$cell = new html_table_cell(th_loginreport_get_count_access_course($userid, $courseid));
				$row->cells[] = $cell;

				$course = $DB->get_record('course', ['id' => $courseid]);
				$info = new completion_info($course);
				$coursecomplete = $info->is_course_complete($userid);

				if ($coursecomplete) {
					$completion_status = get_string('completion', 'block_th_vmc_loginreport');
				} else {
					$completion_status = get_string('no_completion', 'block_th_vmc_loginreport');
				}
				$cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/report/progress/index.php?course=' . $courseid, $completion_status));
				// $cell = new html_table_cell($linkCourse);
				$row->cells[] = $cell;
				$enrol_get_enrolment_end = enrol_get_enrolment_end($courseid, $userid);
				if (gettype($enrol_get_enrolment_end) === false) {
					$cell = new html_table_cell(get_string('deactived', 'block_th_vmc_loginreport'));
				} else {
					if ($enrol_get_enrolment_end === 0 || $enrol_get_enrolment_end > time()) {
						$actived = html_writer::tag('span',
							get_string('actived', 'block_th_vmc_loginreport'),
							array('class' => 'badge badge-success')
						);
						$cell = new html_table_cell($actived);
					} else {
						$deactived = html_writer::tag('span',
							get_string('deactived', 'block_th_vmc_loginreport'),
							array('class' => 'badge badge-error')
						);
						$cell = new html_table_cell($deactived);
					}
				}
				$row->cells[] = $cell;
				$rightrows[$userid . '_' . $courseid] = $row;
			}
			$records = $DB->get_records_sql($sql_registeredcourses, $params);

			foreach ($records as $courseid => $record) {
				$row = new html_table_row();
				$linkCourse = html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $courseid, th_loginreport_get_fullname_course($courseid));
				$cell = new html_table_cell($linkCourse);
				$row->cells[] = $cell;
				$cell = new html_table_cell();
				$row->cells[] = $cell;
				$cell = new html_table_cell();
				$row->cells[] = $cell;
				$cell = new html_table_cell();
				$row->cells[] = $cell;
				$not_activated = html_writer::tag('span',
					get_string('not_activated', 'block_th_vmc_loginreport'),
					array('class' => 'badge badge-warning')
				);
				$cell = new html_table_cell($not_activated);
				$row->cells[] = $cell;
				$rightrows[$userid . '_' . $courseid] = $row;
			}
		}

		$stt = 0;
		foreach ($rightrows as $key => $row) {
			$arr = explode("_", $key);
			$userid = $arr[0];
			$row->cells = array_merge($leftrows[$userid]->cells, $row->cells);
			if ($stt != 0) {
				$c = new html_table_cell($stt);
				$row->cells[0] = $c;
			}
			$stt++;
			$table->data[] = $row;
		}
		$headrows = array_shift($table->data);
		$table->head = $headrows->cells;
		$table->align[0] = 'center';
		$table->align[$soCot + 2] = 'center';
		echo html_writer::table($table);
	}
}
echo $OUTPUT->footer();
?>
<script type="text/javascript">
  	$(document).ready(function() {
		$('input[type=radio][name=show_option]').change(function() {
			$('#fitem_id_courseidarr .col-form-label').removeAttr('hidden');
			$('#fitem_id_courseidarr .col-form-label .word-break').removeAttr('hidden');
		    $('#fitem_id_userid .col-form-label').removeAttr('hidden');
		    $('#fitem_id_userid .col-form-label .word-break').removeAttr('hidden');
		});
});
</script>