<?php

require_once '../../config.php';
require_once 'externallib.php';
require_once $CFG->dirroot . '/user/externallib.php';
require_once $CFG->dirroot . '/local/th_registeredcourse_api/lib.php';

global $DB, $OUTPUT, $PAGE, $COURSE;
if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
    print_error('invalidcourse', 'local_th_registeredcourse_api', $courseid);
}

require_login($COURSE->id);
require_capability('local/th_registeredcourse_api:seeallthings', context_course::instance($COURSE->id));

$pageurl = "/local/th_registeredcourse_api/view.php";
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading('Log API');
$PAGE->set_title('Log API');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

$editurl = new moodle_url('/local/th_registeredcourse_api/view.php');
$PAGE->settingsnav->add('th_registeredcourse_api', $editurl);

echo $OUTPUT->header('');

// $logs = $DB->get_records('local_registeredcourse_api', [], 'id DESC');

$sql = "SELECT a.*,o.ordercode,totalprice
        FROM {local_registeredcourse_api} a
        LEFT JOIN {th_order} o ON o.id = a.orderid
        LEFT JOIN {marketing_campaign} mc ON mc.id = a.campaignid
        ORDER BY id DESC";
// $logs = $DB->get_records_sql($sql);
// $sql = "SELECT a.*,o.ordercode
//         FROM {th_order} o
//         LEFT JOIN {local_registeredcourse_api} a ON a.orderid = o.id
//         -- RIGHT JOIN {marketing_campaign} mc ON mc.id = a.campaignid
//         ORDER BY id DESC";
$logs = $DB->get_records_sql($sql);
// JOIN {th_order_status} os ON os.orderid = a.id

$table = new html_table();
$table->attributes = array('class' => 'table', 'border' => '1');

$table->head = array('No.',
    'Mã đơn hàng',
    get_string('fullname'),
    get_string('phone'),
    get_string('email'),
    get_string('course'),
    'Chiến dịch',
    'Giá tiền',
    'Thông báo',
    'Tổng tiền',
    'Ngày tạo');
$stt = 0;

foreach ($logs as $log) {
    $stt = $stt + 1;
    $row = new html_table_row();
    $cell = new html_table_cell($stt);
    $row->cells[] = $cell;
    $cell = new html_table_cell($log->ordercode);
    $row->cells[] = $cell;

    if ($user = $DB->get_record('user', ['id' => $log->userid], 'firstname,lastname')) {
        $fullname = $user->firstname . ' ' . $user->lastname;
        $user = html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $log->userid, $fullname);
        $cell = new html_table_cell($user);
    } else {
        $cell = new html_table_cell($log->fullname);
    }

    $row->cells[] = $cell;
    $cell = new html_table_cell($log->phone);
    $row->cells[] = $cell;
    $cell = new html_table_cell($log->email);
    $row->cells[] = $cell;
    if ($fullname = $DB->get_field('course', 'fullname', ['id' => $log->courseid])) {
        $course = html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $log->courseid, $fullname);
        $cell = new html_table_cell($course);
    } else {
        $cell = new html_table_cell($log->courseid);
    }
    $row->cells[] = $cell;

    $campaignname = $DB->get_field('marketing_campaign', 'campaignname', array('id' => $log->campaignid));
    $cell = new html_table_cell($campaignname);
    $row->cells[] = $cell;

    $cell = new html_table_cell($log->courseprice);
    $row->cells[] = $cell;

    if ($log->status == 1) {
        $status = html_writer::tag('span', 'Đăng ký thành công', array('class' => 'badge badge-success'));
        $message = html_writer::tag('span', $log->message, array('class' => 'badge badge-success'));
    } else if ($log->status == 0) {
        $status = html_writer::tag('span', 'Thất bại', array('class' => 'badge badge-danger'));
        $message = html_writer::tag('span', $log->message, array('class' => 'badge badge-danger'));
    } else {
        $status = html_writer::tag('span', 'Hủy', array('class' => 'badge badge-warning'));
        $message = html_writer::tag('span', $log->message, array('class' => 'badge badge-warning'));
    }

    $cell = new html_table_cell($message);
    $row->cells[] = $cell;
    $cell = new html_table_cell($log->totalprice);
    $row->cells[] = $cell;
    $cell = new html_table_cell(date('d/m/Y H:i:s', $log->timecreated));
    $row->cells[] = $cell;
    $table->data[] = $row;
}
$lang = current_language();
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.table', "Log API", $lang));
$table->align[0] = 'center';
echo $OUTPUT->heading("Log API");
echo html_writer::table($table);

//unenrol
$user = new stdClass();
$user->userinfo = array(
    "userfullname" => "Trần Hưng Đạo",
    "phonenumber" => '123456789',
    "email" => 'id3@example.com',
);
$user->courses = array(
    [
        'courseshortname' => "ATBM",
        'campaigncode' => 'code1',
        'campaignname' => 'test1',
        'courseprice' => '10000',
    ],
    [
        'courseshortname' => "Linu",
        'campaigncode' => 'code2',
        'campaignname' => 'test2',
        'courseprice' => '11111',
    ],
);
$user->order = array(
    "ordercode" => "ordercode1",
    "ordername" => 'ordername1',
    "description" => '',
    "totalprice" => '10000000',
    "status" => 'huy',
);
$user = (array) $user;

// print_object($user);

// print_object(local_th_registeredcourse_api_external::send_mail('111', array('error' => 'hello')));
// print_object(local_th_registeredcourse_api_external::enrolcourse($user));
// print_object(local_th_registeredcourse_api_external::unenrolcourse($user));

echo $OUTPUT->footer();
