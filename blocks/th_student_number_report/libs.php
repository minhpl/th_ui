<?php

function ds_course() {
    global $DB;
    $listcourses = [];
    $sql = "SELECT c.* FROM {course} as c, {course_categories} as ca WHERE NOT c.id = 1 AND NOT c.category = 1 AND c.visible <> 0 AND c.fullname NOT LIKE '% - mẫu' AND ca.id = c.category AND ca.visible = 1";
    $courses = $DB->get_records_sql($sql);
    
    if (!empty($courses)) {
        foreach ($courses as $id => $course) {
            $listcourses[$course->fullname] = $course->shortname;
        }
    }
    return $listcourses;
}

function ds_nganh() {
	global $DB;
    $listnganh = [];
    $sql = "SELECT * FROM {course_categories} WHERE NOT id = 1 AND parent = 0 AND visible = 1";
    $ds_nganh = $DB->get_records_sql($sql);
    if (!empty($ds_nganh)) {
        foreach ($ds_nganh as $id => $nganh) {
            $listnganh[$id] = $nganh->name;
        }
    }
    return $listnganh;
}

function ds_nganh2() {
    global $DB;
    $listnganh = [];
    $sql = "SELECT * FROM {course_categories} WHERE NOT id = 1 AND parent = 0 AND visible = 1";
    $ds_nganh = $DB->get_records_sql($sql);
    if (!empty($ds_nganh)) {
        foreach ($ds_nganh as $id => $nganh) {
            $listnganh[] = $id;
        }
    }
    return $listnganh;
}

function list_student_of_course($courseid) {
    global $DB;

    $roleid = $DB->get_field_sql("SELECT id FROM {role} WHERE shortname = 'student'");
    $count = $DB->get_field_sql("SELECT COUNT(ue.userid) FROM {enrol} as e, {user_enrolments} as ue, {user} as u, {context} as c, {role_assignments} as ra WHERE ue.status = 0 AND e.courseid = '$courseid' AND e.enrol = 'manual' AND e.id = ue.enrolid AND u.id = ue.userid AND c.instanceid = '$courseid' AND c.contextlevel = '50' AND c.id = ra.contextid AND ra.userid = u.id AND ra.roleid = '$roleid'");

    return $count;
}

?>