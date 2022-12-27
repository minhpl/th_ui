<?php
require '../../config.php';
require_once $CFG->dirroot . '/lib/classes/notification.php';
require_once $CFG->dirroot . '/blocks/th_manage_activatecourses/th_manage_activatecourses_form.php';
require_once $CFG->dirroot . '/blocks/th_manage_activatecourses/classes/lib.php';

global $DB, $CFG, $COURSE;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
    print_error('invalidcourse', 'block_th_manage_activatecourses', $COURSE->id);
}
require_login($COURSE->id);
require_capability('block/th_manage_activatecourses:view', context_course::instance($COURSE->id));

$id = optional_param('id', 0, PARAM_INT);
$contextid = optional_param('contextid', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

if ($id) {
    $record = $DB->get_record('th_registeredcourses', array('id' => $id), '*', MUST_EXIST);
} else {
    $record = new stdClass();
    $record->id = 0;
    $record->userid = '';
    $record->courseid = '';
    $record->timecreated = '';
}

if ($returnurl) {
    $returnurl = new moodle_url($returnurl);
} else {
    $returnurl = new moodle_url('/blocks/th_manage_activatecourses/view.php');
}
$baseurl = new moodle_url('/blocks/th_manage_activatecourses/edit.php');
$PAGE->set_url($baseurl);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        th_manage_activatecourses::unregister_courses($id);
        redirect($CFG->wwwroot . '/blocks/th_manage_activatecourses/view.php', get_string('delete_successful', 'block_th_manage_activatecourses'), null, \core\output\notification::NOTIFY_SUCCESS);
    }
    $strheading = get_string('delete', 'block_th_manage_activatecourses');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    $PAGE->set_heading($COURSE->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $record = $DB->get_record('th_registeredcourses', array('id' => $id));
    $yesurl = new moodle_url('/blocks/th_manage_activatecourses/edit.php', array('id' => $id, 'confirm' => 1, 'delete' => 1, 'sesskey' => sesskey()));
    $user = html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $record->id, get_userid_fullname($record->userid));
    $course = html_writer::link($CFG->wwwroot . '/user/index.php?id=' . $record->courseid, th_manage_activatecourses::get_fullname_course($record->courseid));
    $message = get_string('confirm', 'block_th_manage_activatecourses', array('user' => $user, 'course' => $course));
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}

if ($record->id) {
    // Edit existing.
    $strheading = get_string('edit', 'block_th_manage_activatecourses');

} else {
    // Add new.
    $strheading = get_string('add', 'block_th_manage_activatecourses');
}

$PAGE->set_title($strheading);
$PAGE->set_heading($strheading);
$editurl = new moodle_url('/blocks/th_manage_activatecourses/view.php');
$PAGE->navbar->add(get_string('pluginname', 'block_th_manage_activatecourses'), $editurl);

if ($record->id) {
    $courses = array();
    $course_arr = array();
    $course_all = array();
    $courseid = array();

    $sql = "SELECT userid
					FROM {th_registeredcourses}
					WHERE id = $id";
    $userid = $DB->get_field_sql($sql);

    $sql = "SELECT courseid
					FROM {th_registeredcourses}
					WHERE userid = :userid AND timeactivated = 0";
    $params = array('userid' => $userid);

    $courseid_user_register = $DB->get_records_sql($sql, $params);
    if ($courseid_user_register) {
        foreach ($courseid_user_register as $cid => $value) {
            $courses[$cid] = $cid;
        }
    }

    $courseid_user_enrol = enrol_get_users_courses($userid);
    if ($courseid_user_enrol) {
        foreach ($courseid_user_enrol as $cid => $value) {
            $courses[$cid] = $cid;
        }
    }

    $sql = "SELECT id FROM {course} WHERE summaryformat=1 AND visible =1";
    $records = $DB->get_records_sql($sql);

    if ($records) {
        foreach ($records as $cid => $value) {
            $course_all[$cid] = $cid;
        }
    }

    $courses = array_diff($course_all, $courses);

    $add = $record->courseid;
    $courses[$add] = $add;

    if ($courses) {
        foreach ($courses as $cid => $value) {
            $course_arr[$cid] = $DB->get_record('course', array('id' => $cid), 'fullname,shortname');
        }
    }

    if ($course_arr) {
        foreach ($course_arr as $cid => $course) {
            $courseid[$cid] = $course->fullname . ', ' . $course->shortname;
        }
    }
    $editform = new th_manage_activatecourses_form(null, ['courseid' => $courseid]);

} else {
    $editform = new th_manage_activatecourses_form();
}
$editform->set_data(array('userid' => $record->userid, 'courseid' => $record->courseid, 'id' => $id));

$table = "";
$userid = 0;
if ($editform->is_cancelled()) {
    redirect($returnurl);

} else if ($data = $editform->get_data()) {

    $dataobject = new stdClass();
    $dataobject->userid = $data->userid;

    if ($id) {
        $dataobject->id = $id;
        $dataobject->courseid = trim($data->courseid);
        $check = th_manage_activatecourses::check_register_courses($dataobject);

        if (!$check) {
            th_manage_activatecourses::update_user_register_courses($dataobject);
            $courseid = trim($data->courseid);
            $userid = $data->userid;
            $coursefullname = $DB->get_record('course', array('id' => $courseid), 'fullname')->fullname;
            $user = $DB->get_record('user', array('id' => $userid));
            $userfullname = fullname($user);
            $linkactive = html_writer::link($CFG->wwwroot . '/blocks/th_activatecourses/activate.php?id=' . $courseid, 'ĐÂY');

            $userfrom = \core_user::get_support_user();
            $title = get_string('title', 'block_th_manage_activatecourses');
            $content = get_string('body', 'block_th_manage_activatecourses', array('userfullname' => $userfullname, 'coursefullname' => $coursefullname, 'linkactive' => $linkactive));
            email_to_user($user, $userfrom, $title, $content);
            redirect($CFG->wwwroot . '/blocks/th_manage_activatecourses/view.php', get_string('edit_success', 'block_th_manage_activatecourses'), null, \core\output\notification::NOTIFY_SUCCESS);
        }

        redirect($CFG->wwwroot . '/blocks/th_manage_activatecourses/view.php', get_string('edit_failed', 'block_th_manage_activatecourses'), null, \core\output\notification::NOTIFY_ERROR);

    } else {
        $userid = $dataobject->userid;
        $campaignid = $data->campaignid;

        $timecreated = idate("U");
        $errors = array();
        $successs = array();

        foreach ($data->courseid as $key => $courseid) {

            $dataobject->courseid = $courseid;
            $context = context_course::instance($courseid);

            if (!is_enrolled($context, $userid)) {

                $dataobject->timecreated = $timecreated;
                $check_register_courses = th_manage_activatecourses::check_register_courses($dataobject);

                // print_object($check_register_courses);
                if ($check_register_courses) {

                    $DB->insert_record('th_registeredcourses', $dataobject);

                    $coursefullname = $DB->get_record('course', array('id' => $courseid), 'fullname')->fullname;
                    $user = $DB->get_record('user', array('id' => $userid));
                    $userfullname = fullname($user);
                    $linkactive = html_writer::link($CFG->wwwroot . '/blocks/th_activatecourses/activate.php?id=' . $courseid, 'ĐÂY');

                    $userfrom = \core_user::get_support_user();
                    $title = get_string('title', 'block_th_manage_activatecourses');
                    $content = get_string('body', 'block_th_manage_activatecourses', array('userfullname' => $userfullname, 'coursefullname' => $coursefullname, 'linkactive' => $linkactive));
                    email_to_user($user, $userfrom, $title, $content);

                    $successs[] = ['courseid' => $courseid, 'userid' => $userid];
                } else {
                    $errors[] = ['courseid' => $courseid, 'userid' => $userid, 'error' => '2'];
                }
            } else {
                $errors[] = ['courseid' => $courseid, 'userid' => $userid, 'error' => '1'];
            }

            if ($campaignid) {
                $campaignid = $data->campaignid;
                $dataobject->campaignid = $campaignid;
                $check_user_campaign_course = th_manage_activatecourses::check_user_campaign_course($dataobject);

                if (!$check_user_campaign_course) {
                    $DB->insert_record('user_campaign_course', $dataobject);
                    $successs[] = ['courseid' => $courseid, 'userid' => $userid, 'campaignid' => $campaignid];
                } else {
                    $errors[] = ['courseid' => $courseid, 'userid' => $userid, 'campaignid' => $campaignid];
                }
            }
        }

        $status = "";
        if (empty($errors)) {
            $status = get_string('successful', 'block_th_manage_activatecourses');
            redirect($CFG->wwwroot . '/blocks/th_manage_activatecourses/view.php', $status, null, \core\output\notification::NOTIFY_SUCCESS);
        }

        $table = add_activatecourses_display_table($successs, $errors);
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading($strheading);

if (!$id && ($editcontrols = block_th_manage_activatecourses_controls($context, $baseurl))) {
    echo $OUTPUT->render($editcontrols);
}
if ($record->id) {
    echo html_writer::tag('h2', html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $record->userid, th_manage_activatecourses::get_userid_fullname($record->userid)));
}
echo $editform->display();
if (!$record->id) {
    echo html_writer::tag('h2', html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $userid, th_manage_activatecourses::get_userid_fullname($userid)));
    echo $table;
}
echo $OUTPUT->footer();