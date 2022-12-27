<?php
//lay ngay sau ngay ket thuc
function layngay($endday, $range) {
	$day = (new DateTime())->setTimestamp(usergetmidnight($endday));
	$day->modify($range);
	$start = $day->getTimestamp();
	return $start;
}

/**
 * [laytaikhoan] Lấy các Học viên được ghi danh vào Khóa học theo thời gian
 *
 * @param [int] $startday	Ngày bắt đầu (từ 0 giờ 0 phút 0 giây Ngày bắt đầu)
 * @param [int] $endday		Ngày kết thúc (đến 23 giờ 59 phút 59 giây Ngày kết thúc)
 * @param [int] $courseid	ID của khóa hoc
 * @return [array]          Các Học viên được ghi danh vào khóa học trong khoảng thời gian
 */
function laytaikhoan($startday, $endday, $courseid) {
	global $DB;
	//echo date('d/m/Y H:i:s', $startday) . ' - ' . date('d/m/Y H:i:s', $endday) . '</br>';
	// return $DB->get_records_sql('SELECT * FROM {user} WHERE {user}.id IN (SELECT DISTINCT u.id
	// 	FROM {user_enrolments} ue, {course} c, {user} u, {enrol} e, {role_assignments} ra
	// 		WHERE c.id=e.courseid AND u.id=ue.userid AND e.id=ue.enrolid AND u.id=ra.userid AND ra.roleid=5
	// 		AND u.deleted=0 AND c.id=? AND ue.timecreated>=? AND ue.timecreated<?) GROUP BY id', [$courseid, $startday, $endday]);
	return $DB->get_records_sql("SELECT DISTINCT u.id, ue.timestart, ue.timeend
		FROM {user_enrolments} ue, {course} c, {user} u, {enrol} e, {role_assignments} ra
			WHERE c.id=e.courseid AND u.id=ue.userid AND e.id=ue.enrolid AND u.id=ra.userid AND ra.roleid=5
			AND u.deleted=0 AND c.id=? AND ue.timecreated>=? AND ue.timecreated<? AND e.status = 0 AND u.suspended = 0", [$courseid, $startday, $endday]);
}
//chuyen ngay kieu int sang kieu date
function day($date) {
	return date('d/m/Y', $date);
}