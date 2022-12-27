<?php
function th_loginreport_get_fullname_course($courseid) {
	global $DB;
	return $DB->get_record('course', array('id' => $courseid), 'fullname')->fullname;
}
function th_loginreport_get_fullname_user($userid) {
	global $DB;
	$user = $DB->get_record('user', array('id' => $userid), 'firstname,lastname');
	return $user->firstname . ' ' . $user->lastname;
}
function th_loginreport_get_count_access_course($userid, $courseid) {
	global $DB;
	return $DB->count_records_sql("SELECT COUNT(ls.id) FROM {logstore_standard_log} ls WHERE userid=? AND courseid=? AND contextlevel=50 AND target='course'", array($userid, $courseid));
}
function th_loginreport_get_last_access($userid, $courseid) {
	global $DB;
	return $DB->get_record("user_lastaccess", array("userid" => $userid, "courseid" => $courseid), 'timeaccess');
}
