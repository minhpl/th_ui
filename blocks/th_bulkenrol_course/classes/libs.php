<?php

    namespace block_th_bulkenrol_course; 

    class libs
    {

        public function get_list_groups() {
            global $DB;
            $groups = [];
            $sql = "SELECT * FROM {groups}";
            $allgroups = $DB->get_records_sql($sql);
            if (count($allgroups)) {
                foreach ($allgroups as $id => $group) {
                    $groups[$id] = $group->name;
                }
            }

            return $groups;
        }

        public function get_list_students() {
            global $DB;
            $liststudent = [];
            $sql = "SELECT * FROM {user} WHERE NOT deleted = 1 AND NOT suspended = 1 AND NOT id =1";
            $students = $DB->get_records_sql($sql);
            if (!empty($students)) {
                foreach ($students as $id => $student) {
                    $liststudent[$id] = $student->firstname . ' ' . $student->lastname . ', ' . $student->username . ', ' . $student->email;
                }
            }

            return $liststudent;
        }

        public function get_list_courses() {
            global $DB;
            $listcourses = [];
            $sql = "SELECT * FROM {course} WHERE NOT id = 1";
            $courses = $DB->get_records_sql($sql);
            if (!empty($courses)) {
                foreach ($courses as $id => $course) {
                    $listcourses[$id] = $course->fullname;
                }
            }
            return $listcourses;
        }

        public function get_list_role() {
            global $DB;
            $sql = "SELECT * FROM {role} WHERE NOT id = 1";
            $role = $DB->get_records_sql($sql);
            if (!empty($role)) {
                $sql1 = "SELECT DISTINCT roleid FROM {role_context_levels} ORDER BY roleid";
                $role_id = $DB->get_records_sql($sql1);
                foreach ($role_id as $k => $role2) {
                    $list[] = $role2->roleid;
                }
                $listroles = [];
                foreach ($role as $id => $role1) {                 
                    if (in_array($id, $list) == true) {
                        $listroles[$id] = $role1->shortname;
                    }  
                }
            }
            return $listroles;
        }

        public function get_course_name($course_id) {
            global $DB;
            $sql = "SELECT fullname FROM {course} WHERE id = $course_id";
            $fullname = $DB->get_field_sql($sql);
            return $fullname;
        }

        public function get_full_name($course_id) {
            global $DB;
            $sql = "SELECT firstname FROM {user} WHERE id = $course_id";
            $firstname = $DB->get_field_sql($sql);
            $sql1 = "SELECT lastname FROM {user} WHERE id = $course_id";
            $lastname = $DB->get_field_sql($sql1);
            $fullname = $firstname.' '.$lastname;
            return $fullname;
        }

        

        public function get_startdate($str){
            global $DB;
            $list_startdate = [];
            $sql = "SELECT startdate FROM {course} WHERE fullname LIKE BINARY '$str%' ORDER BY startdate ";
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

        public function get_course_date($date){
            global $DB;
            $list_course = [];
            $sql = "SELECT id FROM {course} WHERE NOT id = 1 AND startdate = $date";
            $course = $DB->get_records_sql($sql);
            foreach ($course as $k => $course) {
                $list_course[] = $course->id;
            }
            return $list_course;
        }

    }
?>