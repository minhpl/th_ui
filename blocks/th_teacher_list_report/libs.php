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

?>