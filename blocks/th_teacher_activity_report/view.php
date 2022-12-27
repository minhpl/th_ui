<?php
require '../../config.php';
require_once $CFG->dirroot . '/blocks/th_teacher_activity_report/lib.php';
require_once $CFG->dirroot . '/blocks/th_teacher_activity_report/th_teacher_activity_report_form.php';

global $DB, $CFG, $COURSE;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
    print_error('invalidcourse', 'blocks_th_register', $COURSE->id);
}
require_login($COURSE->id);
require_capability('block/th_teacher_activity_report:view', context_course::instance($COURSE->id));

$baseurl = new moodle_url('/blocks/th_teacher_activity_report/view.php');
$pluginname = get_string('pluginname', 'block_th_teacher_activity_report');

$PAGE->set_url($baseurl);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($pluginname);
$PAGE->set_heading($pluginname);
$PAGE->navbar->add($pluginname, $baseurl);
$lang = current_language();
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.table', "Báo cáo Thông kê hoạt động của Giảng viên", $lang));

$mform = new th_teacher_activity_report_form();

if ($data = $mform->get_data()) {

    $option = $data->show_option;
    $startdate = $data->start_date;
    $enddate = $data->end_date + 24 * 60 * 60 - 1;

    $th_teacher_activity = new th_teacher_activity();

    $role_teacher = $DB->get_record('role', array('shortname' => 'editingteacher'));

    if ($option == 0 or $option == 1) {

        if ($option == 0) {
            // select course start date
            $course_start_date = $data->course_start_date;
            $courses = $th_teacher_activity->get_course($course_start_date);

        } else if ($option == 1) {
            // select course
            $list_course = $data->list_course;
            $courses = array();
            foreach ($list_course as $key => $courseid) {
                $courses[$courseid] = $DB->get_record('course', array('id' => $courseid));
            }
        }

        $records = array();

        foreach ($courses as $key => $course) {
            $context = context_course::instance($course->id);
            $teachers = get_role_users($role_teacher->id, $context);

            $record = new stdClass();

            $record->name_teacher = "";
            $record->role_name = "";
            $record->start_date_course = date("d/m/Y", $course->startdate);
            $link_course = $CFG->wwwroot . '/course/view.php?id=' . $course->id;
            $record->fullname_course = html_writer::link($link_course, $course->fullname);
            $record->last_access_course = "";
            $record->count_access_course = "";
            $record->count_forum_news = "";
            $record->count_forum_general = "";
            $record->count_questions = "";
            $record->count_answers = "";

            if ($teachers) {
                foreach ($teachers as $key => $teacher) {
                    $record->role_name = "Giảng viên";
                    $link_user = $CFG->wwwroot . "/user/view.php?id=" . $teacher->id;
                    $record->name_teacher .= html_writer::link($link_user, $teacher->firstname . ' ' . $teacher->lastname . '<br>');
                    $record->count_access_course .= $th_teacher_activity->get_count_access_course($teacher->id, $course->id, $startdate, $enddate) . '<br>';
                    $record->last_access_course .= $th_teacher_activity->get_last_access_course($teacher->id, $course->id, $startdate, $enddate) . '<br>';

                    $record->count_forum_news .= $th_teacher_activity->get_count_forum_posts($teacher->id, $course->id, 'news', $startdate, $enddate) . '<br>';

                    $record->count_forum_general .= $th_teacher_activity->get_count_forum_posts($teacher->id, $course->id, 'general', $startdate, $enddate) . '<br>';
                    $record->count_questions .= $th_teacher_activity->get_questions_qaa($course->id, $startdate, $enddate) . '<br>';
                    $record->count_answers .= $th_teacher_activity->get_answers_qaa($teacher->id, $course->id, $startdate, $enddate) . '<br>';
                }
            } else {
                $record->name_teacher = "N/A";
                $record->role_name = "N/A";
                $record->last_access_course = "N/A";
                $record->count_access_course = "N/A";
                $record->count_forum_news = "N/A";
                $record->count_forum_general = "N/A";
                $record->count_questions = "N/A";
                $record->count_answers = "N/A";
            }

            $records[$course->id] = $record;
        }
    }

    if ($option == 2) {
        //select teacher
        $list_teacher = $data->list_teacher;

        $sql = "SELECT DISTINCT c.*
                    FROM {course} c,
                        {enrol} e,
                        {user_enrolments} ue,
                        {role_assignments} ra,
                        {role} r,
                        {user} u
                    WHERE c.id = e.courseid AND e.status = 0 AND e.id = ue.enrolid
                        AND ue.status = 0 AND ue.userid = u.id AND u.deleted = 0
                        AND u.suspended = 0 AND u.id = ra.userid AND ra.roleid = r.id
                        AND r.shortname LIKE 'editingteacher' AND u.id = :userid";

        $records = array();
        foreach ($list_teacher as $key => $userid) {

            $teacher = $DB->get_record('user', array('id' => $userid));
            $courses = $DB->get_records_sql($sql, array('userid' => $userid));

            foreach ($courses as $key => $course) {

                $record = new stdClass();

                $link_user = $CFG->wwwroot . "/user/view.php?id=" . $teacher->id;
                $record->name_teacher = html_writer::link($link_user, $teacher->firstname . ' ' . $teacher->lastname);

                $record->role_name = "Giảng viên";
                $record->start_date_course = date("d/m/Y", $course->startdate);

                $link_course = $CFG->wwwroot . '/course/view.php?id=' . $course->id;
                $record->fullname_course = html_writer::link($link_course, $course->fullname);

                $record->count_access_course = $th_teacher_activity->get_count_access_course($teacher->id, $course->id, $startdate, $enddate);
                $record->last_access_course = $th_teacher_activity->get_last_access_course($teacher->id, $course->id, $startdate, $enddate);

                $record->count_forum_news = $th_teacher_activity->get_count_forum_posts($teacher->id, $course->id, 'news', $startdate, $enddate);

                $record->count_forum_general = $th_teacher_activity->get_count_forum_posts($teacher->id, $course->id, 'general', $startdate, $enddate);

                $record->count_answers = $th_teacher_activity->get_answers_qaa($teacher->id, $course->id, $startdate, $enddate);

                $record->count_questions = $th_teacher_activity->get_questions_qaa($course->id, $startdate, $enddate);

                $records[$course->id] = $record;
            }
        }
    }

    if ($records) {
        $table = new html_table();
        $table->attributes = array('class' => 'table', 'border' => '1');
        $table->head = array(
            get_string('fullname', 'block_th_teacher_activity_report'),
            get_string('role'),
            get_string('course_start_date', 'block_th_teacher_activity_report'),
            get_string('course', 'block_th_teacher_activity_report'),
            get_string('count_access_course', 'block_th_teacher_activity_report'),
            get_string('last_login', 'block_th_teacher_activity_report'),
            get_string('count_post_general', 'block_th_teacher_activity_report'),
            get_string('count_post_news', 'block_th_teacher_activity_report'),
            get_string('count_answer_qaa', 'block_th_teacher_activity_report'),
            get_string('count_question_qaa', 'block_th_teacher_activity_report'));

        foreach ($records as $key => $record) {

            $row = new html_table_row();
            $cell = new html_table_cell($record->name_teacher);
            $cell->attributes['data-search'] = $record->name_teacher;
            $row->cells[] = $cell;

            $cell = new html_table_cell($record->role_name);
            $cell->attributes['data-search'] = $record->role_name;
            $row->cells[] = $cell;

            $cell = new html_table_cell($record->start_date_course);
            $cell->attributes['data-search'] = $record->start_date_course;
            $row->cells[] = $cell;

            $cell = new html_table_cell($record->fullname_course);
            $cell->attributes['data-search'] = $record->fullname_course;
            $row->cells[] = $cell;

            $cell = new html_table_cell($record->count_access_course);
            $cell->attributes['data-search'] = $record->count_access_course;
            $row->cells[] = $cell;

            $cell = new html_table_cell($record->last_access_course);
            $cell->attributes['data-search'] = $record->last_access_course;
            $row->cells[] = $cell;

            $cell = new html_table_cell($record->count_forum_general);
            $cell->attributes['data-search'] = $record->count_forum_general;
            $row->cells[] = $cell;

            $cell = new html_table_cell($record->count_forum_news);
            $cell->attributes['data-search'] = $record->count_forum_news;
            $row->cells[] = $cell;

            $cell = new html_table_cell($record->count_answers);
            $cell->attributes['data-search'] = $record->count_answers;
            $row->cells[] = $cell;

            $cell = new html_table_cell($record->count_questions);
            $cell->attributes['data-search'] = $record->count_questions;
            $row->cells[] = $cell;

            $table->data[] = $row;
        }
        $html = html_writer::table($table);
    } else {
        $html = "<h2>Không có dữ liệu!</h2>";
    }

} else if ($mform->is_cancelled()) {

    redirect($baseurl);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'block_th_teacher_activity_report'));
echo $mform->display();

if (isset($html)) {
    echo $html;
}
echo $OUTPUT->footer();

?>
<script type="text/javascript">
    $(document).ready(function() {
        $('input[type=radio][name=show_option]').change(function() {
            $('#fitem_id_list_course .col-form-label label').removeAttr('hidden');
            $('#fitem_id_list_teacher .col-form-label label').removeAttr('hidden');
            $('#id_course_start_date_label').removeAttr('hidden');
        });
    });
</script>