<?php

require_once '../../config.php';
require_once 'th_user_activity_report_form.php';
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
    print_error('invalidcourse', 'block_th_user_activity_report', $courseid);
}

require_login($courseid);
require_capability('block/th_user_activity_report:view', context_course::instance($COURSE->id));

$pageurl = '/blocks/th_user_activity_report/view.php';
$PAGE->set_url('/blocks/th_user_activity_report/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('heading', 'block_th_user_activity_report'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_user_activity_report'));

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

$PAGE->requires->js_call_amd('local_thlib/main', 'addAsteriskToCustomRequiredFieldForm', array($CFG->wwwroot));

$editurl = new moodle_url('/blocks/th_user_activity_report/view.php');
$settingsnode = $PAGE->settingsnav->add(get_string('breadcrumb', 'block_th_user_activity_report'), $editurl);
$settingsnode->make_active();

if (!isset($USER->gradeediting)) {
    $USER->gradeediting = array();
}

// $total_collapsed = array('aggregatesonly' => array(), 'gradesonly' => array());

$config = get_config('local_thlib');
$sortorder = "lastname,firstname";
if ($config->sortorder == 1) {
    $sortorder = "firstname,lastname";
}

$th_user_activity_report_form = new th_user_activity_report_form();
$malopid = optional_param('malopid', 0, PARAM_INT);
$makhoaid = optional_param('makhoaid', 0, PARAM_INT);
// $userid = optional_param('userid', 0, PARAM_INT);

$numlogin_op = optional_param('numlogin_op', 0, PARAM_INT);
$numlogin = optional_param('numlogin', 0, PARAM_INT);
$custom_role = optional_param('custom_role', -1, PARAM_INT);
$show_option = optional_param('show_option', 0, PARAM_INT);
$user_status = optional_param('user_status', 0, PARAM_INT);

$userid = null;
$useridget = $userid;
const TIMESPAN_GAP = 7 * 24 * 60 * 60;
const NUM_VALCOLUM = 2;

if ($th_user_activity_report_form->is_submitted()) {
    $fromform = $th_user_activity_report_form->get_data();

    if ($fromform) {

        $time_from = $fromform->time_from;
        $time_to = $fromform->time_to;
        $time_to = $time_to + strtotime("+23 hours 59 minutes 59 seconds", 0);
        $userid = $fromform->userid;
    }
} else {
    $time_from = optional_param('time_from', 0, PARAM_INT);
    if (!$time_from) {
        $time_from = time() + strtotime("-1 months", 0);
    }
    //
    $time_to = optional_param('time_to', time(), PARAM_INT);
    $th_user_activity_report_form->set_data(array('malopid' => $malopid, 'makhoaid' => $makhoaid, 'time_from' => $time_from,
        'time_to' => $time_to));
}

// $fromform = $th_user_activity_report_form->get_data();

$grade_item = new grade_item();

if ($makhoaid || $malopid || $userid || ($show_option == 2)) {

    if ($makhoaid || $malopid) {
        $makhoaarr = $th_user_activity_report_form->makhoaarr;
        $maloparr = $th_user_activity_report_form->maloparr;

        $makhoa = null;
        $malop = null;

        if ($makhoaid) {
            $makhoa = $makhoaarr[$makhoaid]->data;
        }

        if ($malopid) {
            $malop = $maloparr[$malopid]->data;
        }

        $userid_arr = get_user_filtered($makhoa, $malop, null, $user_status);

    } else if ($show_option == 2) {

        if ($userid == 0 && $custom_role == 0) {
            $userid_arr = array_keys($th_user_activity_report_form->user_arr);
        } else {

            $userid_arr = array_keys($th_user_activity_report_form->user_arr);
            $condition = [];
            if ($userid) {
                $userid_arr = array_values($userid);
            }

            if (count($userid_arr)) {
                list($insql_userid, $params_userid) = $DB->get_in_or_equal($userid_arr);
            } else {
                list($insql, $params) = $DB->get_in_or_equal(['']);
            }

            $fieldid = $th_user_activity_report_form->user_info_field_id;
            if ($custom_role) {
                $arr_roles = $th_user_activity_report_form->arr_roles;
                $rolename = $arr_roles[$custom_role];
                $defaultdata_role = $th_user_activity_report_form->defaultdata_role;

                // $condition = array_merge($condition, array('fieldid' => $th_user_activity_report_form->user_info_field_id));

                if ($rolename == $defaultdata_role) {
                    $rolesnotin = $arr_roles;
                    unset($rolesnotin[$custom_role]);
                    unset($rolesnotin[0]);
                    // print_object($rolesnotin);

                    if (count($rolesnotin)) {
                        list($insql_rolenotin, $params_rolenotin) = $DB->get_in_or_equal($rolesnotin);
                    } else {
                        list($insql_rolenotin, $params_rolenotin) = $DB->get_in_or_equal(['']);
                    }

                    $sql = "SELECT userid,data
					from {user_info_data}
					where fieldid = $fieldid
					and userid $insql_userid
					and data $insql_rolenotin";

                    $condition = array_merge($params_userid, $params_rolenotin);

                    $records = $DB->get_records_sql($sql, $condition);

                    $useridnotin_arr = array_keys($records);
                    $userid_arr = array_diff($userid_arr, $useridnotin_arr);

                } else {
                    $condition = array_merge($params_userid, array('fieldid' => $th_user_activity_report_form->user_info_field_id, 'data' => $rolename));

                    $sql = "SELECT userid,data
					from {user_info_data}
					where fieldid = $fieldid
					and userid $insql_userid
					and " . $DB->sql_compare_text('data') . " = '" . $rolename . "'";

                    $records = $DB->get_records_sql($sql, $condition);
                    $userid_arr = array_keys($records);
                }
            }
        }

        $userid_arr = thlib::filter_userarr_by_userstatus($userid_arr, $user_status);

    }

    if (sizeof($userid_arr)) {

        list($insql, $params) = $DB->get_in_or_equal($userid_arr, SQL_PARAMS_NAMED, 'ctx');
        $where = "userid $insql and action != 'failed' and target != 'user_login' and timecreated <= :time_to and timecreated >= :time_from";

        $params = array_merge($params, array('time_from' => $time_from, 'time_to' => $time_to));
        $logs = get_events_select1($where, $params);

        $timespan = $time_to - $time_from;
        $num_colum = floor($timespan / TIMESPAN_GAP);
        $num_colum = min($num_colum, NUM_VALCOLUM);

        $tt = $time_to;
        // $tf = $time_from;
        $timefromto_arr = [];
        for ($i = 1; $i <= $num_colum; $i++) {
            // echo $i;
            $timefromto = new stdClass();
            $timefromto->to = $tt;
            $tt = $tt - TIMESPAN_GAP;
            $timefromto->from = $tt + 1;
            $tt = $tt;

            $timefromto->fromd = get_datetime($timefromto->from);
            $timefromto->tod = get_datetime($timefromto->to);

            $timefromto_arr[] = $timefromto;
        }
        if ($tt > $time_from) {
            $timefromto = new stdClass();
            $timefromto->to = $tt;
            $timefromto->from = $time_from;
            $timefromto->fromd = get_datetime($timefromto->from);
            $timefromto->tod = get_datetime($timefromto->to);
            $timefromto_arr[] = $timefromto;
        }

        $userlogs = [];
        foreach ($logs as $key => $log) {
            $userid = $log->userid;
            $timeloggedin = $log->time;
            if (!array_key_exists($userid, $userlogs)) {
                $userlogs[$userid] = new stdClass();
                $userlogs[$userid]->logdetail = [];
                $userlogs[$userid]->totallog = 0;
            }
            $userlogs[$userid]->totallog++;
            foreach ($timefromto_arr as $idx => $tft) {
                $timefrom = $tft->from;
                $timeto = $tft->to;
                if (!array_key_exists($idx, $userlogs[$userid]->logdetail)) {
                    $userlogs[$userid]->logdetail[$idx] = 0;
                }
                if ($timeloggedin >= $timefrom && $timeloggedin <= $timeto) {
                    $userlogs[$userid]->logdetail[$idx]++;
                }
            }
        }

        $user_arr = $th_user_activity_report_form->user_arr;

        $table = new html_table();

        $rightrows = [];
        //
        // get left rows
        //
        // @var        array

        list($leftrows, $rows_ex_left) = get_left_rows($userid_arr, $user_arr);

        //
        // get right rows;
        //
        //
        $row = new html_table_row();
        $rows_ex_right = array();
        $row_ex = [];
        foreach ($timefromto_arr as $ktft => $timefromto) {
            $from = $timefromto->from;
            $to = $timefromto->to;
            $name = get_datetime($from, '%d/%m/%Y') . " -> " . get_datetime($to, '%d/%m/%Y');
            $cell = new html_table_cell($name);
            $cell->attributes['class'] = 'cell headingcell';
            $cell->header = true;
            $row->cells[] = $cell;
            $row_ex[] = $name;
        }

        $name = get_string('totalloggin', 'block_th_user_activity_report');
        $cell = new html_table_cell($name);
        $cell->attributes['class'] = 'cell headingcell';
        $cell->header = true;
        $row->cells[] = $cell;
        $row_ex[] = $name;

        $rows_ex_right[] = $row_ex;
        $rightrows[] = $row;

        foreach ($userid_arr as $key => $userid) {
            $totallog = 0;
            $row = new html_table_row();
            $row_ex = [];

            if (array_key_exists($userid, $userlogs)) {
                $ulog = $userlogs[$userid]->logdetail;
                $totallog = $userlogs[$userid]->totallog;
                foreach ($ulog as $kulog => $vulog) {
                    $cell = new html_table_cell($vulog);
                    $row_ex[] = $vulog;
                    $row->cells[] = $cell;
                }
            } else {
                foreach ($timefromto_arr as $ktft => $vtft) {
                    $cell = new html_table_cell(0);
                    $row_ex[] = 0;
                    $row->cells[] = $cell;
                }
            }

            $cell = new html_table_cell($totallog);
            $row_ex[] = $totallog;
            $row->cells[] = $cell;

            if ($numlogin_op == 0) {
                if ($totallog < $numlogin) {
                    $rightrows[$userid] = $row;
                }
            } else if ($numlogin_op == 1) {
                if ($totallog > $numlogin) {
                    $rightrows[$userid] = $row;
                }
            } else if ($numlogin_op == 2) {
                if ($totallog == $numlogin) {
                    $rightrows[$userid] = $row;
                }
            }

            $rows_ex_right[$userid] = $row_ex;
        }

        $table_ex = [];
        $count = 0;
        foreach ($rightrows as $key => $row) {
            $row->cells = array_merge($leftrows[$key]->cells, $row->cells);
            $count++;
            if ($count > 1) {
                $row->cells[0]->text = $count - 1;
            }
            $table->data[] = $row;
            $table_ex[] = array_merge($rows_ex_left[$key], $rows_ex_right[$key]);
        }

        $headrows = array_shift($table->data);
        $table->head = $headrows->cells;

        $table->attributes = array('class' => 'th_user_activity_report-grader-table');
        $html = html_writer::table($table);
        $html = $OUTPUT->container($html, 'gradeparent');

        $makhoaarr = $th_user_activity_report_form->makhoaarr;
        $maloparr = $th_user_activity_report_form->maloparr;

        $title_name = "";
        if ($makhoaid) {
            $title_name .= $makhoaarr[$makhoaid]->data;
        }
        if ($malopid) {
            $title_name .= $maloparr[$malopid]->data;
        }
        if ($useridget) {
            $title_name .= "user";
        }

    }
} else {

}

echo $OUTPUT->header();

$th_user_activity_report_form->display();

if (isset($html) && $html) {
    $lang = current_language();
    echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">';
    $PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_user_activity_report-grader-table', $title_name, $lang));

    echo $html;

}

echo $OUTPUT->footer();

?>

<script type="text/javascript">
  	$(document).ready(function() {
		$('input[type=radio][name=show_option]').change(function() {
			$('#fitem_id_makhoaid .col-form-label label').removeAttr('hidden');
			$('#fitem_id_malopid .col-form-label label').removeAttr('hidden');
			$('#fitem_id_userid .col-form-label label').removeAttr('hidden');
		});
	});
</script>