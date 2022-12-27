<?php
require_once "../../config.php";
require_once "$CFG->libdir/formslib.php";
require_once "th_enrollmentreport_form.php";
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once "lib.php";
global $DB, $CFG, $COURSE;
if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
    print_error('invalidcourse', 'block_th_enrollmentreport', $COURSE->id);
}
require_login($COURSE->id);
require_capability('block/th_enrollmentreport:view', context_course::instance($COURSE->id));

$PAGE->set_url(new moodle_url('/blocks/th_enrollmentreport/view.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('namereport', 'block_th_enrollmentreport'));
$PAGE->set_title(get_string('namereport', 'block_th_enrollmentreport'));
$editurl = new moodle_url('/blocks/th_enrollmentreport/view.php');
$settingsnode = $PAGE->settingsnav->add(get_string('namereport', 'block_th_enrollmentreport'), $editurl);
echo $OUTPUT->header();

$mform = new th_enrollmentreport_form();
$fromform = $mform->get_data();
$mform->display();

if ($mform->is_submitted()) {
    if (empty($fromform->areaids)) {
        $courses = $DB->get_records_sql('SELECT id,fullname FROM {course} WHERE summaryformat=?', ['1']);
        foreach ($courses as $course) {
            $fromform->areaids[] = $course->id;
        }
    }
    if ($fromform->startdate <= $fromform->enddate) {
        $to = $fromform->enddate + strtotime("+23 hours 59 minutes 59 seconds", 0);
        $from = $fromform->startdate;
        $title_name = '';
        if ($fromform->filter == 'day') {
            $title_name = 'Báo cáo học viên ghi danh theo ngày';
        }
        if ($fromform->filter == 'week') {
            $title_name = 'Báo cáo học viên ghi danh theo tuần';
        }
        if ($fromform->filter == 'month') {
            $title_name = 'Báo cáo học viên ghi danh theo tháng';
        }
        $lang = current_language();
        echo '<link rel="stylesheet" type="text/css" href="<https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">';
        $PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.reportenrollment-table', $title_name, $lang));
        $getUser = null;
        $getUser1 = null;
        $getUser2 = null;
        $getUserDayLeft = null;
        $last = 0;
        if ($fromform->filter == 'day') {
            $firstDayStart = strtotime(date("m/d/Y", strtotime("this day", $to)));

            $secondsDayStart = strtotime(date("m/d/Y", strtotime("last day", $to)));
            $secondsDayEnd = $secondsDayStart + strtotime("+23 hours 59 minutes 59 seconds", 0);

            $thirdDayStart = strtotime(date("m/d/Y", strtotime("last day -1 day", $to)));
            $thirdDayEnd = $thirdDayStart + strtotime("+23 hours 59 minutes 59 seconds", 0);

            $fourthDayEnd = strtotime(date("m/d/Y", strtotime("last day -2 day", $to))) + strtotime("+23 hours 59 minutes 59 seconds", 0);
            foreach ($fromform->areaids as $key => $courseid) {
                $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
                $id = $course->id;

                $getUser = laytaikhoan($firstDayStart, $to, $id);
                $sotk = count($getUser);

                if ($secondsDayStart >= $from) {
                    $getUser1 = laytaikhoan($secondsDayStart, $secondsDayEnd, $id);
                    $sotk1 = count($getUser1);
                }
                if ($thirdDayStart >= $from) {
                    $getUser2 = laytaikhoan($thirdDayStart, $thirdDayEnd, $id);
                    $sotk2 = count($getUser2);
                }
                if ($fourthDayEnd >= $from) {
                    $last = $fourthDayEnd;
                    $getUserDayLeft = laytaikhoan($from, $last, $id);
                    $countAccDayLeft = count($getUserDayLeft);
                }
                $userid_arr = [];
                if ($getUser != null) {
                    foreach ($getUser as $key => $value) {
                        $userid_arr[] = $value->id;
                    }
                }
                if ($getUser1 != null) {
                    foreach ($getUser1 as $key => $value) {
                        $userid_arr[] = $value->id;
                    }
                }
                if ($getUser2 != null) {
                    foreach ($getUser2 as $key => $value) {
                        $userid_arr[] = $value->id;
                    }
                }
                if ($getUserDayLeft != null) {
                    foreach ($getUserDayLeft as $key => $value) {
                        $userid_arr[] = $value->id;
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

                $user_arr = $DB->get_records('user', array('deleted' => 0, 'suspended' => 0), "", $alluserfields);
                // Start of table
                $table = new html_table();
                $headrows = new html_table_row();
                $cell = new html_table_cell(get_string('enrol', 'block_th_enrollmentreport'));
                $cell->attributes['class'] = 'cell headingcell';
                $cell->header = true;
                $headrows->cells[] = $cell;
                $cell = new html_table_cell(get_string('enddate', 'block_th_enrollmentreport'));
                $cell->attributes['class'] = 'cell headingcell';
                $cell->header = true;
                $headrows->cells[] = $cell;
                $cell = new html_table_cell(get_string('time'));
                $cell->attributes['class'] = 'cell headingcell';
                $cell->header = true;
                $headrows->cells[] = $cell;
                $cell = new html_table_cell(get_string('total', 'block_th_enrollmentreport'));
                $cell->attributes['class'] = 'cell headingcell';
                $cell->header = true;
                $headrows->cells[] = $cell;

                $rightrows = [];
                $rightrows[] = $headrows;
                if ($userid_arr) {
                    list($leftrows, $rows_ex_left) = get_left_rows($userid_arr, $user_arr);
                }
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
                //do du lieu ra bang
                if ($getUser != null) {
                    $in = true;
                    foreach ($getUser as $key => $value) {
                        $row = new html_table_row();
                        if ($value->timestart == 0) {
                            $cell = new html_table_cell('N/A');
                        } else {
                            $cell = new html_table_cell(day($value->timestart));
                        }
                        $cell->attributes['data-order'] = $value->timestart;
                        $row->cells[] = $cell;
                        if ($value->timeend == 0) {
                            $cell = new html_table_cell('N/A');
                        } else {
                            $cell = new html_table_cell(day($value->timeend));
                        }
                        $cell->attributes['data-order'] = $value->timeend;
                        $row->cells[] = $cell;
                        if ($in == true) {
                            $cell = new html_table_cell(day($firstDayStart));
                            $cell->attributes['data-order'] = $firstDayStart;
                            $cell->attributes['data-search'] = day($firstDayStart);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell($sotk);
                        }
                        if ($in == false) {
                            $cell = new html_table_cell();
                            $cell->attributes['data-order'] = $firstDayStart;
                            $cell->attributes['data-search'] = day($firstDayStart);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell();
                        }
                        $cell->attributes['data-order'] = $sotk;
                        $row->cells[] = $cell;
                        $rightrows[$key] = $row;
                        $in = false;
                    }
                } else {
                    $row = new html_table_row();
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                    for ($i = 0; $i < $cellLeft; $i++) {
                        $cell = new html_table_cell();
                        $row->cells[] = $cell;
                    }
                    $cell = new html_table_cell('N/A');
                    $row->cells[] = $cell;
                    for ($i = 0; $i < $cellRight + 2; $i++) {
                        $cell = new html_table_cell();
                        $row->cells[] = $cell;
                    }
                    $cell = new html_table_cell(day($firstDayStart));
                    $cell->attributes['data-order'] = $firstDayStart;
                    $cell->attributes['data-search'] = day($firstDayStart);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($sotk);
                    $cell->attributes['data-order'] = $sotk;
                    $row->cells[] = $cell;
                    $rightrows['s1'] = $row;
                }
                if ($getUser1 != null) {
                    $in = true;
                    foreach ($getUser1 as $key => $value) {
                        $row = new html_table_row();
                        if ($value->timestart == 0) {
                            $cell = new html_table_cell('N/A');
                        } else {
                            $cell = new html_table_cell(day($value->timestart));
                        }
                        $cell->attributes['data-order'] = $value->timestart;
                        $row->cells[] = $cell;
                        if ($value->timeend == 0) {
                            $cell = new html_table_cell('N/A');
                        } else {
                            $cell = new html_table_cell(day($value->timeend));
                        }
                        $cell->attributes['data-order'] = $value->timeend;
                        $row->cells[] = $cell;
                        if ($in == true) {
                            $cell = new html_table_cell(day($secondsDayStart));
                            $cell->attributes['data-order'] = $secondsDayStart;
                            $cell->attributes['data-search'] = day($secondsDayStart);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell($sotk1);
                        }
                        if ($in == false) {
                            $cell = new html_table_cell();
                            $cell->attributes['data-order'] = $secondsDayStart;
                            $cell->attributes['data-search'] = day($secondsDayStart);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell();
                        }
                        $cell->attributes['data-order'] = $sotk1;
                        $row->cells[] = $cell;
                        $rightrows[$key] = $row;
                        $in = false;
                    }
                } elseif ($secondsDayStart < $from) {

                } else {
                    $row = new html_table_row();
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                    for ($i = 0; $i < $cellLeft; $i++) {
                        $cell = new html_table_cell();
                        $row->cells[] = $cell;
                    }
                    $cell = new html_table_cell('N/A');
                    $row->cells[] = $cell;
                    for ($i = 0; $i < $cellRight + 2; $i++) {
                        $cell = new html_table_cell();
                        $row->cells[] = $cell;
                    }
                    $cell = new html_table_cell(day($secondsDayStart));
                    $cell->attributes['data-order'] = $secondsDayStart;
                    $cell->attributes['data-search'] = day($secondsDayStart);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($sotk1);
                    $cell->attributes['data-order'] = $sotk1;
                    $row->cells[] = $cell;
                    $rightrows['s2'] = $row;
                }
                if ($getUser2 != null) {
                    $in = true;
                    foreach ($getUser2 as $key => $value) {
                        $row = new html_table_row();
                        if ($value->timestart == 0) {
                            $cell = new html_table_cell('N/A');
                        } else {
                            $cell = new html_table_cell(day($value->timestart));
                        }
                        $cell->attributes['data-order'] = $value->timestart;
                        $row->cells[] = $cell;
                        if ($value->timeend == 0) {
                            $cell = new html_table_cell('N/A');
                        } else {
                            $cell = new html_table_cell(day($value->timeend));
                        }
                        $cell->attributes['data-order'] = $value->timeend;
                        $row->cells[] = $cell;
                        if ($in == true) {
                            $cell = new html_table_cell(day($thirdDayStart));
                            $cell->attributes['data-order'] = $thirdDayStart;
                            $cell->attributes['data-search'] = day($thirdDayStart);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell($sotk2);
                        }
                        if ($in == false) {
                            $cell = new html_table_cell();
                            $cell->attributes['data-order'] = $thirdDayStart;
                            $cell->attributes['data-search'] = day($thirdDayStart);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell();
                        }
                        $cell->attributes['data-order'] = $sotk2;
                        $row->cells[] = $cell;
                        $rightrows[$key] = $row;
                        $in = false;
                    }
                } elseif ($thirdDayStart < $from) {

                } else {
                    $row = new html_table_row();
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                    for ($i = 0; $i < $cellLeft; $i++) {
                        $cell = new html_table_cell();
                        $row->cells[] = $cell;
                    }
                    $cell = new html_table_cell('N/A');
                    $row->cells[] = $cell;
                    for ($i = 0; $i < $cellRight + 2; $i++) {
                        $cell = new html_table_cell();
                        $row->cells[] = $cell;
                    }
                    $cell = new html_table_cell(day($thirdDayStart));
                    $cell->attributes['data-order'] = $thirdDayStart;
                    $cell->attributes['data-search'] = day($thirdDayStart);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($sotk2);
                    $cell->attributes['data-order'] = $sotk2;
                    $row->cells[] = $cell;
                    $rightrows['s3'] = $row;
                }
                if ($getUserDayLeft != null) {
                    $in = true;
                    foreach ($getUserDayLeft as $key => $value) {
                        $row = new html_table_row();
                        if ($value->timestart == 0) {
                            $cell = new html_table_cell('N/A');
                        } else {
                            $cell = new html_table_cell(day($value->timestart));
                        }
                        $cell->attributes['data-order'] = $value->timestart;
                        $row->cells[] = $cell;
                        if ($value->timeend == 0) {
                            $cell = new html_table_cell('N/A');
                        } else {
                            $cell = new html_table_cell(day($value->timeend));
                        }
                        $cell->attributes['data-order'] = $value->timeend;
                        $row->cells[] = $cell;
                        if ($in == true) {
                            $cell = new html_table_cell(day($from) . ' - ' . day($last));
                            $cell->attributes['data-search'] = day($from) . ' - ' . day($last);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell($countAccDayLeft);
                        }
                        if ($in == false) {
                            $cell = new html_table_cell();
                            $cell->attributes['data-search'] = day($from) . ' - ' . day($last);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell();
                        }
                        $cell->attributes['data-order'] = $countAccDayLeft;
                        $row->cells[] = $cell;
                        $rightrows[$key] = $row;
                        $in = false;
                    }
                } elseif ($last < $from) {

                } else {
                    $row = new html_table_row();
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                    for ($i = 0; $i < $cellLeft; $i++) {
                        $cell = new html_table_cell();
                        $row->cells[] = $cell;
                    }
                    $cell = new html_table_cell('N/A');
                    $row->cells[] = $cell;
                    for ($i = 0; $i < $cellRight + 2; $i++) {
                        $cell = new html_table_cell();
                        $row->cells[] = $cell;
                    }
                    $cell = new html_table_cell(day($from) . ' - ' . day($last));
                    $cell->attributes['data-search'] = day($from) . ' - ' . day($last);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($countAccDayLeft);
                    $cell->attributes['data-order'] = $countAccDayLeft;
                    $row->cells[] = $cell;
                    $rightrows['s4'] = $row;
                }
                $stt = 0;
                foreach ($rightrows as $key => $row) {
                    if (!array_key_exists($key, $leftrows)) {
                        $row->cells = array_merge(array(), $row->cells);
                    } else {
                        $row->cells = array_merge($leftrows[$key]->cells, $row->cells);
                    }
                    if ($stt != 0) {
                        $row->cells[0]->text = $stt;
                    }
                    $stt++;
                    $table->data[] = $row;
                }
                $headrows = array_shift($table->data);
                $table->head = $headrows->cells;
                $table->attributes = array('class' => 'reportenrollment-table', 'border' => '1');
                $table->align[0] = 'center';
                $table->align[$soCot + 3] = 'center';
                echo html_writer::tag('h2', html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $id, $course->fullname));
                echo html_writer::table($table);
                echo '</br>';
            }
        }
        if ($fromform->filter == 'week') {

            $thisWeekMonday = date("m/d/Y", strtotime("this week monday", $to));
            $thisWeekMonday = strtotime($thisWeekMonday);

            $thisWeekSunday = date("m/d/Y", strtotime("this week sunday", $to));
            $thisWeekSunday = strtotime($thisWeekSunday) + strtotime("+23 hours 59 minutes 59 seconds", 0);

            $lastWeekMonday = date("m/d/Y", strtotime("last week monday", $to));
            $lastWeekMonday = strtotime($lastWeekMonday);

            $lastWeekSunday = date("m/d/Y", strtotime("last week sunday", $to));
            $lastWeekSunday = strtotime($lastWeekSunday) + strtotime("+23 hours 59 minutes 59 seconds", 0);

            $lastTwoWeekMonday = date("m/d/Y", strtotime("last week monday -1 week", $to));
            $lastTwoWeekMonday = strtotime($lastTwoWeekMonday);

            $lastTwoWeekSunday = date("m/d/Y", strtotime("last week sunday -1 week", $to));
            $lastTwoWeekSunday = strtotime($lastTwoWeekSunday) + strtotime("+23 hours 59 minutes 59 seconds", 0);

            $lastThreeWeekSunday = date("m/d/Y", strtotime("last week sunday -2 week", $to));
            $lastThreeWeekSunday = strtotime($lastThreeWeekSunday) + strtotime("+23 hours 59 minutes 59 seconds", 0);

            foreach ($fromform->areaids as $key => $courseid) {
                $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
                $id = $course->id;

                $getUser = laytaikhoan($thisWeekMonday, $thisWeekSunday, $id);
                $sotk = count($getUser);

                if ($thisWeekMonday > $from) {
                    $getUser1 = laytaikhoan($lastWeekMonday, $lastWeekSunday, $id);
                    $sotk1 = count($getUser1);
                }

                if ($lastWeekMonday > $from) {
                    $getUser2 = laytaikhoan($lastTwoWeekMonday, $lastTwoWeekSunday, $id);
                    $sotk2 = count($getUser2);
                }
                if ($lastTwoWeekMonday > $from) {
                    $getUserDayLeft = laytaikhoan($from, $lastThreeWeekSunday, $id);
                    $countAccDayLeft = count($getUserDayLeft);
                }
                $userid_arr = [];
                if ($getUser != null) {
                    foreach ($getUser as $key => $value) {
                        $userid_arr[] = $value->id;
                    }
                }
                if ($getUser1 != null) {
                    foreach ($getUser1 as $key => $value) {
                        $userid_arr[] = $value->id;
                    }
                }
                if ($getUser2 != null) {
                    foreach ($getUser2 as $key => $value) {
                        $userid_arr[] = $value->id;
                    }
                }
                if ($getUserDayLeft != null) {
                    foreach ($getUserDayLeft as $key => $value) {
                        $userid_arr[] = $value->id;
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

                $user_arr = $DB->get_records('user', array('deleted' => 0, 'suspended' => 0), "", $alluserfields);
                $table = new html_table();

                $headrows = new html_table_row();
                $cell = new html_table_cell(get_string('enrol', 'block_th_enrollmentreport'));
                $cell->attributes['class'] = 'cell headingcell';
                $cell->header = true;
                $headrows->cells[] = $cell;
                $cell = new html_table_cell(get_string('enddate', 'block_th_enrollmentreport'));
                $cell->attributes['class'] = 'cell headingcell';
                $cell->header = true;
                $headrows->cells[] = $cell;
                $cell = new html_table_cell(get_string('time'));
                $cell->attributes['class'] = 'cell headingcell';
                $cell->header = true;
                $headrows->cells[] = $cell;
                $cell = new html_table_cell(get_string('total', 'block_th_enrollmentreport'));
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
                //do du lieu ra bang
                if ($getUser != null) {
                    $in = true;
                    foreach ($getUser as $key => $value) {
                        $row = new html_table_row();
                        if ($value->timestart == 0) {
                            $cell = new html_table_cell('N/A');
                        } else {
                            $cell = new html_table_cell(day($value->timestart));
                        }
                        $cell->attributes['data-order'] = $value->timestart;
                        $row->cells[] = $cell;
                        if ($value->timeend == 0) {
                            $cell = new html_table_cell('N/A');
                        } else {
                            $cell = new html_table_cell(day($value->timeend));
                        }
                        $cell->attributes['data-order'] = $value->timeend;
                        $row->cells[] = $cell;
                        if ($in == true) {
                            $cell = new html_table_cell(day($thisWeekMonday) . ' - ' . day($thisWeekSunday));
                            $cell->attributes['data-search'] = day($thisWeekMonday) . ' - ' . day($thisWeekSunday);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell($sotk);
                        }
                        if ($in == false) {
                            $cell = new html_table_cell();
                            $cell->attributes['data-search'] = day($thisWeekMonday) . ' - ' . day($thisWeekSunday);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell();
                        }
                        $cell->attributes['data-order'] = $sotk;
                        $row->cells[] = $cell;
                        $rightrows[$key] = $row;
                        $in = false;
                    }
                } else {
                    $row = new html_table_row();
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                    for ($i = 0; $i < $cellLeft; $i++) {
                        $cell = new html_table_cell();
                        $row->cells[] = $cell;
                    }
                    $cell = new html_table_cell('N/A');
                    $row->cells[] = $cell;
                    for ($i = 0; $i < $cellRight + 2; $i++) {
                        $cell = new html_table_cell();
                        $row->cells[] = $cell;
                    }
                    $cell = new html_table_cell(day($thisWeekMonday) . ' - ' . day($thisWeekSunday));
                    $cell->attributes['data-search'] = day($thisWeekMonday) . ' - ' . day($thisWeekSunday);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($sotk);
                    $cell->attributes['data-order'] = $sotk;
                    $row->cells[] = $cell;
                    $rightrows['s1'] = $row;
                }
                if ($getUser1 != null) {
                    $in = true;
                    foreach ($getUser1 as $key => $value) {
                        $row = new html_table_row();
                        if ($value->timestart == 0) {
                            $cell = new html_table_cell('N/A');
                        } else {
                            $cell = new html_table_cell(day($value->timestart));
                        }
                        $cell->attributes['data-order'] = $value->timestart;
                        $row->cells[] = $cell;
                        if ($value->timeend == 0) {
                            $cell = new html_table_cell('N/A');
                        } else {
                            $cell = new html_table_cell(day($value->timeend));
                        }
                        $cell->attributes['data-order'] = $value->timeend;
                        $row->cells[] = $cell;
                        if ($in == true) {
                            $cell = new html_table_cell(day($lastWeekMonday) . ' - ' . day($lastWeekSunday));
                            $cell->attributes['data-search'] = day($lastWeekMonday) . ' - ' . day($lastWeekSunday);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell($sotk1);
                        }
                        if ($in == false) {
                            $cell = new html_table_cell();
                            $cell->attributes['data-search'] = day($lastWeekMonday) . ' - ' . day($lastWeekSunday);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell();

                        }
                        $cell->attributes['data-order'] = $sotk1;
                        $row->cells[] = $cell;
                        $rightrows[$key] = $row;
                        $in = false;
                    }
                } elseif ($thisWeekMonday <= $from) {

                } else {
                    $row = new html_table_row();
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                    for ($i = 0; $i < $cellLeft; $i++) {
                        $cell = new html_table_cell();
                        $row->cells[] = $cell;
                    }
                    $cell = new html_table_cell('N/A');
                    $row->cells[] = $cell;
                    for ($i = 0; $i < $cellRight + 2; $i++) {
                        $cell = new html_table_cell();
                        $row->cells[] = $cell;
                    }
                    $cell = new html_table_cell(day($lastWeekMonday) . ' - ' . day($lastWeekSunday));
                    $cell->attributes['data-search'] = day($lastWeekMonday) . ' - ' . day($lastWeekSunday);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($sotk1);
                    $cell->attributes['data-order'] = $sotk1;
                    $row->cells[] = $cell;
                    $rightrows['s2'] = $row;
                }
                if ($getUser2 != null) {
                    $in = true;
                    foreach ($getUser2 as $key => $value) {
                        $row = new html_table_row();
                        if ($value->timestart == 0) {
                            $cell = new html_table_cell('N/A');
                        } else {
                            $cell = new html_table_cell(day($value->timestart));
                        }
                        $cell->attributes['data-order'] = $value->timestart;
                        $row->cells[] = $cell;
                        if ($value->timeend == 0) {
                            $cell = new html_table_cell('N/A');
                        } else {
                            $cell = new html_table_cell(day($value->timeend));
                        }
                        $cell->attributes['data-order'] = $value->timeend;
                        $row->cells[] = $cell;
                        if ($in == true) {
                            $cell = new html_table_cell(day($lastTwoWeekMonday) . ' - ' . day($lastTwoWeekSunday));
                            $cell->attributes['data-search'] = day($lastTwoWeekMonday) . ' - ' . day($lastTwoWeekSunday);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell($sotk2);
                        }
                        if ($in == false) {
                            $cell = new html_table_cell();
                            $cell->attributes['data-search'] = day($lastTwoWeekMonday) . ' - ' . day($lastTwoWeekSunday);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell();
                        }
                        $cell->attributes['data-order'] = $sotk2;
                        $row->cells[] = $cell;
                        $rightrows[$key] = $row;
                        $in = false;
                    }
                } elseif ($lastWeekMonday <= $from) {
                } else {
                    $row = new html_table_row();
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                    for ($i = 0; $i < $cellLeft; $i++) {
                        $cell = new html_table_cell();
                        $row->cells[] = $cell;
                    }
                    $cell = new html_table_cell('N/A');
                    $row->cells[] = $cell;
                    for ($i = 0; $i < $cellRight + 2; $i++) {
                        $cell = new html_table_cell();
                        $row->cells[] = $cell;
                    }
                    $cell = new html_table_cell(day($lastTwoWeekMonday) . ' - ' . day($lastTwoWeekSunday));
                    $cell->attributes['data-search'] = day($lastTwoWeekMonday) . ' - ' . day($lastTwoWeekSunday);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($sotk2);
                    $cell->attributes['data-order'] = $sotk2;
                    $row->cells[] = $cell;
                    $rightrows['s3'] = $row;
                }
                if ($getUserDayLeft != null) {
                    $in = true;
                    foreach ($getUserDayLeft as $key => $value) {
                        $row = new html_table_row();
                        if ($value->timestart == 0) {
                            $cell = new html_table_cell('N/A');
                        } else {
                            $cell = new html_table_cell(day($value->timestart));
                        }
                        $cell->attributes['data-order'] = $value->timestart;
                        $row->cells[] = $cell;
                        if ($value->timeend == 0) {
                            $cell = new html_table_cell('N/A');
                        } else {
                            $cell = new html_table_cell(day($value->timeend));
                        }
                        $cell->attributes['data-order'] = $value->timeend;
                        $row->cells[] = $cell;
                        if ($in == true) {
                            $cell = new html_table_cell(day($from) . ' - ' . day($lastThreeWeekSunday));
                            $cell->attributes['data-search'] = day($from) . ' - ' . day($lastThreeWeekSunday);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell($countAccDayLeft);
                        }
                        if ($in == false) {
                            $cell = new html_table_cell();
                            $cell->attributes['data-search'] = day($from) . ' - ' . day($lastThreeWeekSunday);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell();
                        }
                        $cell->attributes['data-order'] = $countAccDayLeft;
                        $row->cells[] = $cell;
                        $rightrows[$key] = $row;
                        $in = false;
                    }
                } elseif ($lastTwoWeekMonday <= $from) {
                } else {
                    $row = new html_table_row();
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                    for ($i = 0; $i < $cellLeft; $i++) {
                        $cell = new html_table_cell();
                        $row->cells[] = $cell;
                    }
                    $cell = new html_table_cell('N/A');
                    $row->cells[] = $cell;
                    for ($i = 0; $i < $cellRight + 2; $i++) {
                        $cell = new html_table_cell();
                        $row->cells[] = $cell;
                    }
                    $cell = new html_table_cell(day($from) . ' - ' . day($lastThreeWeekSunday));
                    $cell->attributes['data-search'] = day($from) . ' - ' . day($lastThreeWeekSunday);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($countAccDayLeft);
                    $cell->attributes['data-order'] = $countAccDayLeft;
                    $row->cells[] = $cell;
                    $rightrows['s4'] = $row;
                }
                $stt = 0;
                foreach ($rightrows as $key => $row) {
                    if (!array_key_exists($key, $leftrows)) {
                        $row->cells = array_merge(array(), $row->cells);
                    } else {
                        $row->cells = array_merge($leftrows[$key]->cells, $row->cells);
                    }
                    if ($stt != 0) {
                        $row->cells[0]->text = $stt;
                    }
                    $stt++;
                    $table->data[] = $row;
                }
                $headrows = array_shift($table->data);
                $table->head = $headrows->cells;
                $table->attributes = array('class' => 'reportenrollment-table', 'border' => '1');
                $table->align[0] = 'center';
                $table->align[$soCot + 3] = 'center';
                echo html_writer::tag('h2', html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $id, $course->fullname));
                echo html_writer::table($table);
                echo '</br>';
            }
        }
        if ($fromform->filter == 'month') {

            $firstDayThisMonth = date("m/d/Y", strtotime("first day of this month", $to));
            $firstDayThisMonth = strtotime($firstDayThisMonth);

            $lastDayThisMonth = date("m/d/Y", strtotime("last day of this month", $to));
            $lastDayThisMonth = strtotime($lastDayThisMonth) + strtotime("+23 hours 59 minutes 59 seconds", 0);

            $firstDayAfterOneMonth = date("m/d/Y", strtotime("first day of last month", $to));
            $firstDayAfterOneMonth = strtotime($firstDayAfterOneMonth);

            $lastDayAfterOneMonth = date("m/d/Y", strtotime("last day of last month", $to));
            $lastDayAfterOneMonth = strtotime($lastDayAfterOneMonth) + strtotime("+23 hours 59 minutes 59 seconds", 0);

            $firstDayAfterTwoMonth = date("m/d/Y", strtotime("first day of last month -1 month", $to));
            $firstDayAfterTwoMonth = strtotime($firstDayAfterTwoMonth);

            $lastDayAfterTwoMonth = date("m/d/Y", strtotime("last day of last month -1 month", $to));
            $lastDayAfterTwoMonth = strtotime($lastDayAfterTwoMonth) + strtotime("+23 hours 59 minutes 59 seconds", 0);

            $lastDayAfterThreeMonth = date("m/d/Y", strtotime("last day of last month -2 month", $to));
            $lastDayAfterThreeMonth = strtotime($lastDayAfterThreeMonth) + strtotime("+23 hours 59 minutes 59 seconds", 0);
            foreach ($fromform->areaids as $key => $courseid) {
                $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
                $id = $course->id;

                $getUser = laytaikhoan($firstDayThisMonth, $lastDayThisMonth, $id);
                $sotk = count($getUser);

                if ($firstDayThisMonth > $from) {
                    $getUser1 = laytaikhoan($firstDayAfterOneMonth, $lastDayAfterOneMonth, $id);
                    $sotk1 = count($getUser1);
                }

                if ($firstDayAfterOneMonth > $from) {
                    $getUser2 = laytaikhoan($firstDayAfterTwoMonth, $lastDayAfterTwoMonth, $id);
                    $sotk2 = count($getUser2);
                }

                if ($firstDayAfterTwoMonth > $from) {
                    $getUserDayLeft = laytaikhoan($from, $lastDayAfterThreeMonth, $id);
                    $countAccDayLeft = count($getUserDayLeft);
                }

                $userid_arr = [];
                if ($getUser != null) {
                    foreach ($getUser as $key => $value) {
                        $userid_arr[] = $value->id;
                    }
                }
                if ($getUser1 != null) {
                    foreach ($getUser1 as $key => $value) {
                        $userid_arr[] = $value->id;
                    }
                }
                if ($getUser2 != null) {
                    foreach ($getUser2 as $key => $value) {
                        $userid_arr[] = $value->id;
                    }
                }
                if ($getUserDayLeft != null) {
                    foreach ($getUserDayLeft as $key => $value) {
                        $userid_arr[] = $value->id;
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

                $user_arr = $DB->get_records('user', array('deleted' => 0, 'suspended' => 0), "", $alluserfields);
                $table = new html_table();

                $headrows = new html_table_row();
                $cell = new html_table_cell(get_string('enrol', 'block_th_enrollmentreport'));
                $cell->attributes['class'] = 'cell headingcell';
                $cell->header = true;
                $headrows->cells[] = $cell;
                $cell = new html_table_cell(get_string('enddate', 'block_th_enrollmentreport'));
                $cell->attributes['class'] = 'cell headingcell';
                $cell->header = true;
                $headrows->cells[] = $cell;
                $cell = new html_table_cell(get_string('time'));
                $cell->attributes['class'] = 'cell headingcell';
                $cell->header = true;
                $headrows->cells[] = $cell;
                $cell = new html_table_cell(get_string('total', 'block_th_enrollmentreport'));
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
                //do du lieu ra bang
                if ($getUser != null) {
                    $in = true;
                    foreach ($getUser as $key => $value) {
                        $row = new html_table_row();
                        if ($value->timestart == 0) {
                            $cell = new html_table_cell('N/A');
                        } else {
                            $cell = new html_table_cell(day($value->timestart));
                        }
                        $cell->attributes['data-order'] = $value->timestart;
                        $row->cells[] = $cell;
                        if ($value->timeend == 0) {
                            $cell = new html_table_cell('N/A');
                        } else {
                            $cell = new html_table_cell(day($value->timeend));
                        }
                        $cell->attributes['data-order'] = $value->timeend;
                        $row->cells[] = $cell;
                        if ($in == true) {
                            $cell = new html_table_cell(day($firstDayThisMonth) . ' - ' . day($lastDayThisMonth));
                            $cell->attributes['data-search'] = day($firstDayThisMonth) . ' - ' . day($lastDayThisMonth);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell($sotk);
                        }
                        if ($in == false) {
                            $cell = new html_table_cell();
                            $cell->attributes['data-search'] = day($firstDayThisMonth) . ' - ' . day($lastDayThisMonth);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell();
                        }
                        $cell->attributes['data-order'] = $sotk;
                        $row->cells[] = $cell;
                        $rightrows[$key] = $row;
                        $in = false;
                    }
                } else {
                    $row = new html_table_row();
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                    for ($i = 0; $i < $cellLeft; $i++) {
                        $cell = new html_table_cell();
                        $row->cells[] = $cell;
                    }
                    $cell = new html_table_cell('N/A');
                    $row->cells[] = $cell;
                    for ($i = 0; $i < $cellRight + 2; $i++) {
                        $cell = new html_table_cell();
                        $row->cells[] = $cell;
                    }
                    $cell = new html_table_cell(day($firstDayThisMonth) . ' - ' . day($lastDayThisMonth));
                    $cell->attributes['data-search'] = day($firstDayThisMonth) . ' - ' . day($lastDayThisMonth);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($sotk);
                    $cell->attributes['data-order'] = $sotk;
                    $row->cells[] = $cell;
                    $rightrows['s1'] = $row;
                }

                if ($getUser1 != null) {
                    $in = true;
                    foreach ($getUser1 as $key => $value) {
                        $row = new html_table_row();
                        if ($value->timestart == 0) {
                            $cell = new html_table_cell('N/A');
                        } else {
                            $cell = new html_table_cell(day($value->timestart));
                        }
                        $cell->attributes['data-order'] = $value->timestart;
                        $row->cells[] = $cell;
                        if ($value->timeend == 0) {
                            $cell = new html_table_cell('N/A');
                        } else {
                            $cell = new html_table_cell(day($value->timeend));
                        }
                        $cell->attributes['data-order'] = $value->timeend;
                        $row->cells[] = $cell;
                        if ($in == true) {
                            $cell = new html_table_cell(day($firstDayAfterOneMonth) . ' - ' . day($lastDayAfterOneMonth));
                            $cell->attributes['data-search'] = day($firstDayAfterOneMonth) . ' - ' . day($lastDayAfterOneMonth);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell($sotk1);
                        }
                        if ($in == false) {
                            $cell = new html_table_cell();
                            $cell->attributes['data-search'] = day($firstDayAfterOneMonth) . ' - ' . day($lastDayAfterOneMonth);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell();
                        }
                        $cell->attributes['data-order'] = $sotk1;
                        $row->cells[] = $cell;
                        $rightrows[$key] = $row;
                        $in = false;
                    }
                } elseif ($firstDayThisMonth <= $from) {

                } else {
                    $row = new html_table_row();
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                    for ($i = 0; $i < $cellLeft; $i++) {
                        $cell = new html_table_cell();
                        $row->cells[] = $cell;
                    }
                    $cell = new html_table_cell('N/A');
                    $row->cells[] = $cell;
                    for ($i = 0; $i < $cellRight + 2; $i++) {
                        $cell = new html_table_cell();
                        $row->cells[] = $cell;
                    }
                    $cell = new html_table_cell(day($firstDayAfterOneMonth) . ' - ' . day($lastDayAfterOneMonth));
                    $cell->attributes['data-search'] = day($firstDayAfterOneMonth) . ' - ' . day($lastDayAfterOneMonth);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($sotk1);
                    $cell->attributes['data-order'] = $sotk1;
                    $row->cells[] = $cell;
                    $rightrows['s2'] = $row;
                }
                if ($getUser2 != null) {
                    $in = true;
                    foreach ($getUser2 as $key => $value) {
                        $row = new html_table_row();
                        if ($value->timestart == 0) {
                            $cell = new html_table_cell('N/A');
                        } else {
                            $cell = new html_table_cell(day($value->timestart));
                        }
                        $cell->attributes['data-order'] = $value->timestart;
                        $row->cells[] = $cell;
                        if ($value->timeend == 0) {
                            $cell = new html_table_cell('N/A');
                        } else {
                            $cell = new html_table_cell(day($value->timeend));
                        }
                        $cell->attributes['data-order'] = $value->timeend;
                        $row->cells[] = $cell;
                        if ($in == true) {
                            $cell = new html_table_cell(day($firstDayAfterTwoMonth) . ' - ' . day(layngay($to, '-2 month -1 day')));
                            $cell->attributes['data-search'] = day($firstDayAfterTwoMonth) . ' - ' . day($lastDayAfterTwoMonth);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell($sotk2);
                        }
                        if ($in == false) {
                            $cell = new html_table_cell();
                            $cell->attributes['data-search'] = day($firstDayAfterTwoMonth) . ' - ' . day($lastDayAfterTwoMonth);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell();
                        }
                        $cell->attributes['data-order'] = $sotk2;
                        $row->cells[] = $cell;
                        $rightrows[$key] = $row;
                        $in = false;
                    }
                } elseif ($firstDayAfterOneMonth <= $from) {

                } else {
                    $row = new html_table_row();
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                    for ($i = 0; $i < $cellLeft; $i++) {
                        $cell = new html_table_cell();
                        $row->cells[] = $cell;
                    }
                    $cell = new html_table_cell('N/A');
                    $row->cells[] = $cell;
                    for ($i = 0; $i < $cellRight + 2; $i++) {
                        $cell = new html_table_cell();
                        $row->cells[] = $cell;
                    }
                    $cell = new html_table_cell(day($firstDayAfterTwoMonth) . ' - ' . day($lastDayAfterTwoMonth));
                    $cell->attributes['data-search'] = day($firstDayAfterTwoMonth) . ' - ' . day($lastDayAfterTwoMonth);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($sotk2);
                    $cell->attributes['data-order'] = $sotk2;
                    $row->cells[] = $cell;
                    $rightrows['s3'] = $row;
                }
                if ($getUserDayLeft != null) {
                    $in = true;
                    foreach ($getUserDayLeft as $key => $value) {
                        $row = new html_table_row();
                        if ($value->timestart == 0) {
                            $cell = new html_table_cell('N/A');
                        } else {
                            $cell = new html_table_cell(day($value->timestart));
                        }
                        $cell->attributes['data-order'] = $value->timestart;
                        $row->cells[] = $cell;
                        if ($value->timeend == 0) {
                            $cell = new html_table_cell('N/A');
                        } else {
                            $cell = new html_table_cell(day($value->timeend));
                        }
                        $cell->attributes['data-order'] = $value->timeend;
                        $row->cells[] = $cell;
                        if ($in == true) {
                            $cell = new html_table_cell(day($from) . ' - ' . day($lastDayAfterThreeMonth));
                            $cell->attributes['data-search'] = day($from) . ' - ' . day($lastDayAfterThreeMonth);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell($countAccDayLeft);
                        }
                        if ($in == false) {
                            $cell = new html_table_cell();
                            $cell->attributes['data-search'] = day($from) . ' - ' . day($lastDayAfterThreeMonth);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell();
                        }
                        $cell->attributes['data-order'] = $countAccDayLeft;
                        $row->cells[] = $cell;
                        $rightrows[$key] = $row;
                        $in = false;
                    }
                } elseif ($firstDayAfterTwoMonth <= $from) {

                } else {
                    $row = new html_table_row();
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                    for ($i = 0; $i < $cellLeft; $i++) {
                        $cell = new html_table_cell();
                        $row->cells[] = $cell;
                    }
                    $cell = new html_table_cell('N/A');
                    $row->cells[] = $cell;
                    for ($i = 0; $i < $cellRight + 2; $i++) {
                        $cell = new html_table_cell();
                        $row->cells[] = $cell;
                    }
                    $cell = new html_table_cell(day($from) . ' - ' . day($lastDayAfterThreeMonth));
                    $cell->attributes['data-search'] = day($from) . ' - ' . day($lastDayAfterThreeMonth);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($countAccDayLeft);
                    $cell->attributes['data-order'] = $countAccDayLeft;
                    $row->cells[] = $cell;
                    $rightrows['s4'] = $row;
                }
                $stt = 0;
                foreach ($rightrows as $key => $row) {
                    if (!array_key_exists($key, $leftrows)) {
                        $row->cells = array_merge(array(), $row->cells);
                    } else {
                        $row->cells = array_merge($leftrows[$key]->cells, $row->cells);
                    }
                    if ($stt != 0) {
                        $row->cells[0]->text = $stt;
                    }
                    $stt++;
                    $table->data[] = $row;
                }
                $headrows = array_shift($table->data);
                $table->head = $headrows->cells;
                $table->attributes = array('class' => 'reportenrollment-table', 'border' => '1');
                $table->align[0] = 'center';
                $table->align[$soCot + 3] = 'center';
                echo html_writer::tag('h2', html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $id, $course->fullname));
                echo html_writer::table($table);
                echo '</br>';
            }
        }
    }
}
echo $OUTPUT->footer();