<?php
require '../../config.php';
require_once $CFG->dirroot . '/blocks/th_quiz_grade_report/lib.php';
require_once $CFG->dirroot . '/blocks/th_quiz_grade_report/th_quiz_grade_report_form.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';

global $DB, $CFG, $COURSE;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
    print_error('invalidcourse', 'blocks_th_register', $COURSE->id);
}
require_login($COURSE->id);
require_capability('block/th_quiz_grade_report:view', context_course::instance($COURSE->id));

$baseurl = new moodle_url('/blocks/th_quiz_grade_report/view.php');
$pluginname = get_string('pluginname', 'block_th_quiz_grade_report');

$PAGE->set_url($baseurl);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($pluginname);
$PAGE->set_heading($pluginname);
$PAGE->navbar->add($pluginname, $baseurl);
$lang = current_language();
// $PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.table', "Báo cáo Điểm bài luyện tập, bài kiểm tra", $lang));

$mform = new th_quiz_grade_report_form();

if ($data = $mform->get_data()) {

    // $course_start_date = 1629565200;
    $course_start_date = $data->course_start_date;
    $makhoa = $data->makhoaid;

    $grade_option = $data->grade_op;
    $grade = $data->grade;
    $start_date_quiz = $data->start_date;
    $end_date_quiz = $data->end_date + 24 * 60 * 60 - 1;

    $th_quiz_grade = new th_quiz_grade($course_start_date, $makhoa, $grade_option, $grade, $start_date_quiz, $end_date_quiz);
    $records = $th_quiz_grade->get_quiz_grade();

    // print_object($records);
    // exit;

    if ($records) {

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

        $userid_arr = $th_quiz_grade->all_user_course;

        list($leftrows, $rows_ex_left) = get_left_rows($userid_arr, $user_arr);

        $rightrows = [];

        $table = new html_table();
        $table->attributes = array('class' => 'table', 'border' => '1');

        $max_col = 0;
        foreach ($records as $key => $record) {

            $count_record = count($record);
            if ($max_col < $count_record) {
                $max_col = $count_record;
            }
        }
        $headrows = new html_table_row();
        $head = '';
        if ($grade_option == 0) {
            $head = "Môn ";
        } else if ($grade_option == 1) {
            $head = "Môn ";
        } else {
            $head = "Môn ";
        }
        for ($i = 0; $i < $max_col; $i++) {

            $cell = new html_table_cell($head . (int) ($i + 1));
            $cell->attributes['class'] = 'cell headingcell';
            $cell->header = true;
            $headrows->cells[] = $cell;
        }

        $rightrows[] = $headrows;

        foreach ($records as $uid => $record) {

            $row = new html_table_row();
            foreach ($record as $cid => $re) {
                $t = '';
                foreach ($re as $r) {
                    $t .= html_writer::link($CFG->wwwroot . "/mod/quiz/report.php?id=" . $r->cmid, $r->name) . ', ';
                }
                // $course_name = $th_quiz_grade->get_fullname_course($cid);

                $course_name = '';

                if ($course = $DB->get_record('course', array('id' => $cid))) {

                    $link_course = $CFG->wwwroot . "/course/view.php?id=" . $cid;
                    $course_name = html_writer::link($link_course, $course->fullname);
                }

                // $a = $course_name . ': ' . rtrim($t, ", ");
                $a = $course_name . ': ' . $t;
                $itemid = $DB->get_field_sql("SELECT id FROM {grade_items} WHERE idnumber like 'ddk' AND courseid = $cid");
                $sql = "SELECT gg.finalgrade
                        FROM {grade_items} gi1
                        JOIN {grade_grades} gg ON gi1.id = gg.itemid
                        WHERE gg.userid = $uid AND gi1.courseid = $cid AND gi1.idnumber LIKE 'ddk'";
                $ddk = "Chưa Đạt điều kiện thi";

                if ($DB->get_field_sql($sql) > 0) {
                    $ddk = "Đã Đạt điều kiện thi";
                }

                $a .= html_writer::link($CFG->wwwroot . "/grade/report/singleview/index.php?id=" . $cid . "&item=grade&itemid=" . $itemid, $ddk);

                $cell = new html_table_cell($a);
                $cell->attributes['data-search'] = $course_name;
                $row->cells[] = $cell;
            }
            if (count($row->cells) < $max_col) {
                $for_number = $max_col - count($row->cells);
                for ($i = 0; $i < $for_number; $i++) {
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                }
            }
            $rightrows[$uid] = $row;
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
        $html = html_writer::table($table);
        if ($grade_option == 0) {
            $h = "nhỏ hơn $grade";
        } else if ($grade_option == 1) {
            $h = "lớn hơn $grade";
        } else {
            $h = "bằng $grade";
        }
        $PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.table', "Báo cáo Điểm có điều kiện điểm $h", $lang));
    } else {
        $html = "<h2>Không có dữ liệu!</h2>";
    }

} else if ($mform->is_cancelled()) {

    redirect($baseurl);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'block_th_quiz_grade_report'));
echo $mform->display();

if (isset($html)) {
    echo $html;
}
echo $OUTPUT->footer();