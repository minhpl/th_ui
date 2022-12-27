<?php
require_once '../../config.php';
require_once 'lib.php';
require_once $CFG->libdir . '/gradelib.php';
require_once $CFG->dirroot . '/user/renderer.php';
require_once $CFG->dirroot . '/grade/lib.php';
require_once $CFG->dirroot . '/grade/report/grader/lib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/externallib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';
require_once 'th_gradereport_form.php';
const DOWNLOAD_CSV = 1;
const DOWNLOAD_EXCEL = 2;
const SUMMARY_MODE = 0;
const DETAIL_MODE = 1;
global $DB, $OUTPUT, $PAGE, $COURSE;
// Check for all required variables.
$courseid = $COURSE->id;
// Next look for optional variables.
$downloadtype = optional_param('downloadtype', 0, PARAM_INT);
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_gradereport', $courseid);
}
require_login($courseid);
require_capability('block/th_gradereport:view', context_course::instance($COURSE->id));
$pageurl = '/blocks/th_gradereport/view.php';
$PAGE->set_url('/blocks/th_gradereport/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('heading', 'block_th_gradereport'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_gradereport'));
$lang = current_language();
// $PAGE->requires->jquery();
// $PAGE->requires->jquery_plugin('ui');
// $PAGE->requires->jquery_plugin('ui-css');
$editurl = new moodle_url('/blocks/th_gradereport/view.php');
$settingsnode = $PAGE->settingsnav->add(get_string('breadcrumb', 'block_th_gradereport'), $editurl);
$settingsnode->make_active();
if (!isset($USER->gradeediting)) {
	$USER->gradeediting = array();
}
// $total_collapsed = array('aggregatesonly' => array(), 'gradesonly' => array());
$th_gradereport_form = new th_gradereport_form();
// $malopid = optional_param('malopid', 0, PARAM_INT);
// $makhoaid = optional_param('makhoaid', 0, PARAM_INT);
// $userid = optional_param('userid', 0, PARAM_INT);
// $courseidarr_op = optional_param('courseidarr', 0, PARAM_RAW);
$summary_detail = optional_param('summary_detail', DETAIL_MODE, PARAM_INT);
$show_option = optional_param('show_option', 0, PARAM_INT);
$config = get_config('local_thlib');

$sortorder = "lastname,firstname";
if ($config->sortorder == 1) {
	$sortorder = "firstname,lastname";
}
if ($th_gradereport_form->is_submitted()) {
	$fromform = $th_gradereport_form->get_data();
	if ($fromform) {
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
	$th_gradereport_form->set_data(array( /*'malopid' => $malopid, 'makhoaid' => $makhoaid,*/'time_from' => $time_from,
		'time_to' => $time_to));
}
$fromform = $th_gradereport_form->get_data();

$makhoaidarr_op = [];
$malopidarr_op = [];
$useridarr_op = [];
$courseidarr_op = [];
$user_status = 0;
// $useridget = $userid;
$title_name = "";
if ($fromform) {
	$courseidarr_op = $fromform->courseidarr;
	$makhoaidarr_op = $fromform->makhoaid;
	$malopidarr_op = $fromform->malopid;
	$useridarr_op = $fromform->userid;
	$user_status = $fromform->user_status;
}
$grade_item = new grade_item();
echo $OUTPUT->header();
$th_gradereport_form->display();
$PAGE->requires->js_call_amd('local_thlib/loadCourseOption', 'loadCourseOption');
$PAGE->requires->js_call_amd('local_thlib/main', 'addAsteriskToCustomRequiredFieldForm', array($CFG->wwwroot));

if (sizeof($makhoaidarr_op) || sizeof($malopidarr_op) || sizeof($useridarr_op)) {
	$all_makhoaarr = $th_gradereport_form->makhoaarr;
	$all_maloparr = $th_gradereport_form->maloparr;
	$makhoaarr = [];
	$maloparr = [];
	foreach ($makhoaidarr_op as $key => $value) {
		$makhoaarr[] = $all_makhoaarr[$value]->data;
	}
	foreach ($malopidarr_op as $key => $value) {
		$maloparr[] = $all_maloparr[$value]->data;
	}
	$userid_arr = get_user_filtered_from_arrayof_makhoa_malop($makhoaarr, $maloparr, $useridarr_op, $user_status);
	if (sizeof($userid_arr)) {
		list($insql, $params) = $DB->get_in_or_equal($userid_arr, SQL_PARAMS_NAMED, 'ctx');
		$sql = "SELECT user_course.* ,{grade_grades}.finalgrade
				from(
					select row_number() OVER (Order by a.userid) as id, a.*
					from (
						select {user}.id as userid , course.fullname, course.id as courseid
						from
							{user}
							left join
							{user_enrolments} ue
							on {user}.id = ue.userid
							AND ((ue.timestart > :timefrom1 and ue.timestart!=0) OR (ue.timestart = 0 AND ue.timecreated > :timefrom2))
					AND ((ue.timestart < :timeend1 and ue.timestart!=0) OR (ue.timestart = 0 AND ue.timecreated < :timeend2))
							left join {enrol}
							on {enrol}.id = ue.enrolid
							left join {course} course
							on course.id = {enrol}.courseid and course.visible = 1
							where {user}.id $insql
						) a
						group by userid, courseid
					) user_course
				left join {grade_items}
				on {grade_items}.courseid = user_course.courseid and {grade_items}.itemtype='course'
				left join {grade_grades}
				on {grade_grades}.itemid = {grade_items}.id and {grade_grades}.userid = user_course.userid";
		$params = array_merge($params, array('timefrom1' => $time_from,
			'timefrom2' => $time_from, 'timeend1' => $time_to, 'timeend2' => $time_to));
		$records = $DB->get_records_sql($sql, $params);
		$rows = [];
		$courses_fullname = [];
		foreach ($records as $key => $value) {
			$userid = $value->userid;
			$courseid = $value->courseid;
			$coursefullname = $value->fullname;
			$finalgrade = $value->finalgrade;
			if (!array_key_exists($userid, $rows)) {
				$rows[$userid] = array();
			}
			if ($courseid) {
				$rows[$userid][$courseid] = $finalgrade;
				$courses_fullname[$courseid] = $coursefullname;
			}
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
		$user_arr = $th_gradereport_form->user_arr;
		if ($summary_detail == SUMMARY_MODE) {
			$table = new html_table();
			$rightrows = [];
			//
			// create headrows
			//
			// @var        array
			//

			$row_ex = array();
			$headrows = new html_table_row();

			foreach ($courses_fullname as $courseid => $coursename) {
				$cell = new html_table_cell($coursename);
				$cell->text = html_writer::link(new moodle_url('/grade/report/grader/index.php', array('id' => $courseid)), $coursename);
				$row_ex[] = $coursename;
				$cell->header = true;
				$cell->attributes['class'] = 'cell headingcell';
				$headrows->cells[] = $cell;
			}

			$table->data[] = $headrows;
			$rightrows[] = $headrows;

			$rowstemp = [];
			foreach ($userid_arr as $key => $userid) {
				if (array_key_exists($userid, $rows)) {
					$rowstemp[$userid] = $rows[$userid];
				}
			}
			$rows = $rowstemp;

			$userid_arr = [];
			//
			//create rows data html
			//
			$count = 0;
			foreach ($rows as $userid => $value) {
				$userid_arr[$userid] = $userid;

				$row = new html_table_row();
				$row_ex = array();

				$coursegrade = $value;
				foreach ($courses_fullname as $courseid => $coursename) {
					if (array_key_exists($courseid, $coursegrade)) {
						$finalgrade = $coursegrade[$courseid];
						if (!isset($finalgrade) || $finalgrade == '') {
							$finalgrade = null;
						}
						$finalgrade = grade_format_gradevalue($finalgrade, $grade_item, true, null, null);
						$cell = new html_table_cell();
						$row_ex[] = $finalgrade;
						$cell->text = html_writer::link(new moodle_url('/grade/report/user/index.php', array('id' => $courseid, 'userid' => $userid)), $finalgrade);
					} else {
						$finalgrade = "N\A";
						$cell = new html_table_cell($finalgrade);
						$row_ex[] = $finalgrade;
						$cell->text = $finalgrade;
					}
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = $finalgrade;
					$row->cells[] = $cell;
				}

				$table->data[] = $row;
				$rightrows[$userid] = $row;
			}

			$leftrows = get_left_rows($userid_arr, $user_arr);
			$leftrows = $leftrows[0];

			$table->data = [];
			foreach ($rightrows as $key => $row) {
				$row->cells = array_merge($leftrows[$key]->cells, $row->cells);
				$table->data[] = $row;
			}
			$headrows = array_shift($table->data);
			$table->head = $headrows->cells;

			$title_name = "";
			$table->attributes = array('class' => 'th_gradereport-grader-table');
			$html = html_writer::table($table);
			$html = $OUTPUT->container($html, 'gradeparent');

			echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">';
			$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_gradereport-grader-table', $title_name, $lang));
			echo $html;

		} else {
			foreach ($courses_fullname as $courseid => $coursename) {
				$context = context_course::instance($courseid);
				$USER->gradeediting[$courseid] = 0;
				$course = $DB->get_record('course', array('id' => $courseid));
				grade_regrade_final_grades_if_required($course);

				$gpr = new grade_plugin_return(
					array(
						'type' => 'report',
						'plugin' => 'grader',
						'course' => $course,
					)
				);
				$report = new th_grade_report_grader($courseid, $gpr, $context);
				$report->load_users();
				$report->load_final_grades();
				$displayaverages = true;
				$leftrows = $report->get_left_rows($displayaverages);
				$rightrows = $report->get_right_rows($displayaverages);

				if (sizeof($rightrows) > 0) {
					$lastrow = end($rightrows);
					$classattr = $lastrow->attributes['class'];
					if (strpos($classattr, 'avg') !== false) {
						$studentsize = sizeof($rightrows) - 2;
					} else {
						$studentsize = sizeof($rightrows) - 1;
					}
				}

				$leftrows = get_left_rows($userid_arr, $user_arr);

				$rightrows_grades = array_slice($rightrows, 1, $studentsize);
				$rightrows_header = array_shift($rightrows_grades);
				foreach ($rightrows_header->cells as $key => $cell) {
					$cell->attributes['class'] = 'cell headingcell header';
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = strip_tags($cell->text);
				}
				$rightrows_html = [];
				$rightrows_html[0] = $rightrows_header;
				// array_walk($rightrows_html[0]->cells,($item,$key){$item->attributes['class'] = 'heading';});
				$table = new html_table();
				foreach ($rightrows_grades as $key => $html_table_row) {
					$userid = $html_table_row->userid;
					array_walk($html_table_row->cells, function (&$cell, &$key) {
						$cell->attributes['data-order'] = $cell->attributes['data-search'] = strip_tags($cell->text);
					});
					$rightrows_html[$userid] = $html_table_row;
				}
				$left_rows_html = $leftrows[0];
				$count = 0;
				foreach ($rightrows_html as $key => $row) {
					$count++;

					if (!array_key_exists($key, $left_rows_html)) {
						continue;
					}

					if ($count > 1) {

						$left_rows_html[$key]->cells[0]->text = $count - 1;
					}
					$row->cells = array_merge($left_rows_html[$key]->cells, $row->cells);
					$table->data[] = $row;
				}
				$headrows = array_shift($table->data);
				$table->head = $headrows->cells;
				$title_name = $coursename;
				$table->attributes = array('class' => "th_gradereport-grader-table_$courseid");
				$html = html_writer::table($table);
				$html = $OUTPUT->container($html, 'gradeparent');
				echo '<br/>';
				echo '<br/>';
				echo html_writer::tag('h2', html_writer::link(new moodle_url('/grade/report/grader/index.php', array('id' => $courseid)), $coursename));
				echo $html;
				$PAGE->requires->js_call_amd('local_thlib/main', 'init', array(".th_gradereport-grader-table_$courseid", $title_name, $lang));
			}
		}
	}
} else if (sizeof($courseidarr_op)) {
	$allcourse = $th_gradereport_form->course_arr;
	if ($courseidarr_op != null) {
		$selected_courses = [];
		foreach ($courseidarr_op as $key => $courseid) {
			$selected_courses[$courseid] = $allcourse[$courseid];
		}
	} else {
		$selected_courses = $allcourse;
	}
	foreach ($selected_courses as $courseid => $course) {
		$context = context_course::instance($courseid);
		$USER->gradeediting[$courseid] = 0;
		$course = $DB->get_record('course', array('id' => $courseid));
		grade_regrade_final_grades_if_required($course);
// return tracking object
		$gpr = new grade_plugin_return(
			array(
				'type' => 'report',
				'plugin' => 'grader',
				'course' => $course,
			)
		);
		$report = new th_grade_report_grader($courseid, $gpr, $context, null, null, null);
		$userid_arr = $report->load_users($time_from, $time_to, $user_status, $sortorder);
		foreach ($userid_arr as $key => $value) {
			$userid_arr[$key] = $key;
		}
		$report->load_final_grades();
		$displayaverages = true;
		$leftrows = $report->get_left_rows($displayaverages);
		$rightrows = $report->get_right_rows($displayaverages);
		$user_arr = $th_gradereport_form->user_arr;

		$leftrows = get_left_rows($userid_arr, $user_arr);
		if (sizeof($rightrows) > 0) {
			$lastrow = end($rightrows);
			$classattr = $lastrow->attributes['class'];
			if (strpos($classattr, 'avg') !== false) {
				$studentsize = sizeof($rightrows) - 2;
			} else {
				$studentsize = sizeof($rightrows) - 1;
			}
		}
		$rightrows_grades = array_slice($rightrows, 1, $studentsize);
		if ($summary_detail == 0) {
			foreach ($rightrows_grades as $key => $rightrows_grade) {
				$rightrows_grades[$key]->cells = array_slice($rightrows_grade->cells, -1);
			}
		}
		$rightrows_header = array_shift($rightrows_grades);
		foreach ($rightrows_header->cells as $key => $cell) {
			$cell->attributes['class'] = 'cell headingcell header';
			$cell->attributes['data-order'] = $cell->attributes['data-search'] = strip_tags($cell->text);
		}
		$rightrows_html = [];
		$rightrows_html[0] = $rightrows_header;
// array_walk($rightrows_html[0]->cells,($item,$key){$item->attributes['class'] = 'heading';});
		$table = new html_table();
		foreach ($rightrows_grades as $key => $html_table_row) {
			$userid = $html_table_row->userid;
			array_walk($html_table_row->cells, function (&$cell, &$key) {
				$cell->attributes['data-order'] = $cell->attributes['data-search'] = strip_tags($cell->text);
			});
			$rightrows_html[$userid] = $html_table_row;
		}
		$left_rows_html = $leftrows[0];
		$count = 0;
		foreach ($rightrows_html as $key => $row) {
			$count++;
			if ($count > 1) {
				$left_rows_html[$key]->cells[0]->text = $count - 1;
			}
			$row->cells = array_merge($left_rows_html[$key]->cells, $row->cells);
			$table->data[] = $row;
		}
		$headrows = array_shift($table->data);
		$table->head = $headrows->cells;
		$title_name = $course->fullname;
		$coursename = $course->fullname;
		$table->attributes = array('class' => "th_gradereport-grader-table_$courseid");
		$html = html_writer::table($table);
		$html = $OUTPUT->container($html, 'gradeparent');
		echo '<br/>';
		echo '<br/>';
		echo html_writer::tag('h2', html_writer::link(new moodle_url('/grade/report/grader/index.php', array('id' => $courseid)), $coursename));
		echo $html;
		$PAGE->requires->js_call_amd('local_thlib/main', 'init', array(".th_gradereport-grader-table_$courseid", $title_name, $lang));
	}
}

echo $OUTPUT->footer();
?>
<script type="text/javascript">
$(document).ready(function() {
		$('input[type=radio][name=show_option]').change(function() {
		// $('#fitem_id_makhoaid .col-form-label').removeAttr('hidden');
		// $('#fitem_id_malopid .col-form-label').removeAttr('hidden');
		// $('#fitem_id_userid .col-form-label').removeAttr('hidden');
		// $('#fitem_id_courseidarr .col-form-label').removeAttr('hidden');
		$('#fitem_id_makhoaid .col-form-label label').removeAttr('hidden');
		$('#fitem_id_malopid .col-form-label label').removeAttr('hidden');
		$('#fitem_id_userid .col-form-label label').removeAttr('hidden');
		$('#fitem_id_courseidarr .col-form-label label').removeAttr('hidden');
	});
});
</script>