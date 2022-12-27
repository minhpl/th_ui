<?php

require_once "$CFG->libdir/externallib.php";
require_once "lib.php";
require_once $CFG->dirroot . "/course/externallib.php";

class local_thlib_external extends external_api {

	public static function loadcourses_parameters() {
		return new external_function_parameters(
			array(
				'makhoaarr' => new external_multiple_structure(
					new external_value(PARAM_RAW, 'string makhoa', VALUE_OPTIONAL), 'aray makhoa'
				),
				'maloparr' => new external_multiple_structure(
					new external_value(PARAM_RAW, 'string malop', VALUE_OPTIONAL), 'aray malop'
				),
				'useridarr' => new external_multiple_structure(
					new external_value(PARAM_INT, 'int, the id of usser', VALUE_OPTIONAL), 'aray userid'
				),
				'time_from' => new external_value(PARAM_INT, 'int, time from'),
				'time_to' => new external_value(PARAM_INT, 'int, time to'),
			)
		);
	}

	public static function loadcourses_returns() {
		return new external_multiple_structure(
			new external_single_structure(
				array(
					'id' => new external_value(PARAM_INT, 'id of the course'),
					'coursefullname' => new external_value(PARAM_RAW, 'course full name of the course'),
				)
			)
		);
	}

	public static function loadcourses($makhoaarr = null, $maloparr = null, $useridarr = null, $time_from = null, $time_to = null) {
		global $DB;

		if (empty($makhoaarr) && empty($maloparr) && empty($useridarr)) {
			$courses = $DB->get_records('course', array('visible' => '1'),
				'', 'id,fullname,shortname,idnumber,category');

			$courses_fullname = array();

			$keyfrontcourse = 1;
			foreach ($courses as $crsid => $value) {
				if ($value->category == 0) {
					$keyfrontcourse = $crsid;
					continue;
				}

				$n = $value->fullname;

				if (isset($value->shortname) && trim($value->shortname) !== '') {
					$n .= ',' . $value->shortname;
				}

				if (isset($value->idnumber) && trim($value->idnumber) !== '') {
					$n .= ',' . $value->idnumber;
				}

				$obj = new stdClass();
				$obj->id = $crsid;
				$obj->coursefullname = $n;

				$courses_fullname[$crsid] = $obj;
			}

			return $courses_fullname;
		}

		$userid_arr = get_user_filtered_from_arrayof_makhoa_malop($makhoaarr, $maloparr, $useridarr);

		$courses_fullname = [];

		$sql_time = '';
		if ($time_from && $time_to) {
			$sql_time = ' AND ((ue.timestart > :timefrom1 and ue.timestart!=0) OR (ue.timestart = 0 AND ue.timecreated > :timefrom2))
			 	 	        AND ((ue.timestart < :timeend1 and ue.timestart!=0) OR (ue.timestart = 0 AND ue.timecreated < :timeend2)) ';
		}

		if (sizeof($userid_arr)) {

			list($insql, $params) = $DB->get_in_or_equal($userid_arr, SQL_PARAMS_NAMED, 'ctx');

			$sql = "SELECT c.id as id, c.fullname as coursefullname
				from {course}  c, {user_enrolments} ue, {enrol}  e
				where e.id = ue.enrolid and e.courseid = c.id
				and ue.userid $insql
				$sql_time
				group by c.id";

			// $params = array_merge($params, array('timefrom1' => $time_from, 'timefrom2' => $time_from, 'timeend1' => $time_to, 'timeend2' => $time_to));
			$records = $DB->get_records_sql($sql, $params);
			// print_object($records);
			return $records;
		}

		return $courses_fullname;
	}

	public static function loadcourses2($makhoaarr = null, $maloparr = null, $useridarr = null, $time_from = null, $time_to = null) {
		global $DB;

		if (empty($makhoaarr) && empty($maloparr) && empty($useridarr)) {
			$courses = $DB->get_records('course', array('visible' => '1'),
				'', 'id,fullname,shortname,idnumber,category');

			$courses_fullname = array();

			$keyfrontcourse = 1;
			foreach ($courses as $crsid => $value) {
				if ($value->category == 0) {
					$keyfrontcourse = $crsid;
					continue;
				}

				$n = $value->fullname;

				if (isset($value->shortname) && trim($value->shortname) !== '') {
					$n .= ',' . $value->shortname;
				}

				if (isset($value->idnumber) && trim($value->idnumber) !== '') {
					$n .= ',' . $value->idnumber;
				}

				$obj = new stdClass();
				$obj->id = $crsid;
				$obj->coursefullname = $n;

				$courses_fullname[$crsid] = $obj;
			}

			return $courses_fullname;
		}

		$userid_arr = get_user_filtered_from_arrayof_makhoa_malop($makhoaarr, $maloparr, $useridarr);

		// print_object($userid_arr);

		$courses_fullname = [];

		$sql_time = '';
		if ($time_from && $time_to) {
			$sql_time = ' AND ((ue.timestart > :timefrom1 and ue.timestart!=0) OR (ue.timestart = 0 AND ue.timecreated > :timefrom2))
			 	 	        AND ((ue.timestart < :timeend1 and ue.timestart!=0) OR (ue.timestart = 0 AND ue.timecreated < :timeend2)) ';
		}

		if (sizeof($userid_arr)) {
			list($insql, $params) = $DB->get_in_or_equal($userid_arr, SQL_PARAMS_NAMED, 'ctx');
			$sql = "SELECT user_course.* ,{grade_grades}.finalgrade
				from(
					select row_number() OVER (Order by a.userid) as id, a.*
					from (
						select {user}.id as userid , course.fullname, course.shortname, course.idnumber, course.id as courseid
						from
							{user}
							left join
							{user_enrolments} ue
							on {user}.id = ue.userid
							$sql_time
							left join {enrol}
							on {enrol}.id = ue.enrolid
							left join {course} course
							on course.id = {enrol}.courseid and course.visible = 1
							where {user}.id $insql
						) a
						group by userid, courseid
					) user_course
				left join {grade_items}
				on {grade_items}.courseid = user_course.courseid and {grade_items}.itemtype='course'
				left join {grade_grades}
				on {grade_grades}.itemid = {grade_items}.id and {grade_grades}.userid = user_course.userid";

			$params = array_merge($params, array('timefrom1' => $time_from, 'timefrom2' => $time_from, 'timeend1' => $time_to, 'timeend2' => $time_to));
			$records = $DB->get_records_sql($sql, $params);

			$rows = [];

			foreach ($records as $key => $value) {
				$userid = $value->userid;
				$courseid = $value->courseid;
				$coursefullname = $value->fullname;
				$courseshortname = $value->shortname;
				$courseidnumber = $value->idnumber;
				$finalgrade = $value->finalgrade;

				if (!array_key_exists($userid, $rows)) {
					$rows[$userid] = array();
				}

				if ($courseid) {
					$rows[$userid][$courseid] = $finalgrade;
					// $courses_fullname[$courseid] = $coursefullname;
					$obj = new stdClass();
					$obj->id = $courseid;
					$text = $coursefullname;
					if ($courseshortname != null && $courseshortname != '') {
						$text .= ', ' . $courseshortname;
					}
					if ($courseidnumber != null && $courseidnumber != '') {
						$text .= ', ' . $courseidnumber;
					}
					$obj->coursefullname = $text;
					if (!array_key_exists($courseid, $courses_fullname)) {
						$courses_fullname[$courseid] = $obj;
					}

				}
			}
		}

		return $courses_fullname;
	}

	//remain functions
	public static function loadsettings_parameters() {
		return new external_function_parameters(
			array(
				'itemid' => new external_value(PARAM_INT, 'The item id to operate on'),
			)
		);
	}

	public static function loadsettings_returns() {
		return new external_multiple_structure(
			new external_single_structure(
				array(
					'content' => new external_value(PARAM_RAW, 'settings content text'),
				)
			)
		);
	}

	public static function loadsettings($itemid) {
		global $DB;
		//$params = self::validate_parameters(self::getExample_parameters(), array());
		$params = self::validate_parameters(self::loadsettings_parameters(),
			array('itemid' => $itemid));

		$sql = 'SELECT content FROM {testtest} WHERE id = ?';
		$paramsDB = $params; //array($itemid);
		$db_result = $DB->get_records_sql($sql, $paramsDB);

		return $db_result;
	}

	public static function updatesettings_parameters() {
		return new external_function_parameters(

			array(
				'itemid' => new external_value(PARAM_INT, 'The item id to operate on'),
				'data2update' => new external_value(PARAM_TEXT, 'Update data'))
		);
	}

	public static function updatesettings_returns() {
		return new external_multiple_structure(
			new external_single_structure(
				array(
					'content' => new external_value(PARAM_RAW, 'settings content text'),
				)
			)
		);
	}

	public static function updatesettings($itemid, $data2update) {
		global $DB;
		//$params = self::validate_parameters(self::getExample_parameters(), array());
		$params = self::validate_parameters(self::updatesettings_parameters(),
			array('itemid' => $itemid, 'data2update' => $data2update));

		$newdata = new stdClass();
		$newdata->id = $itemid;
		$newdata->content = $data2update;
		if ($DB->record_exists('testtest', array('id' => $itemid))) {
			$DB->update_record('testtest', $newdata);
		}

		$sql = 'SELECT content FROM {testtest} WHERE id = ?';
		$paramsDB = array($itemid);
		$db_result = $DB->get_records_sql($sql, $paramsDB);

		return $db_result;
	}

	// ///////////////

	// public static function load_registeredcourses_by_userid_parameters() {

	// 	return new external_function_parameters(
	// 		array(
	// 			new external_value(PARAM_INT, 'The id of user to fetch registerd courses'),
	// 		)
	// 	);
	// }

	// public static function load_registeredcourses_by_userid_returns() {

	// 	$returnstructure = course_summary_exporter::get_read_structure();
	// 	$returnstructure->keys["activated_link"] = new external_value(PARAM_URL, 'link to active this course');
	// 	return new external_single_structure(
	// 		array(
	// 			'courses' => new external_multiple_structure($returnstructure, 'Course'),
	// 			'nextoffset' => new external_value(PARAM_INT, 'Offset for the next request'),
	// 		)
	// 	);
	// }

	// public static function load_registeredcourses_by_userid($userid = null) {

	// 	global $CFG, $PAGE, $DB, $USER;
	// 	require_once $CFG->dirroot . '/course/lib.php';
	// 	require_once $CFG->dirroot . '/user/profile/lib.php';

	// 	self::validate_context(context_user::instance($USER->id));

	// 	if (!$userid || $userid == 0) {
	// 		$userid = $USER->id;
	// 	}

	// 	$config = get_config('block_th_activatecourses');
	// 	$registercourse_shortname = $config->regisredcourseshortname;

	// 	if (empty(trim($registercourse_shortname))) {
	// 		return [
	// 			'courses' => [],
	// 			'nextoffset' => 0,
	// 		];
	// 	}

	// 	$filteredcourses = "";

	// 	$userfielddatas = profile_get_user_fields_with_data($userid);
	// 	foreach ($userfielddatas as $fd) {
	// 		if ($fd->field->shortname == $registercourse_shortname) {
	// 			$filteredcourses = $fd->field->data;
	// 			break;
	// 		}
	// 	}

	// 	$courseidarr = explode(",", $filteredcourses);

	// 	$filteredcourses = [];
	// 	if (count($courseidarr) == 0) {

	// 	} else {
	// 		list($insql, $params) = $DB->get_in_or_equal($courseidarr);
	// 		$sql = "SELECT * from {course} where id $insql";

	// 		$records = $DB->get_records_sql($sql, $params);
	// 		foreach ($records as $record) {

	// 			$courseid = $record->id;
	// 			$context = context_course::instance($courseid);
	// 			if (!is_enrolled($context, $userid, '', true)) {
	// 				$filteredcourses[] = $record;
	// 			}
	// 		}
	// 	}

	// 	$offset = 0;
	// 	$processedcount = count($filteredcourses);

	// 	$renderer = $PAGE->get_renderer('core');

	// 	$formattedcourses = array_map(function ($course) use ($renderer) {
	// 		context_helper::preload_from_record($course);
	// 		$context = context_course::instance($course->id);
	// 		$exporter = new course_summary_exporter($course, ['context' => $context]);
	// 		return $exporter->export($renderer);
	// 	}, $filteredcourses);

	// 	print_object($formattedcourses);

	// 	foreach ($formattedcourses as $key => $value) {
	// 		$formattedcourses[$key]->activated_link = $CFG->wwwroot . "/blocks/th_selfenrol_registeredcourses/activate.php?id=$value->id";
	// 	}

	// 	return [
	// 		'courses' => $formattedcourses,
	// 		'nextoffset' => $offset + $processedcount,
	// 	];
	// }

}
