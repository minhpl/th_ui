<?php
require_once "$CFG->libdir/externallib.php";

class block_th_course_unenrollment_report_external extends external_api {

	/**
	 * Returns description of method parameters
	 * @return external_function_parameters
	 */
	public static function load_users_parameters() {
		return new external_function_parameters(
			array(
				'courseid' => new external_value(PARAM_INT, 'int, the id of courseid'),
				'date' => new external_value(PARAM_RAW, 'string, date'),
			)
		);
	}

	public static function load_users_returns() {
		return new external_multiple_structure(
			new external_single_structure(
				array(
					'userid' => new external_value(PARAM_INT, 'userid'),
					'fullname' => new external_value(PARAM_RAW, 'user fullname'),
					'username' => new external_value(PARAM_RAW, 'user name'),
					'role' => new external_value(PARAM_RAW, 'role'),
					'institution' => new external_value(PARAM_RAW, 'institution', VALUE_OPTIONAL),
					'enrolment_date' => new external_value(PARAM_RAW, 'enrolment date'),
					'enrolment_activation_date' => new external_value(PARAM_RAW, 'enrolment activation date'),
					'enrolment_expire_date' => new external_value(PARAM_RAW, 'enrolment expire date'),
					'enrolment_status' => new external_value(PARAM_RAW, 'enrolment status'),
				)
			)
		);
	}

	public static function load_users($courseid, $date) {
		//Don't forget to set it as static
		global $CFG, $DB;

		$date = trim($date);
		$date = str_replace(" ", "", $date);
		$one_day = 60 * 60 * 24;
		$string_len = strlen($date);

		if ($string_len == 10) {
			//option = day

			$date = date_create_from_format("d/m/Y", $date);
			$date = date_format($date, "Y/m/d");

			$start_date = strtotime($date);
			$end_date = $start_date + $one_day - 1;

			// $start_date = date('d/m/Y H:i:s', $start_date);
			// $end_date = date('d/m/Y H:i:s', $end_date);
		} elseif ($string_len > 10) {
			// option = week

			$arr = explode('-', $date);
			$start_date = $arr[0];
			$end_date = $arr[1];

			$start_date = date_create_from_format("d/m/Y", $start_date);
			$start_date = date_format($start_date, "Y/m/d");
			$start_date = strtotime($start_date);

			$end_date = date_create_from_format("d/m/Y", $end_date);
			$end_date = date_format($end_date, "Y/m/d");
			$end_date = strtotime($end_date);
			$end_date = $end_date + $one_day - 1;

			//$start_date = date('d/m/Y H:i:s', $start_date);
			// $end_date = date('d/m/Y H:i:s', $end_date);

		} else {
			// option = month

			$start_date = $date;
			$end_date = $date;

			$start_date = date_create_from_format("m/Y", $start_date);
			$start_date = date_format($start_date, "Y/m/d");
			$start_date = strtotime($start_date);
			$start_date = strtotime("first day of this month", $start_date);

			$end_date = date_create_from_format("m/Y", $end_date);
			$end_date = date_format($end_date, "Y/m/d");
			$end_date = strtotime($end_date);
			$end_date = strtotime("last day of this month", $end_date);
			$end_date = $end_date + $one_day - 1;

			// $start_date = date('d/m/Y H:i:s', $start_date);
			// $end_date = date('d/m/Y H:i:s', $end_date);
		}

		$sql = "
		SELECT DISTINCT m.id AS userid,
						concat(m.firstname,' ',m.lastname) AS fullname,
						m.username,m.shortname AS role,
						rc.timecreated AS enrolment_activation_date,
						m.timestart AS enrolment_date,
						m.timeend AS enrolment_expire_date,
						m.status AS enrolment_status,
						m.institution
		FROM
			(SELECT DISTINCT u.id,firstname,lastname,username,email,institution,
							r.shortname,
							ue.timestart,timeend,ue.status,
							c.id AS courseid
			FROM {user} u
			JOIN {user_enrolments} ue ON ue.userid = u.id
			JOIN {enrol} e ON e.id = ue.enrolid
			JOIN {role_assignments} ra ON ra.userid = u.id
			JOIN {context} ct ON ct.id = ra.contextid AND ct.contextlevel = 50
			JOIN {course} c ON c.id = ct.instanceid AND e.courseid = c.id
			JOIN {role} r ON r.id = ra.roleid
			WHERE c.visible=1 AND e.status = 0 AND u.suspended = 0 AND u.deleted = 0
				AND c.id = :courseid AND ue.timeend>=:start_date AND ue.timeend<=:end_date
			) m
		LEFT JOIN
	   		{th_registeredcourses} rc ON m.id=rc.userid AND m.courseid=rc.courseid
		GROUP BY m.id
		HAVING MAX(m.timestart)";

		$params = array('courseid' => $courseid, 'start_date' => $start_date, 'end_date' => $end_date);
		$records = $DB->get_records_sql($sql, $params);
		if (!$records) {
			return;
		}
		$lang = current_language();

		foreach ($records as $key => $record) {
			$record->enrolment_date = date("d/m/Y", $record->enrolment_date);

			$timeend = $record->enrolment_expire_date;
			$status = $record->enrolment_status;
			if ($status == 1) {
				$record->enrolment_status = get_string('suspend', 'block_th_course_unenrollment_report');
			} elseif ($status == 0 && $timeend >= idate("U")) {
				$record->enrolment_status = get_string('active');
			} elseif ($status == 0 && $timeend < idate("U")) {
				$record->enrolment_status = get_string('inactive', 'block_th_course_unenrollment_report');
				if ($timeend == 0) {
					$record->enrolment_status = get_string('active');
				}
			}

			$record->enrolment_expire_date = date("d/m/Y", $record->enrolment_expire_date);

			$registered = $record->enrolment_activation_date;
			if (empty($registered)) {
				$record->enrolment_activation_date = "N/A";
			} else {
				$record->enrolment_activation_date = date('d/m/Y', $registered);
			}
		}

		return $records;
	}
}