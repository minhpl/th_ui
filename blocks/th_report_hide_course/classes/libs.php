<?php


function get_list_hide_courses($startdate) {
    global $DB;
    $listcourses = [];
    $sql = "SELECT * FROM {course} WHERE NOT id = 1 AND startdate = '$startdate' ORDER BY fullname";
    $listcourses = $DB->get_records_sql($sql);
    return $listcourses;
}

?>