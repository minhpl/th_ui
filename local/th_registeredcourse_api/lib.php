<?php

// function th_registeredcourse_api_get_user($userid) {
//     global $DB, $CFG;

//     $data = $DB->get_record('user', ['id' => $userid], 'firstname,lastname');

//     $user = '';

//     if ($data) {
//         $fullname = $data->firstname . ' ' . $data->lastname;

//         $user = html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $userid, $fullname);
//     }

//     return $user;
// }

// function th_registeredcourse_api_get_course($courseid) {
//     global $DB, $CFG;

//     if (!$courseid) {
//         return "Khóa học không hợp lệ";
//     }

//     $data = $DB->get_record('course', ['id' => $courseid], 'fullname');

//     $fullname = $data->fullname;

//     $course = html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $courseid, $fullname);

//     return $course;
// }
// function th_registeredcourse_api_get_course_shortname($shortname) {
//     global $DB, $CFG;

//     if (!$data = $DB->get_record('course', ['shortname' => $shortname], 'id,fullname')) {
//         return "Khóa học không hợp lệ";
//     }

//     $fullname = $data->fullname;
//     $id       = $data->id;

//     $course = html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $id, $fullname);

//     return $course;
// }
