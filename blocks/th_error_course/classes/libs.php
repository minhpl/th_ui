<?php

    namespace block_th_error_course; 

    class libs
    {

        public function get_list_courses() {
            global $DB;
            $listcourses = [];
            $sql = "SELECT * FROM {course} WHERE NOT id = 1";
            $courses = $DB->get_records_sql($sql);
            if (!empty($courses)) {
                foreach ($courses as $id => $course) {
                    $listcourses[$id] = $course->fullname.','.$course->shortname.','.$course->idnumber;
                }
            }
            return $listcourses;
        }


        public function get_list_courses_new($fullname) {
            global $DB;
            $listcourses = [];
            $max = count($fullname);
            for ($i=0;$i<$max;++$i) {
                $sql = "SELECT * FROM {course} WHERE fullname REGEXP BINARY '^$fullname[$i]$'";
                $courses = $DB->get_records_sql($sql);
                if (!empty($courses) && count($courses) < 2) {
                    foreach ($courses as $id => $course) {
                        $listcourses[$id] = $course->fullname;
                    }
                } else {
                    $sql1 = "SELECT startdate FROM {course} WHERE fullname REGEXP BINARY '^$fullname[$i]$'";
                    $startdate = $DB->get_records_sql($sql1);
                    $startdate_max = max($startdate);
                    $sql3 = "SELECT id FROM {course} WHERE fullname REGEXP BINARY '^$fullname[$i]$' AND startdate = '$startdate_max->startdate'";
                    $id = $DB->get_field_sql($sql3);
                    $listcourses[$id] = $fullname[$i];
                }
            }  
            return $listcourses;
        }

        public function get_fullname($course_id) {
            global $DB;
            $sql = "SELECT fullname FROM {course} WHERE id = $course_id";
            $fullname = $DB->get_field_sql($sql);
            return $fullname;
        }

        public function get_shortname($course_id) {
            global $DB;
            $sql = "SELECT shortname FROM {course} WHERE id = $course_id";
            $shortname = $DB->get_field_sql($sql);
            return $shortname;
        }

        public function get_category($course_id) {
            global $DB;
            $sql = "SELECT category FROM {course} WHERE id = $course_id";
            $category = $DB->get_field_sql($sql);
            return $category;
        }

        public function get_startdate($str){
            global $DB;
            $list_startdate = [];
            $sql = "SELECT startdate FROM {course} WHERE fullname LIKE BINARY '$str%' AND fullname NOT LIKE BINARY '% - mẫu' AND fullname NOT LIKE BINARY '% - Mẫu' AND fullname NOT LIKE BINARY '% - MẪU' ORDER BY startdate DESC";
            $startdate = $DB->get_records_sql($sql);
            foreach ($startdate as $k => $course) {
                $list_startdate[] = $course->startdate;
            }

            return $list_startdate;
        }

        public function get_id($fullname) {
            global $DB;
            $sql = "SELECT * FROM {course} WHERE fullname REGEXP BINARY '^$fullname$'";
            $courses = $DB->get_records_sql($sql);
            if (count($courses) < 2) {
                $sql1 = "SELECT id FROM {course} WHERE fullname REGEXP BINARY '^$fullname$'";
                $id = $DB->get_field_sql($sql1);
            } else {
                $sql1 = "SELECT startdate FROM {course} WHERE fullname REGEXP BINARY '^$fullname$'";
                $startdate = $DB->get_records_sql($sql1);
                $startdate_max = max($startdate);
                $sql3 = "SELECT id FROM {course} WHERE fullname REGEXP BINARY '^$fullname$' AND startdate = '$startdate_max->startdate'";
                $id = $DB->get_field_sql($sql3);
            }
            
            return $id;
        }
    }
?>