<?php

class th_teacher_activity {

    public function get_course($course_start_date) {

        global $DB;

        $sql = "SELECT *
                  FROM {course}
                  WHERE startdate >= :startdate AND startdate < :enddate AND visible = 1";
        $params = array(
            'startdate' => $course_start_date,
            'enddate' => $course_start_date + 24 * 60 * 60 - 1,
        );

        $courses = $DB->get_records_sql($sql, $params);

        return $courses;
    }

    public function get_count_access_course($userid, $courseid, $startdate, $enddate) {

        global $DB;

        $sql = "SELECT COUNT(ls.id)
        		FROM {logstore_standard_log} ls
        		WHERE userid = :userid AND courseid = :courseid AND contextlevel=50
                	AND target like 'course' AND action like 'viewed' AND realuserid IS NULL
        		AND timecreated >= :startdate AND timecreated < :enddate";
        $params = array(
            'userid' => $userid,
            'courseid' => $courseid,
            'startdate' => $startdate,
            'enddate' => $enddate,
        );

        $count = $DB->count_records_sql($sql, $params);

        return $count;
    }

    public function get_last_access_course($userid, $courseid) {

        global $DB;

        $last_access_course = $DB->get_record("user_lastaccess", array("userid" => $userid, "courseid" => $courseid), 'timeaccess');

        if ($last_access_course) {

            return date('d/m/Y H:i:s', $last_access_course->timeaccess);
        }

        return get_string('no_access_course', 'block_th_teacher_activity_report');

    }

    public function get_count_forum_posts($userid, $courseid, $forum_type, $startdate, $enddate) {

        global $DB;

        $sql = "SELECT COUNT(fp.id)
	   			FROM {forum_discussions} fd
				JOIN {forum_posts} fp ON fp.discussion = fd.id
				JOIN {course} c ON c.id = fd.course
        		JOIN {forum} f ON fd.forum = f.id
	   			WHERE c.visible = 1 AND f.type LIKE :forum_type AND fp.userid = :userid
	   				AND c.id = :courseid AND fp.created >= :startdate AND fp.created < :enddate";

        $params = array(
            'courseid' => $courseid,
            'userid' => $userid,
            'startdate' => $startdate,
            'enddate' => $enddate,
            'forum_type' => $forum_type,
        );

        $count = $DB->count_records_sql($sql, $params);

        return $count;
    }

    public function get_questions_qaa($courseid, $startdate, $enddate) {

        global $DB;

        $sql = "SELECT COUNT(qa.id)
				FROM {qaa} q
				JOIN {qaapairs} qa ON q.id = qa.qaaid
				JOIN {course} c ON q.course = c.id
				WHERE c.id = :courseid AND c.visible = 1
				AND timecreatedquestion >= :startdate AND timecreatedquestion < :enddate";
        $params = array(
            'courseid' => $courseid,
            'startdate' => $startdate,
            'enddate' => $enddate,
        );
        $count = $DB->count_records_sql($sql, $params);

        return $count;
    }

    public function get_answers_qaa($userid, $courseid, $startdate, $enddate) {

        global $DB;

        $sql = "SELECT COUNT(qa.id)
				FROM {qaa} q
				JOIN {qaapairs} qa ON q.id = qa.qaaid
				JOIN {course} c ON q.course = c.id
				WHERE c.id = :courseid AND c.visible = 1 AND qa.teacherid = :userid
				AND timecreatedanswer >= :startdate AND timecreatedanswer < :enddate";
        $params = array(
            'courseid' => $courseid,
            'userid' => $userid,
            'startdate' => $startdate,
            'enddate' => $enddate,
        );
        $count = $DB->count_records_sql($sql, $params);

        return $count;
    }
}
?>
