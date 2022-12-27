<?php

class th_quiz_grade {

    public $course_start_date;

    public $makhoa = array();

    public $grade_option;

    public $grade;

    public $start_date_quiz;

    public $end_date_quiz;
    public $all_user_course = array();

    public function __construct($course_start_date, $makhoa, $grade_option, $grade, $start_date_quiz, $end_date_quiz) {
        $this->course_start_date = $course_start_date;
        $this->makhoa = $makhoa;
        $this->grade_option = $grade_option;
        $this->grade = $grade;
        $this->start_date_quiz = $start_date_quiz;
        $this->end_date_quiz = $end_date_quiz;
    }

    public function get_course() {

        global $DB;

        $sql = "SELECT *
                  FROM {course}
                  WHERE startdate >= :startdate AND startdate < :enddate AND visible = 1";
        $params = array(
            'startdate' => $this->course_start_date,
            'enddate' => $this->course_start_date + 24 * 60 * 60 - 1,
        );

        $courses = $DB->get_records_sql($sql, $params);

        return $courses;
    }

    public function get_quiz_grade() {

        global $DB;

        $cohort = get_config('local_thlib', 'enrollmentcourseshortname');
        $cohorts = explode(",", $cohort);
        $student_cohort = trim($cohorts[0]);
        $student_cohort2 = trim($cohorts[1]);

        $user_arr = $this->get_all_user_course();
        // print_object('$user_arr');
        // print_object($user_arr);

        $records = array();

        foreach ($user_arr as $userid => $user) {

            $courses = $this->enrol_get_all_users_courses($userid, true);
            // print_object('$courses');
            // print_object($courses);
            $tmp = [];
            foreach ($courses as $courseid => $course) {

                // foreach ($this->makhoa as $key => $makhoa) {

                // $sql = "SELECT DISTINCT qg.userid,qg.grade,c.fullname,q.name
                //         FROM {course} c, {user} u, {quiz} q, {quiz_grades} qg
                //         WHERE c.id = q.course AND q.id = qg.quiz AND qg.userid = u.id
                //             AND u.id = $userid AND c.id = $courseid ";

                // $sql = "SELECT qg.id,userid,qg.grade,q.name,q.course
                //         FROM {quiz_grades} qg
                //         JOIN {quiz} q ON q.id = qg.quiz AND q.course = $courseid
                //         WHERE 1 = 1 AND qg.userid = $userid
                //         AND q.timeopen >= $this->start_date_quiz AND q.timeopen < $this->end_date_quiz ";

                // $sql = "SELECT DISTINCT qg.userid,qg.grade,q.name,q.course,gi.gradepass,gi.idnumber,cm.id as cmid
                //         FROM {quiz} q
                //         JOIN {grade_items} gi ON gi.iteminstance = q.id AND gi.itemmodule like 'quiz' AND gi.idnumber <> ''
                //         JOIN {quiz_grades} qg ON qg.quiz = q.id AND qg.userid = $userid
                //         JOIN {course_modules} cm ON cm.instance = q.id AND cm.course = $courseid AND cm.deletioninprogress = 0 AND cm.visible = 1 AND cm.module = (SELECT DISTINCT m.id FROM {modules} m JOIN {course_modules} cm ON m.id = cm.module WHERE m.name like 'quiz' AND cm.visible = 1)
                //         WHERE 1 = 1 AND qg.userid = $userid AND q.course = $courseid
                //         AND q.timeopen >= $this->start_date_quiz AND q.timeopen < $this->end_date_quiz ";
                $sql = "SELECT q.id,q.name,q.course,gi.gradepass,gi.idnumber,cm.id as cmid,qg.userid,qg.grade
                        FROM {quiz} q
                        JOIN {grade_items} gi ON gi.iteminstance = q.id AND gi.itemmodule like 'quiz' AND gi.idnumber <> ''
                        JOIN {course_modules} cm ON cm.instance = q.id AND cm.course = $courseid AND cm.deletioninprogress = 0 AND cm.visible = 1 AND cm.module = (SELECT DISTINCT m.id FROM {modules} m JOIN {course_modules} cm ON m.id = cm.module WHERE m.name like 'quiz' AND cm.visible = 1)
                        LEFT JOIN {quiz_grades} qg ON qg.quiz = q.id AND qg.userid = $userid
                        WHERE 1 = 1 AND q.course = $courseid AND q.timeopen >= $this->start_date_quiz AND q.timeopen < $this->end_date_quiz ";

                if ($this->grade_option == 0) {
                    $sql .= "AND (qg.grade < $this->grade OR qg.grade IS NULL)";
                } else if ($this->grade_option == 1) {
                    $sql .= "AND qg.grade > $this->grade";
                } else {
                    $sql .= "AND qg.grade = $this->grade";
                }
                $records_db = $DB->get_records_sql($sql);
                // print_object($records_db);

                if ($records_db) {

                    foreach ($records_db as $key => $t) {

                        $tmp[$courseid][] = $t;
                    }
                }
            }
            if ($tmp) {

                $records[$userid] = $tmp;
            }
        }

        return $records;
    }

    public function get_enrolled_users($courseid, $makhoa) {

        global $DB;

        $cohort = get_config('local_thlib', 'enrollmentcourseshortname');
        $cohorts = explode(",", $cohort);
        $student_cohort = trim($cohorts[0]);
        $student_cohort2 = trim($cohorts[1]);

        $sql = "SELECT u.*,d.data
              FROM {user} u
              JOIN
              (SELECT DISTINCT eu1_u.id
                FROM {user} eu1_u
                JOIN {user_enrolments} ej1_ue ON ej1_ue.userid = eu1_u.id AND ej1_ue.status = 0 AND (ej1_ue.timeend = 0 || ej1_ue.timeend > :timeend)
                JOIN {enrol} ej1_e ON (ej1_e.id = ej1_ue.enrolid AND ej1_e.courseid = :ej1_courseid)
                JOIN {role_assignments} ra ON ra.userid = eu1_u.id AND ra.roleid = 5
                WHERE 1 = 1 AND eu1_u.deleted = 0) je
              ON je.id = u.id
              JOIN {user_info_data} d ON d.userid = u.id AND (d.fieldid = (SELECT id FROM {user_info_field} WHERE shortname LIKE :student_cohort AND d.data LIKE :makhoa) OR d.fieldid = (SELECT id FROM {user_info_field} WHERE shortname LIKE :student_cohort2 AND d.data LIKE :makhoa1))
             WHERE u.deleted = 0 AND u.suspended = 0 ORDER BY u.lastname, u.firstname, u.id";
// AND d.data LIKE :makhoa
        // OR d.fieldid = (SELECT id FROM {user_info_field} WHERE shortname LIKE :student_cohort2 AND d.data LIKE :makhoa1
        $params = array('ej1_courseid' => $courseid, 'timeend' => time(), 'student_cohort' => $student_cohort, 'student_cohort2' => $student_cohort2, 'makhoa' => $makhoa, 'makhoa1' => $makhoa);

        return $DB->get_records_sql($sql, $params);

    }

    public function get_all_user_course() {
        global $DB;

        $courses = $this->get_course();
        $user_arr = array();
        $all_user_course = array();

        foreach ($courses as $key => $course) {
            foreach ($this->makhoa as $key => $makhoa) {

                $users = $this->get_enrolled_users($course->id, $makhoa);
                foreach ($users as $key => $user) {
                    $user_arr[$user->id] = $user;
                    $all_user_course[$user->id] = $user->id;
                }
            }
        }

        $this->all_user_course = $all_user_course;

        return $user_arr;
    }

    public function get_fullname_course($courseid) {
        global $DB, $CFG;

        $course_name = '';

        if ($course = $DB->get_record('course', array('id' => $courseid))) {

            $link_course = $CFG->wwwroot . "/course/view.php?id=" . $courseid;
            $course_name = html_writer::link($link_course, $course->fullname);

            return $course_name;

        }
        return $course_name;

    }

    public function enrol_get_all_users_courses($userid, $onlyactive = false, $fields = null, $sort = null) {

        global $DB;

        if (isguestuser($userid) or empty($userid)) {
            return (array());
        }

        $sql = "SELECT c.id,c.category,c.sortorder,c.shortname,c.fullname,c.idnumber,c.startdate,c.visible,c.defaultgroupingid,c.groupmode,c.groupmodeforce , ctx.id AS ctxid, ctx.path AS ctxpath, ctx.depth AS ctxdepth, ctx.contextlevel AS ctxlevel, ctx.instanceid AS ctxinstance, ctx.locked AS ctxlocked
              FROM {course} c
              JOIN (SELECT DISTINCT e.courseid
                      FROM {enrol} e
                      JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)
                 WHERE ue.status = :active AND e.status = :enabled AND ue.timestart < :now1 AND (ue.timeend = 0 OR ue.timeend > :now2)
                   ) en ON (en.courseid = c.id)
           LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)
             WHERE c.id <> :siteid AND c.visible = 1 AND c.startdate >= :startdate AND c.startdate < :enddate
          ORDER BY c.visible DESC,c.sortorder ASC";

        $params = array(
            'siteid' => SITEID,
            'now1' => round(time(), -2),
            'now2' => round(time(), -2),
            'active' => ENROL_USER_ACTIVE,
            'enabled' => ENROL_INSTANCE_ENABLED,
            'contextlevel' => CONTEXT_COURSE,
            'userid' => $userid,
            'startdate' => $this->course_start_date,
            'enddate' => $this->course_start_date + 24 * 60 * 60 - 1,
        );
        $courses = $DB->get_records_sql($sql, $params);

        return $courses;
    }
}
?>
