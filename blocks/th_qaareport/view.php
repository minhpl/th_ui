<?php

require_once '../../config.php';
require_once 'th_qaareport_form.php';
require_once 'lib.php';
require_once $CFG->libdir . '/gradelib.php';
require_once $CFG->dirroot . '/user/renderer.php';
require_once $CFG->dirroot . '/grade/lib.php';
require_once $CFG->dirroot . '/grade/report/grader/lib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';

const DOWNLOAD_CSV = 1;
const DOWNLOAD_EXCEL = 2;

global $DB, $OUTPUT, $PAGE, $COURSE;

// Check for all required variables.
$courseid = $COURSE->id;

// Next look for optional variables.
$downloadtype = optional_param('downloadtype', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_qaareport', $courseid);
}

require_login($courseid);

require_capability('block/th_qaareport:view', context_course::instance($COURSE->id));

$pageurl = '/blocks/th_qaareport/view.php';
$PAGE->set_url('/blocks/th_qaareport/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('heading', 'block_th_qaareport'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_qaareport'));

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

$editurl = new moodle_url('/blocks/th_qaareport/view.php');
$settingsnode = $PAGE->settingsnav->add(get_string('breadcrumb', 'block_th_qaareport'), $editurl);
$settingsnode->make_active();

$th_qaareport = new th_qaareport_form();

if (!isset($USER->gradeediting)) {
	$USER->gradeediting = array();
}

// $total_collapsed = array('aggregatesonly' => array(), 'gradesonly' => array());

$config = get_config('local_thlib');
$sortorder = "lastname,firstname";
if ($config->sortorder == 1) {
	$sortorder = "firstname,lastname";
}

$th_qaareport_form = new th_qaareport_form();
// $malopid = optional_param('malopid', 0, PARAM_INT);
// $makhoaid = optional_param('makhoaid', 0, PARAM_INT);
// $userid = optional_param('userid', 0, PARAM_INT);
// $teachingid = optional_param('teachingid', 0, PARAM_INT);
$show_option = optional_param('show_option', 0, PARAM_INT);
$user_status = optional_param('user_status', 0, PARAM_INT);

$title_name = "";

$courseidarr = null;
if ($th_qaareport_form->is_submitted()) {
	$fromform = $th_qaareport_form->get_data();
	if ($fromform) {

		$courseidarr = $fromform->courseidarr;
		$time_from = $fromform->time_from;
		$time_to = $fromform->time_to;
		$time_to = $time_to + strtotime("+23 hours 59 minutes 59 seconds", 0);
	}
} else {
	$time_from = optional_param('time_from', 0, PARAM_INT);
	if (!$time_from) {
		$time_from = time() + strtotime("-6 months", 0);
	}
	$time_to = optional_param('time_to', time(), PARAM_INT);
	$th_qaareport_form->set_data(array('time_from' => $time_from, 'time_to' => $time_to));
}

$fromform = $th_qaareport_form->get_data();
$makhoaidarr_op = [];
$malopidarr_op = [];
$useridarr_op = [];
$teachingidarr_op = [];
$courseidarr_op = [];

// $useridget = $userid;
$title_name = "";

if ($fromform) {
	$courseidarr_op = $fromform->courseidarr;
	$makhoaidarr_op = $fromform->makhoaid;
	$malopidarr_op = $fromform->malopid;
	$useridarr_op = $fromform->userid;
	$teachingidarr_op = $fromform->teachingid;
}

$grade_item = new grade_item();

echo $OUTPUT->header();
$PAGE->requires->js_call_amd('local_thlib/loadCourseOption', 'loadCourseOption');
$PAGE->requires->js_call_amd('local_thlib/main', 'addAsteriskToCustomRequiredFieldForm', array($CFG->wwwroot));

$th_qaareport_form->display();

echo "</br>";
echo "</br>";

$lang = current_language();

function get_html_table($courses, $time_from, $time_to) {
	$lang = current_language();
	$time24 = strtotime("+24 hours", 0);
	global $OUTPUT, $PAGE;
	$table = new html_table();
	$row = new html_table_row();

	$cell = new html_table_cell(get_string('course', 'local_thlib'));
	$cell->header = true;
	$cell->attributes['class'] = 'cell headingcell';
	$row->cells[] = $cell;
	$cell = new html_table_cell(get_string('totalcourse', 'local_thlib'));
	$cell->header = true;
	$cell->attributes['class'] = 'cell headingcell';
	$row->cells[] = $cell;
	$cell = new html_table_cell(get_string('answered', 'local_thlib'));
	$cell->header = true;
	$cell->attributes['class'] = 'cell headingcell';
	$row->cells[] = $cell;
	$cell = new html_table_cell(get_string('answeredwithin24', 'local_thlib'));
	$cell->header = true;
	$cell->attributes['class'] = 'cell headingcell';
	$row->cells[] = $cell;
	$cell = new html_table_cell(get_string('notanswered', 'local_thlib'));
	$cell->header = true;
	$cell->attributes['class'] = 'cell headingcell';
	$row->cells[] = $cell;
	$table->data[] = $row;

	foreach ($courses as $key => $course) {
		$context = context_course::instance($course->id);
		if (has_capability('block/th_qaareport:view', $context)) {
			// $qaapairs = $DB->get_records_sql($sql, ['courseid' => $course->id, 'time_from' => $time_from, 'time_to' => $time_to]);
			$qaapairs = get_course_qaaps($course->id, $time_from, $time_to);

			$total_question = 0;
			$num_answer = 0;
			$num_answer_24 = 0;
			$num_unanswer = 0;

			// get_users_by_capability;
			// get_user_capability_course;

			$row = new html_table_row();
			$cell = new html_table_cell();
			$coursename = $course->fullname;
			$cell->text = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)), $coursename);
			$cell->attributes['data-order'] = $cell->attributes['data-search'] = $coursename;
			$cell->attributes['class'] = 'user';
			$row->cells[] = $cell;

			foreach ($qaapairs as $k => $qaap) {
				if ($qaap->parentid == null) {
					$total_question++;
					if ($qaap->answer != null) {
						$num_answer++;
					} else {
						$num_unanswer++;
					}
					if (isset($qaap->timecreatedanswer) && $qaap->answer != null) {
						$elapse = $qaap->timecreatedanswer - $qaap->timemodifiedquestion;
						if ($elapse < $time24) {
							$num_answer_24++;
						}
					}
				}
			}

			$cell = new html_table_cell($total_question);
			$row->cells[] = $cell;
			$cell = new html_table_cell($num_answer);
			$row->cells[] = $cell;
			$cell = new html_table_cell($num_answer_24);
			$row->cells[] = $cell;
			$cell = new html_table_cell($num_unanswer);
			$row->cells[] = $cell;
			$table->data[] = $row;
		}
	}

	$headrows = array_shift($table->data);
	$table->head = $headrows->cells;

	$table->attributes = array('class' => 'th_qaareport-grader-table');
	$html = html_writer::table($table);
	$html = $OUTPUT->container($html, 'gradeparent');

	return $html;
}

if (sizeof($teachingidarr_op)) {

	$teaching_courses_arr = [];
	$course_arr = [];
	foreach ($teachingidarr_op as $key => $teachingid) {

		$time24 = strtotime("+24 hours", 0);

		$params = [];
		if ($user_status == thlib::USER_STATUS_ACTIVE) {
			$params = ['suspended' => 0];
		} else if ($user_status == thlib::USER_STATUS_SUPPENDED) {
			$params = ['suspended' => 1];
		}

		$params['id'] = $teachingid;

		$user = $DB->get_record('user', $params);

		if (!$user) {
			continue;
		}

		$fullname = fullname($user);

		$courses = enrol_get_all_users_courses($teachingid);

		$html = get_html_table($courses, $time_from, $time_to);
		echo html_writer::tag('h2', $fullname);
		echo $html;
		echo '</br>';
	}
	$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_qaareport-grader-table', 'qaa_report', $lang));
}

$userid_arr = [];
if (sizeof($makhoaidarr_op) || sizeof($malopidarr_op) || sizeof($useridarr_op)) {
	$all_makhoaarr = $th_qaareport_form->makhoaarr;
	$all_maloparr = $th_qaareport_form->maloparr;

	$makhoaarr = [];
	$maloparr = [];

	foreach ($makhoaidarr_op as $key => $value) {
		$makhoaarr[] = $all_makhoaarr[$value]->data;
	}

	foreach ($malopidarr_op as $key => $value) {
		$maloparr[] = $all_maloparr[$value]->data;
	}

	$userid_arr = get_user_filtered_from_arrayof_makhoa_malop($makhoaarr, $maloparr, $useridarr_op, $user_status);

} else if (sizeof($courseidarr_op)) {

	$config = get_config('local_thlib');
	$sortorder = "u.lastname,u.firstname";
	if ($config->sortorder == 1) {
		$sortorder = "u.firstname,u.lastname";
	}

	$userid_arr = [];
	foreach ($courseidarr_op as $key => $crsid) {
		$context_course = context_course::instance($crsid);
		$students = get_role_users(array(5), $context_course, false, 'ra.id,u.id,u.lastname, u.firstname', $sortorder);

		foreach ($students as $key => $value) {
			$userid_arr[$key] = $key;
		}
	}

	$userid_arr = thlib::filter_userarr_by_userstatus($userid_arr, $user_status);

}

if (sizeof($userid_arr)) {

	list($insql, $params) = $DB->get_in_or_equal($userid_arr, SQL_PARAMS_NAMED, 'ctx');

	$sql = "SELECT user_course.* , count({qaapairs}.qaaid) as numqaap
				from(
					select row_number() OVER (Order by a.userid) as id, a.*
					from (
						select {user}.id as userid , course.fullname, course.id as courseid
						from
							{user}
							left join
							{user_enrolments} ue
							on {user}.id = ue.userid
							left join {enrol}
							on {enrol}.id = ue.enrolid
							left join {course} course
							on course.id = {enrol}.courseid and course.visible = 1
							where {user}.id $insql
						) a
						group by userid, courseid
					) user_course
				left join {qaa}
				on {qaa}.course = user_course.courseid and user_course.userid
				left join {qaapairs}
				on {qaapairs}.studentid = user_course.userid and {qaapairs}.qaaid = {qaa}.id
				AND ({qaapairs}.timecreatedquestion > :timefrom1 )
				AND ({qaapairs}.timecreatedquestion < :timeend1 )
				group by courseid, userid";

	$params = array_merge($params, array('timefrom1' => $time_from, 'timefrom2' => $time_from, 'timeend1' => $time_to, 'timeend2' => $time_to));

	$records = $DB->get_records_sql($sql, $params);

	$rows = [];
	$courses_fullname = [];
	foreach ($records as $key => $value) {
		$userid = $value->userid;
		$courseid = $value->courseid;
		$coursefullname = $value->fullname;
		$numqaap = $value->numqaap;
		if (!array_key_exists($userid, $rows)) {
			$rows[$userid] = array();
		}
		if ($courseid) {
			$rows[$userid][$courseid] = $numqaap;
			$courses_fullname[$courseid] = $coursefullname;
		}
	}

	$courseidarr_op = [];
	if ($fromform) {
		$courseidarr_op = $fromform->courseidarr;
	}

	if (sizeof($courseidarr_op) && sizeof($courses_fullname)) {
		$temp = [];
		foreach ($courseidarr_op as $key => $value) {
			if (array_key_exists($value, $courses_fullname)) {
				$temp[$value] = $courses_fullname[$value];
			}
		}
		$courses_fullname = $temp;

	}

	$user_arr = $th_qaareport_form->user_arr;
	list($leftrows, $rows_ex_left) = get_left_rows($userid_arr, $user_arr);
	//create rightrows;

	$table = new html_table();

	$rightrows = [];
	$headrows = new html_table_row();

	foreach ($courses_fullname as $courseid => $coursename) {
		$cell = new html_table_cell($coursename);
		$cell->text = html_writer::link(new moodle_url('/course/view.php', array('id' => $courseid)), $coursename);
		$row_ex[] = $coursename;
		$cell->header = true;
		$cell->attributes['class'] = 'cell headingcell';
		$headrows->cells[] = $cell;
	}
	$rightrows[] = $headrows;

	$count = 0;
	foreach ($leftrows as $userid => $value) {

		if ($userid == 0) {
			continue;
		}

		$row = new html_table_row();

		if (array_key_exists($userid, $rows)) {

			$courseqaap = $rows[$userid];

			foreach ($courses_fullname as $courseid => $coursename) {

				if (array_key_exists($courseid, $courseqaap)) {
					$numqaap = $courseqaap[$courseid];
					if (!isset($numqaap) || $numqaap == '') {
						$numqaap = null;
					}
					// $numqaap = grade_format_gradevalue($numqaap, $grade_item, true, null, null);
					$cell = new html_table_cell();
					$cell->text = html_writer::link(new moodle_url('/course/view.php', array('id' => $courseid)), $numqaap);
				} else {
					$numqaap = "N\A";
					$cell = new html_table_cell($numqaap);
					$cell->text = $numqaap;
				}
				$cell->attributes['data-order'] = $cell->attributes['data-search'] = $numqaap;
				$row->cells[] = $cell;
			}
		} else {
			foreach ($courses_fullname as $courseid => $coursename) {
				$numqaap = "N\A";
				$cell = new html_table_cell($numqaap);
				$cell->text = $numqaap;
				$cell->attributes['data-order'] = $cell->attributes['data-search'] = $numqaap;
				$row->cells[] = $cell;
			}
		}

		$rightrows[$userid] = $row;
	}

	$makhoaarr = $th_qaareport_form->makhoaarr;
	$maloparr = $th_qaareport_form->maloparr;
	$title_name = "";

	// $table_ex = [];
	foreach ($rightrows as $key => $row) {
		$row->cells = array_merge($leftrows[$key]->cells, $row->cells);
		$table->data[] = $row;
		// $table_ex[] = array_merge($rows_ex_left[$key], $rows_ex_right[$key]);
	}
	$headrows = array_shift($table->data);
	$table->head = $headrows->cells;

	$table->attributes = array('class' => 'th_qaareport-grader-table');
	$html = html_writer::table($table);
	$html = $OUTPUT->container($html, 'gradeparent');

	echo '<br/>';
	echo '<br/>';
	echo html_writer::tag('h2', $title_name);
	echo $html;

	echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">';
	$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_qaareport-grader-table', $title_name, $lang));
}

echo $OUTPUT->footer();

?>

<script type="text/javascript">
  	$(document).ready(function() {
		$('input[type=radio][name=show_option]').change(function() {
			$('#fitem_id_makhoaid .col-form-label label').removeAttr('hidden');
		    $('#fitem_id_teachingid .col-form-label label').removeAttr('hidden');
		    $('#fitem_id_userid .col-form-label label').removeAttr('hidden');
	    	$('#fitem_id_malopid .col-form-label label').removeAttr('hidden');
	    	$('#fitem_id_courseidarr .col-form-label label').removeAttr('hidden');
		});

});
</script>