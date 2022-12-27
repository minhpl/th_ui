<?php
require '../../config.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/blocks/th_course_access_report/th_course_access_report_form.php';
require_once $CFG->dirroot . '/blocks/th_course_access_report/classes/lib.php';

global $DB, $CFG, $COURSE;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
    print_error('invalidcourse', 'block_th_course_access_report', $COURSE->id);
}
require_login($COURSE->id);
require_capability('block/th_course_access_report:view', context_course::instance($COURSE->id));

$title = get_string('pluginname', 'block_th_course_access_report');
$PAGE->set_url(new moodle_url('/blocks/th_course_access_report/view.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($title);
$PAGE->set_title($title);
$editurl = new moodle_url('/blocks/th_course_access_report/view.php');
$settingsnode = $PAGE->settingsnav->add($title, $editurl);
$settingsnode->make_active();

$th_course_access_report_form = new th_course_access_report_form();

if ($th_course_access_report_form->is_cancelled()) {
    // Cancelled forms redirect to the course main page.
    $courseurl = new moodle_url('/my');
    redirect($courseurl);
} else if ($fromform = $th_course_access_report_form->get_data()) {

    $lib = new th_course_access_report\lib();

    $from_date = $fromform->from_date;
    $to_date = $fromform->to_date + strtotime("+23 hours 59 minutes 59 seconds", 0);
    $courseid_arr = $fromform->courseid;
    $userid_arr = $fromform->userid;
    $show_option = $fromform->show_option;

    //echo $from_date . "_" . $to_date;

    if (empty($courseid_arr)) {
        $courses = $lib->get_courseid();
    } else {
        $courses = array();
        foreach ($courseid_arr as $key => $courseid) {
            $courses[$courseid] = $courseid;
        }
    }

    if (empty($userid_arr)) {
        $users = $lib->get_userid();
    } else {
        $users = array();
        foreach ($userid_arr as $key => $userid) {
            $users[$userid] = $userid;
        }
    }

    foreach ($courses as $key => $courseid) {
        foreach ($users as $key => $userid) {
            $sql = "SELECT DISTINCT thr.userid
			FROM {th_registeredcourses} thr
			JOIN {user} u ON thr.userid=u.id
			JOIN {course} c ON thr.courseid=c.id
			WHERE thr.timeactivated = 0 AND u.deleted=0 AND u.suspended=0 AND c.id = :courseid AND u.id=:userid";

            $users_register = $DB->get_records_sql($sql, ['courseid' => $courseid, 'userid' => $userid]);

            if (!empty($users_register)) {
                foreach ($users_register as $key => $user_register) {
                    $users[$key] = $user_register->userid;
                }
            }
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
    $rightrows = [];

    $headrows = new html_table_row();
    $cell = new html_table_cell(get_string('course'));
    $cell->attributes['class'] = 'cell headingcell';
    $cell->header = true;
    $headrows->cells[] = $cell;
    $cell = new html_table_cell(get_string('shortname'));
    $cell->attributes['class'] = 'cell headingcell';
    $cell->header = true;
    $headrows->cells[] = $cell;
    $cell = new html_table_cell(get_string('role', 'block_th_course_access_report'));
    $cell->attributes['class'] = 'cell headingcell';
    $cell->header = true;
    $headrows->cells[] = $cell;
    $cell = new html_table_cell(get_string('enrolment_date', 'block_th_course_access_report'));
    $cell->attributes['class'] = 'cell headingcell';
    $cell->header = true;
    $headrows->cells[] = $cell;
    $cell = new html_table_cell(get_string('enrolment_activation_date', 'block_th_course_access_report'));
    $cell->attributes['class'] = 'cell headingcell';
    $cell->header = true;
    $headrows->cells[] = $cell;
    $cell = new html_table_cell(get_string('enrolment_expire_date', 'block_th_course_access_report'));
    $cell->attributes['class'] = 'cell headingcell';
    $cell->header = true;
    $headrows->cells[] = $cell;
    $cell = new html_table_cell(get_string('enrolment_status', 'block_th_course_access_report'));
    $cell->attributes['class'] = 'cell headingcell';
    $cell->header = true;
    $headrows->cells[] = $cell;
    $cell = new html_table_cell(get_string('access_count', 'block_th_course_access_report'));
    $cell->attributes['class'] = 'cell headingcell';
    $cell->header = true;
    $headrows->cells[] = $cell;

    $rightrows[] = $headrows;
    list($leftrows, $rows_ex_left) = get_left_rows($users, $user_arr);

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
    if (!empty($courses)) {

        foreach ($courses as $key => $courseid) {

            $context = context_course::instance($courseid);

            if ($show_option == 1) {

                $sql = "SELECT DISTINCT m.*, rc.timecreated AS registered FROM
				(SELECT DISTINCT u.id,c.id AS courseid,c.fullname,c.shortname,ue.timecreated,ue.timeend,ue.status
				FROM {user} u
				JOIN {user_enrolments} ue ON ue.userid = u.id
				JOIN {enrol} e ON e.id = ue.enrolid
				JOIN {role_assignments} ra ON ra.userid = u.id
				JOIN {context} ct ON ct.id = ra.contextid AND ct.contextlevel = 50
				JOIN {course} c ON c.id = ct.instanceid AND e.courseid = c.id
				JOIN {role} r ON r.id = ra.roleid
				WHERE c.visible=1 AND e.status = 0 AND u.suspended = 0 AND u.deleted = 0 AND c.id = :courseid AND ue.status=0 AND (r.id=5 OR r.id=3)
				   AND (ue.timeend>:to_date OR ue.timeend=0)) m
				LEFT JOIN
				   {th_registeredcourses} rc ON m.id=rc.userid AND m.courseid=rc.courseid
				GROUP BY m.id
                HAVING MAX(m.timecreated)";
            }
            if ($show_option == 2) {
                $sql = "SELECT DISTINCT m.*, rc.timecreated AS registered FROM
				(SELECT DISTINCT u.id,c.id AS courseid,c.fullname,c.shortname,ue.timecreated,ue.timeend,ue.status
				FROM {user} u
				JOIN {user_enrolments} ue ON ue.userid = u.id
				JOIN {enrol} e ON e.id = ue.enrolid
				JOIN {role_assignments} ra ON ra.userid = u.id
				JOIN {context} ct ON ct.id = ra.contextid AND ct.contextlevel = 50
				JOIN {course} c ON c.id = ct.instanceid AND e.courseid = c.id
				JOIN {role} r ON r.id = ra.roleid
				WHERE (c.visible=1 AND e.status = 0 AND u.suspended = 0 AND u.deleted = 0 AND c.id = :courseid)
				   AND (ue.timeend>:from_date AND ue.timeend<=:to_date OR ue.status=1)) m
				LEFT JOIN
				   {th_registeredcourses} rc ON m.id=rc.userid AND m.courseid=rc.courseid
				GROUP BY m.id
                HAVING MAX(m.timecreated)";
            }
            $params = array('courseid' => $courseid, 'from_date' => $from_date, 'to_date' => $to_date);
            $temp = $DB->get_records_sql($sql, $params);

            //lay nhung ban ghi co key trung trong array
            $records = array_intersect_key($temp, $users);

            if ($show_option == 1) {
                foreach ($users as $key => $userid) {
                    $sql = "SELECT DISTINCT row_number() OVER() as stt,thr.userid,thr.courseid,thr.timecreated AS registered
						FROM {th_registeredcourses} thr
						JOIN {user} u ON thr.userid=u.id
						JOIN {course} c ON thr.courseid=c.id
					WHERE thr.timeactivated = 0 AND u.deleted=0 AND u.suspended=0 AND c.id = :courseid AND u.id=:userid";

                    $users_register = $DB->get_records_sql($sql, ['courseid' => $courseid, 'userid' => $userid]);

                    if (!empty($users_register)) {
                        foreach ($users_register as $key => $user_register) {
                            if (!array_key_exists($user_register->userid, $records)) {
                                $records[$user_register->userid] = $user_register;
                            }
                        }
                    }
                }
            }

            if (!empty($records)) {
                foreach ($records as $userid => $record) {

                    if (is_enrolled($context, $userid)) {

                        $link_course = $CFG->wwwroot . '/course/view.php?id=' . $courseid;
                        $course_fullname = html_writer::link($link_course, $record->fullname);
                        $course_shortname = $record->shortname;

                        $timecreated = $record->registered;
                        if (empty($timecreated)) {
                            $enrolment_date = "N/A";
                        } else {
                            $enrolment_date = date('d/m/Y', $timecreated);
                        }

                        $enrolment_activation_date = $record->timecreated;
                        if (empty($enrolment_activation_date)) {
                            $enrolment_activation_date = "N/A";
                        } else {
                            $enrolment_activation_date = date('d/m/Y', $enrolment_activation_date);
                        }

                        $timeend = $record->timeend;
                        if (!empty($timeend)) {
                            $expire_date = date('d/m/Y', $timeend);
                        } else {
                            $expire_date = get_string('infinite', 'block_th_course_access_report');
                        }

                        $status = $record->status;
                        if ($status == 1) {
                            $status = get_string('suspend', 'block_th_course_access_report');
                        } elseif ($status == 0 && $timeend > idate("U")) {
                            $status = get_string('active');
                        } elseif ($status == 0 && $timeend < idate("U")) {
                            $status = get_string('inactive', 'block_th_course_access_report');
                            if ($timeend == 0) {
                                $status = get_string('active');
                            }
                        }

                        $access_course = $lib->get_access_course($userid, $courseid, $from_date, $to_date);

                        $roles = get_user_roles($context, $userid, true);
                        $role = key($roles);
                        $rolename = $roles[$role]->shortname;
                        if ($rolename == "student") {
                            $rolename = "Học viên";
                        } else {
                            $rolename = "Giảng viên";
                        }

                    } else {
                        $rolename = "N/A";
                        $link_course = $CFG->wwwroot . '/course/view.php?id=' . $courseid;
                        $course_fullname = html_writer::link($link_course, $lib->get_fullname_course($record->courseid));
                        $course_shortname = $lib->get_shortname_course($record->courseid);

                        $timecreated = $record->registered;
                        if (empty($timecreated)) {
                            $enrolment_date = "N/A";
                        } else {
                            $enrolment_date = date('d/m/Y', $timecreated);
                        }

                        $enrolment_activation_date = "N/A";

                        $expire_date = "N/A";

                        $status = "N/A";

                        $access_course = "N/A";
                    }

                    $row = new html_table_row();
                    $cell = new html_table_cell($course_fullname);
                    $cell->attributes['data-search'] = $course_fullname;
                    $row->cells[] = $cell;

                    $cell = new html_table_cell($course_shortname);
                    $cell->attributes['data-search'] = $course_shortname;
                    $row->cells[] = $cell;

                    $cell = new html_table_cell($rolename);
                    $cell->attributes['data-search'] = $rolename;
                    $row->cells[] = $cell;

                    $cell = new html_table_cell($enrolment_date);
                    $cell->attributes['data-order'] = $timecreated;
                    $cell->attributes['data-search'] = $enrolment_date;
                    $row->cells[] = $cell;

                    $cell = new html_table_cell($enrolment_activation_date);
                    $cell->attributes['data-search'] = $enrolment_activation_date;
                    $row->cells[] = $cell;

                    $cell = new html_table_cell($expire_date);
                    $cell->attributes['data-search'] = $expire_date;
                    $row->cells[] = $cell;

                    $cell = new html_table_cell($status);
                    $cell->attributes['data-search'] = $status;
                    $row->cells[] = $cell;

                    $cell = new html_table_cell($access_course);
                    $cell->attributes['data-search'] = $access_course;
                    $row->cells[] = $cell;

                    $rightrows[$userid . "_" . $courseid] = $row;
                }
            }
        }

        $stt = 0;
        foreach ($rightrows as $key => $row) {
            $userid = explode("_", $key);
            $row->cells = array_merge($leftrows[$userid[0]]->cells, $row->cells);
        }

        foreach ($rightrows as $key => $row) {
            if ($stt != 0) {
                $c = new html_table_cell($stt);
                $row->cells[0] = $c;
            }
            $stt++;
            $table->data[] = $row;
        }
        $headrows = array_shift($table->data);
        $table->head = $headrows->cells;
        $table->attributes = array('class' => 'table', 'border' => '1');
        $table->align[0] = 'center';
        $table->align[$soCot + 7] = 'center';
    }

    $html = html_writer::table($table);
    $lang = current_language();
    $PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.table', "Báo cáo Truy cập khóa học", $lang));
    echo $OUTPUT->header();
    echo $OUTPUT->heading($title);
    $th_course_access_report_form->display();
    echo $html;
    echo $OUTPUT->footer();
} else {
    echo $OUTPUT->header();
    echo $OUTPUT->heading($title);
    $th_course_access_report_form->display();
    echo $OUTPUT->footer();
}