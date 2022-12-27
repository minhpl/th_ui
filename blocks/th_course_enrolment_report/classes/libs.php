<?php

    namespace block_th_course_enrolment_report; 

    class libs
    {


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

        public function get_list_id_students() {
            global $DB;
            $liststudent = [];
            $sql = "SELECT * FROM {user} WHERE NOT deleted = 1 AND NOT suspended = 1 AND NOT id =1";
            $students = $DB->get_records_sql($sql);
            if (!empty($students)) {
                foreach ($students as $id => $student) {
                    $liststudent[$id] = $student->id;
                }
            }
            return $liststudent;
        }

        public function get_list_courses() {
            global $DB;
            $listcourses = [];
            $sql = "SELECT * FROM {course} WHERE visible = 1 AND NOT id = 1 ";
            $courses = $DB->get_records_sql($sql);
            if (!empty($courses)) {
                foreach ($courses as $id => $course) {
                    $listcourses[$id] = $course->fullname;
                }
            }
            return $listcourses;
        }

        public function get_list_id_courses() {
            global $DB;
            $listcourses = [];
            $sql = "SELECT * FROM {course} WHERE visible = 1 AND NOT id = 1 ";
            $courses = $DB->get_records_sql($sql);
            if (!empty($courses)) {
                foreach ($courses as $id => $course) {
                    $listcourses[$id] = $course->id;
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

        public function get_full_name($user_id) {
            global $DB;
            $sql = "SELECT firstname FROM {user} WHERE id = $user_id";
            $firstname = $DB->get_field_sql($sql);
            $sql1 = "SELECT lastname FROM {user} WHERE id = $user_id";
            $lastname = $DB->get_field_sql($sql1);
            $fullname = $firstname.' '.$lastname;
            return $fullname;
        }

        public function get_user_name($user_id) {
            global $DB;
            $sql = "SELECT username FROM {user} WHERE id = $user_id";
            $username = $DB->get_field_sql($sql);
            return $username;
        }

        public function get_role_name($role_id) {
            global $DB;
            $sql = "SELECT shortname FROM {role} WHERE id = $role_id";
            $rolename = $DB->get_field_sql($sql);
            return $rolename;
        }

    }
?>