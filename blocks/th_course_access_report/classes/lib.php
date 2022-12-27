<?php
namespace th_course_access_report;

class lib {
    public function get_fullname_course($courseid) {
        global $DB;
        $sql = "SELECT fullname FROM {course} WHERE id = $courseid";
        $fullname = $DB->get_field_sql($sql);
        return $fullname;
    }

    public function get_shortname_course($courseid) {
        global $DB;
        $sql = "SELECT shortname FROM {course} WHERE id = $courseid";
        $shortname = $DB->get_field_sql($sql);
        return $shortname;
    }
    /**
     * [get_access_course] Lấy số lần truy cập vào khóa học của học viên
     *
     * @param [int] $userid     id của tài khoản
     * @param [int] $courseid   id của khóa học
     * @param [int] $from_date  Ngày bắt đầu (0 giờ 0 phút 0 giây Ngày bắt đầu)
     * @param [int] $to_date    Ngày kết thúc (23 giờ 59 phút 59 giây Ngày kết thúc)
     * @return int              Lấy số lần truy cập vào khóa học của học viên
     */
    public function get_access_course($userid, $courseid, $from_date, $to_date) {
        global $DB;
        $sql = "SELECT COUNT(ls.id) as accesscourse
				FROM {logstore_standard_log} ls
				WHERE contextlevel=50 AND target='course' AND courseid=:courseid
				AND userid=:userid AND timecreated>:from_date AND timecreated<=:to_date";
        $params = array(
            'courseid' => $courseid,
            'userid' => $userid,
            'from_date' => $from_date,
            'to_date' => $to_date,
        );
        $record = $DB->record_exists_sql($sql, $params);

        if (empty($record)) {
            return 0;
        }
        return $DB->get_field_sql($sql, $params);
    }

    public function get_courseid() {
        global $DB;
        $courses = $DB->get_records('course', array('visible' => 1), '', 'id,fullname,shortname,idnumber,category');

        $choice = array();
        foreach ($courses as $key => $value) {

            $n = $value->id;

            $choice[$key] = $n;
        }
        return $choice;
    }

    public static function get_allcourseid_form($mform) {
        global $DB;
        $courses = $DB->get_records('course', array('visible' => 1), '', 'id,fullname,shortname,idnumber,category');
        $choice = array();
        $choice[''] = '';
        $keyfrontcourse = 1;
        foreach ($courses as $key => $value) {
            if ($value->category == 0) {
                $keyfrontcourse = $key;
                continue;
            }

            $n = $value->fullname;

            if (isset($value->shortname) && trim($value->shortname) !== '') {
                $n .= ',' . $value->shortname;
            }

            if (isset($value->idnumber) && trim($value->idnumber) !== '') {
                $n .= ',' . $value->idnumber;
            }

            $choice[$key] = $n;
        }
        unset($courses[$keyfrontcourse]);

        $options = array(
            'multiple' => true,
            'noselectionstring' => get_string('choose_a_course', 'block_th_course_access_report'),
        );
        $mform->addElement('autocomplete', 'courseid', get_string('course', 'block_th_course_access_report'), $choice, $options);
        return $courses;
    }

    public function get_userid() {
        global $DB;

        $users = $DB->get_records('user', ['suspended' => 0, 'deleted' => 0], '', 'id');
        $choice = array();

        foreach ($users as $key => $value) {

            $choice[$value->id] = $value->id;
        }
        return $choice;
    }
}
?>