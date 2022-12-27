<?php
require_once $CFG->dirroot . '/config.php';
require_once $CFG->libdir . '/gradelib.php';
require_once $CFG->dirroot . '/user/renderer.php';
require_once $CFG->dirroot . '/grade/lib.php';
require_once $CFG->dirroot . '/grade/report/grader/lib.php';
require_once $CFG->libdir . '/html2text/lib.php';

class thlib {
	const USER_FIELD = ['username', 'email', 'institution'];
	const ONE_DAY = 26 * 60 * 60;
	const USER_ACTIVE_STATUS_ALL = 0;
	const USER_STATUS_SUPPENDED = 2;
	const USER_STATUS_ACTIVE = 1;
	const USER_STATUS_ALL = 0;

	public static function logged($data) {
		$event = \local_thlib\event\debug_logged::create(array(
			'context' => context_system::instance(),
			'other' => ['log' => $data],
		));
		$event->trigger();
	}

	public static function filter_userarr_by_userstatus($userid_arr, $user_status = thlib::USER_STATUS_ALL) {
		global $DB;

		if (count($userid_arr) == 0) {
			return $userid_arr;
		}

		if ($user_status == thlib::USER_ACTIVE_STATUS_ALL) {
			return $userid_arr;
		}

		list($insql, $params) = $DB->get_in_or_equal($userid_arr);

		$wheresql_userstatus = '';
		if ($user_status == thlib::USER_STATUS_ACTIVE) {
			$wheresql_userstatus .= ' us.suspended = 0';
		} else if ($user_status == thlib::USER_STATUS_SUPPENDED) {
			$wheresql_userstatus .= ' us.suspended = 1';
		}

		$sql = "SELECT *
				from {user} us
				where $wheresql_userstatus and id  $insql";
		$records = $DB->get_records_sql($sql, $params);
		$userid_arr = array_keys($records);

		return $userid_arr;
	}

	public static function html2text($text) {
		$html = new \Html2Text\Html2Text($text);
		return $html->getText();
	}

	public static function get_string_params() {
		global $CFG, $SITE;

		$config = get_config("local_th_alert_scheduledtask");
		$universityshortname = $SITE->shortname;
		if (substr($universityshortname, 0, 1) == 'e') {
			$universityshortname = substr($universityshortname, 1);
		}

		$elearningwebsite = $CFG->wwwroot;
		$emailcontact = $config->emailcontact;
		$hotline = get_config("local_thlib")->technical_hotline;
		$stringparams = [
			'universityshortname' => $universityshortname,
			'elearningwebsite' => $elearningwebsite,
			'emailcontact' => $emailcontact,
			'hotline' => $hotline,
		];

		// print_object($config);
		return $stringparams;
	}
}

function local_thlib_secondsToTime($seconds) {

	$dtF = new \DateTime('@0');
	$dtT = new \DateTime("@$seconds");

	$lang = current_language();
	// global $lang;
	if ($lang == 'vi') {
		return $dtF->diff($dtT)->format('%a ngày, %h giờ, %i phút và %s giây');
	} else {
		return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
	}

}

function get_course_qaaps($courseid, $time_from, $time_to) {
	global $DB;
	$sql = 'SELECT {qaapairs}.*
			from {qaapairs}
			join {qaa}
			on {qaa}.id = {qaapairs}.qaaid
			join {course}
			on {course}.id = {qaa}.course
			where {course}.id = :courseid
			AND timecreatedquestion >= :time_from AND timecreatedquestion <= :time_to';

	return $DB->get_records_sql($sql, ['courseid' => $courseid, 'time_from' => $time_from, 'time_to' => $time_to]);
}

function generate_download_excel($downloadname, $rows, $sheetname) {
	global $CFG;

	require_once $CFG->libdir . '/excellib.class.php';

	$workbook = new MoodleExcelWorkbook(clean_filename($downloadname));

	$myxls = $workbook->add_worksheet($sheetname);
	$rowcount = 0;
	foreach ($rows as $row) {
		foreach ($row as $index => $content) {
			$myxls->write($rowcount, $index, $content);
		}
		$rowcount++;
	}

	$workbook->close();

	return $workbook;
}

function generate_download_csv($downloadname, $rows) {
	global $CFG;

	require_once $CFG->libdir . '/csvlib.class.php';

	$workbook = new csv_export_writer();
	$workbook->set_filename($downloadname);
	foreach ($rows as $row) {

		$workbook->add_data($row);
	}

	$workbook->download_file();

	return $workbook;
}

function my_get_enrolled_users($courseid, $cohortid, $timefrom, $timeend, $orderby = null, $limitfrom = 0, $limitnum = 0) {
	global $DB;

	$sql = "SELECT u.*
			FROM {user} u
			JOIN (SELECT DISTINCT eu1_u.*
				FROM {user} eu1_u
				JOIN {user_enrolments} ej1_ue
				ON ej1_ue.userid = eu1_u.id
				JOIN {enrol} ej1_e
				ON (ej1_e.id = ej1_ue.enrolid AND ej1_e.courseid = :courseid)
				JOIN {role_assignments}
				ON ({role_assignments}.userid = eu1_u.id AND {role_assignments}.roleid = 5)
				JOIN {context}
				ON {context}.instanceid = ej1_e.courseid AND {context}.id = {role_assignments}.contextid
				WHERE eu1_u.deleted = 0
				AND ((ej1_ue.timestart > :timefrom1 and ej1_ue.timestart!=0) OR (ej1_ue.timestart = 0 AND ej1_ue.timecreated > :timefrom2))
             	AND ((ej1_ue.timestart < :timeend1 and ej1_ue.timestart!=0) OR (ej1_ue.timestart = 0 AND ej1_ue.timecreated < :timeend2))
             	AND ej1_e.enrol ='cohort' and ej1_e.customint1 = :cohortid
             	) je
			ON je.id = u.id
			WHERE u.deleted = 0";

	$params = array('courseid' => $courseid, 'timefrom1' => $timefrom, 'timefrom1' => $timefrom,
		'timefrom2' => $timefrom, 'timeend1' => $timeend, 'timeend2' => $timeend, 'cohortid' => $cohortid);

	if ($orderby) {
		$sql = "$sql ORDER BY $orderby";
	} else {
		list($sort, $sortparams) = users_order_by_sql('u');
		$sql = "$sql ORDER BY $sort";
		$params = array_merge($params, $sortparams);
	}

	return $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
}

function get_courses_enrolled_by_cohort($cohort_id) {
	global $DB;

	$sql_courses_by_cohort = "SELECT {course}.id ,{course}.fullname , {cohort}.name, {cohort}.id as cohortid
        from {enrol}
        inner join {course}
        on {enrol}.courseid = {course}.id
        inner join {cohort}
        on {cohort}.id = {enrol}.customint1
        where {enrol}.enrol='cohort' and {cohort}.id= ?
        order by {course}.startdate, {course}.fullname";

	$course_bycohortsync = $DB->get_records_sql($sql_courses_by_cohort, [$cohort_id]);
	return $course_bycohortsync;
}

function get_userid_fullname($userid) {
	global $DB;
	$userfields = 'id, ' . get_all_user_name_fields(true);
	$user = $DB->get_record('user', array('id' => $userid), $userfields);
	return fullname($user);
}

function get_userid_form($mform, $sortorder = null, $required = false) {
	global $DB, $COURSE, $CFG;
	// $context = context_course::instance($COURSE->id);
	// $userfields = get_extra_user_fields($context);

	$extra = array_filter(explode(',', $CFG->showuseridentity));
	$userfields = array_values($extra);

	$usernamefield = get_all_user_name_fields();
	$usernamefield = implode(",", $usernamefield);
	$alluserfields = "id," . $usernamefield;

	if (count($userfields) > 0) {
		$alluserfields .= "," . implode(',', $userfields);
	}

	$alluserfields .= "," . "email";

	$users = $DB->get_records('user', array('deleted' => 0), $sortorder, $alluserfields);
	$choice = array();
	$choice[''] = '';

	foreach ($users as $key => $value) {
		$fullname = fullname($value);
		$users[$key]->fullname = $fullname;
		$fullname = html_writer::tag("span", $fullname);

		$extraf = array();
		foreach ($userfields as $key => $uf) {
			$v = $value->$uf;
			if (!is_null($v) && $v !== '') {
				$extraf[] = $v;
			}
		}
		$extraf = implode(", ", $extraf);
		$extraf = html_writer::tag("small", $extraf);
		$extraf = html_writer::tag("span", $extraf);

		$val = html_writer::tag("span", $fullname . ", " . $extraf);

		$choice[$value->id] = $val;
	}
	$options = array(
		'multiple' => true,
		'noselectionstring' => get_string('no_selection', 'local_thlib'),
	);

	$element = $mform->addElement('autocomplete', 'userid', get_string('search_user', 'local_thlib'), $choice, $options);
	if ($required) {
		$attributes = $element->getAttributes() + ['required' => 'true', 'class' => 'custom_required'];
		$element->setAttributes($attributes);
	}
	return $users;
}

function get_allcourseid_form($mform) {
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
		'noselectionstring' => get_string('no_selection', 'local_thlib'),
	);
	$mform->addElement('autocomplete', 'courseidarr', get_string('selectcourse', 'local_thlib'), $choice, $options);
	return $courses;
}

function get_all_cohort($field = 'id,name', $sortorder = 'name ASC') {
	global $DB;
	return $DB->get_records('cohort', null, $sortorder, $field);
}

function rebuild_date($return) {

	$lang = array();
	$lang['sun'] = 'CN';
	$lang['mon'] = 'T2';
	$lang['tue'] = 'T3';
	$lang['wed'] = 'T4';
	$lang['thu'] = 'T5';
	$lang['fri'] = 'T6';
	$lang['sat'] = 'T7';
	$lang['sunday'] = 'Chủ nhật';
	$lang['monday'] = 'Thứ hai';
	$lang['tuesday'] = 'Thứ ba';
	$lang['wednesday'] = 'Thứ tư';
	$lang['thursday'] = 'Thứ năm';
	$lang['friday'] = 'Thứ sáu';
	$lang['saturday'] = 'Thứ bảy';
	$lang['january'] = 'Tháng Một';
	$lang['february'] = 'Tháng Hai';
	$lang['march'] = 'Tháng Ba';
	$lang['april'] = 'Tháng Tư';
	$lang['may'] = 'Tháng Năm';
	$lang['june'] = 'Tháng Sáu';
	$lang['july'] = 'Tháng Bảy';
	$lang['august'] = 'Tháng Tám';
	$lang['september'] = 'Tháng Chín';
	$lang['october'] = 'Tháng Mười';
	$lang['november'] = 'Tháng Mười một';
	$lang['december'] = 'Tháng Mười hai';
	$lang['jan'] = 'T01';
	$lang['feb'] = 'T02';
	$lang['mar'] = 'T03';
	$lang['apr'] = 'T04';
	$lang['may2'] = 'T05';
	$lang['jun'] = 'T06';
	$lang['jul'] = 'T07';
	$lang['aug'] = 'T08';
	$lang['sep'] = 'T09';
	$lang['oct'] = 'T10';
	$lang['nov'] = 'T11';
	$lang['dec'] = 'T12';

	// $format = str_replace("r", "D, d M Y H:i:s O", $format);
	// $format = str_replace(array("D", "M"), array("[D]", "[M]"), $format);
	// $return = date($format, $time);

	$replaces = array(
		'/\[Sun\](\W|$)/' => $lang['sun'] . "$1",
		'/\[Mon\](\W|$)/' => $lang['mon'] . "$1",
		'/\[Tue\](\W|$)/' => $lang['tue'] . "$1",
		'/\[Wed\](\W|$)/' => $lang['wed'] . "$1",
		'/\[Thu\](\W|$)/' => $lang['thu'] . "$1",
		'/\[Fri\](\W|$)/' => $lang['fri'] . "$1",
		'/\[Sat\](\W|$)/' => $lang['sat'] . "$1",
		'/\[Jan\](\W|$)/' => $lang['jan'] . "$1",
		'/\[Feb\](\W|$)/' => $lang['feb'] . "$1",
		'/\[Mar\](\W|$)/' => $lang['mar'] . "$1",
		'/\[Apr\](\W|$)/' => $lang['apr'] . "$1",
		'/\[May\](\W|$)/' => $lang['may2'] . "$1",
		'/\[Jun\](\W|$)/' => $lang['jun'] . "$1",
		'/\[Jul\](\W|$)/' => $lang['jul'] . "$1",
		'/\[Aug\](\W|$)/' => $lang['aug'] . "$1",
		'/\[Sep\](\W|$)/' => $lang['sep'] . "$1",
		'/\[Oct\](\W|$)/' => $lang['oct'] . "$1",
		'/\[Nov\](\W|$)/' => $lang['nov'] . "$1",
		'/\[Dec\](\W|$)/' => $lang['dec'] . "$1",
		'/Sunday(\W|$)/' => $lang['sunday'] . "$1",
		'/Monday(\W|$)/' => $lang['monday'] . "$1",
		'/Tuesday(\W|$)/' => $lang['tuesday'] . "$1",
		'/Wednesday(\W|$)/' => $lang['wednesday'] . "$1",
		'/Thursday(\W|$)/' => $lang['thursday'] . "$1",
		'/Friday(\W|$)/' => $lang['friday'] . "$1",
		'/Saturday(\W|$)/' => $lang['saturday'] . "$1",
		'/January(\W|$)/' => $lang['january'] . "$1",
		'/February(\W|$)/' => $lang['february'] . "$1",
		'/March(\W|$)/' => $lang['march'] . "$1",
		'/April(\W|$)/' => $lang['april'] . "$1",
		'/May(\W|$)/' => $lang['may'] . "$1",
		'/June(\W|$)/' => $lang['june'] . "$1",
		'/July(\W|$)/' => $lang['july'] . "$1",
		'/August(\W|$)/' => $lang['august'] . "$1",
		'/September(\W|$)/' => $lang['september'] . "$1",
		'/October(\W|$)/' => $lang['october'] . "$1",
		'/November(\W|$)/' => $lang['november'] . "$1",
		'/December(\W|$)/' => $lang['december'] . "$1");

	return preg_replace(array_keys($replaces), array_values($replaces), $return);
}

function get_datetime($time, $format = '') {
	$lang = current_language();
	// global $lang;
	if ($lang == 'vi') {
		return rebuild_date(userdate($time, $format));
	} else {
		return userdate($time, $format);
	}
}

function get_userid_filtered_by_makhoa($makhoa = null, $user_status = thlib::USER_STATUS_ALL) {

	global $DB;

	$config = get_config('local_thlib');
	$mk = $config->enrollmentcourseshortname;

	$shortnamearr = explode(",", $mk);
	if (count($shortnamearr) > 0) {
		list($insql, $inparams) = $DB->get_in_or_equal($shortnamearr);
	} else {
		list($insql, $inparams) = $DB->get_in_or_equal(['']);
	}

	$sortorder = "lastname,firstname";
	if ($config->sortorder == 1) {
		$sortorder = "firstname,lastname";
	}

	$wheresql_userstatus = '';
	if ($user_status == thlib::USER_STATUS_ACTIVE) {
		$wheresql_userstatus .= 'and us.suspended = 0';
	} else if ($user_status == thlib::USER_STATUS_SUPPENDED) {
		$wheresql_userstatus .= 'and us.suspended = 1';
	}

	$sql = "SELECT  us.id as userid, us.firstname,us.lastname, infodata.data
		from {user} us
		inner join {user_info_data} infodata
		on us.id = infodata.userid and us.deleted = 0 $wheresql_userstatus
		inner join {user_info_field}
		on {user_info_field}.shortname $insql
		and infodata.fieldid = {user_info_field}.id
		and infodata.data = '$makhoa'
		group by userid
		order by $sortorder";

	$records = $DB->get_records_sql($sql, $inparams);
	if ($records) {
		return array_keys($records);
	}
	return [];
}

function get_userid_filtered_by_malop($malop = null, $user_status = thlib::USER_STATUS_ALL) {

	global $DB;

	$config = get_config('local_thlib');
	$ml = $config->classcodeshortname;

	$shortnamearr = explode(",", $ml);
	if (count($shortnamearr) > 0) {
		list($insql, $inparams) = $DB->get_in_or_equal($shortnamearr);
	} else {
		list($insql, $inparams) = $DB->get_in_or_equal(['']);
	}

	$sortorder = "lastname,firstname";
	if ($config->sortorder == 1) {
		$sortorder = "firstname,lastname";
	}

	$wheresql_userstatus = '';
	if ($user_status == thlib::USER_STATUS_ACTIVE) {
		$wheresql_userstatus .= 'and us.suspended = 0';
	} else if ($user_status == thlib::USER_STATUS_SUPPENDED) {
		$wheresql_userstatus .= 'and us.suspended = 1';
	}

	$sql = "SELECT  us.id as userid, us.firstname,us.lastname, infodata.data
		from {user} us
		inner join {user_info_data} infodata
		on us.id = infodata.userid and us.deleted = 0 $wheresql_userstatus
		inner join {user_info_field}
		on {user_info_field}.shortname $insql
		and infodata.fieldid = {user_info_field}.id
		and infodata.data = '$malop'
		group by userid
		order by $sortorder";

	$records = $DB->get_records_sql($sql, $inparams);

	if ($records) {
		return array_keys($records);
	}
	return [];
}

function get_user_filtered_from_arrayof_makhoa_malop($makhoaarr = null, $maloparr = null, $useridarr_op = null, $user_status = thlib::USER_STATUS_ALL) {
	global $DB;

	$config = get_config('local_thlib');
	$mk = $config->enrollmentcourseshortname;
	$ml = $config->classcodeshortname;

	$shortnamearr = explode(",", $ml);
	if (count($shortnamearr) > 0) {
		list($insql_ml, $inparams_ml) = $DB->get_in_or_equal($shortnamearr);
	} else {
		list($insql_ml, $inparams_ml) = $DB->get_in_or_equal(['']);
	}

	$shortnamearr = explode(",", $mk);
	if (count($shortnamearr) > 0) {
		list($insql_mk, $inparams_mk) = $DB->get_in_or_equal($shortnamearr);
	} else {
		list($insql_mk, $inparams_mk) = $DB->get_in_or_equal(['']);
	}

	$sortorder = "lastname,firstname";
	if ($config->sortorder == 1) {
		$sortorder = "firstname,lastname";
	}

	$wheresql_userstatus = '';
	if ($user_status == thlib::USER_STATUS_ACTIVE) {
		$wheresql_userstatus .= 'and us.suspended = 0';
	} else if ($user_status == thlib::USER_STATUS_SUPPENDED) {
		$wheresql_userstatus .= 'and us.suspended = 1';
	}

	$intersect = false;
	$userid_arr = [];
	if ($useridarr_op && sizeof($useridarr_op)) {

		$userid_arr = array_values($useridarr_op);

		list($insql_users, $inparams_users) = $DB->get_in_or_equal($userid_arr);

		$sql = null;
		if ($user_status == thlib::USER_STATUS_ACTIVE) {
			$sql = "select id from {user} where id $insql_users and suspended=0";
		} else if ($user_status == thlib::USER_STATUS_SUPPENDED) {
			$sql = "select id from {user} where id $insql_users and suspended=1";
		}
		if ($sql) {
			$records = $DB->get_records_sql($sql, $inparams_users);
			$userid_arr = array_keys($records);
		}

		$intersect = true;

	}

	if (sizeof($makhoaarr)) {

		list($insql_makhoa, $inparams_makhoa) = $DB->get_in_or_equal($makhoaarr);

		$sql = "SELECT  us.id as userid, us.firstname,us.lastname, infodata.data
		from {user} us
		inner join {user_info_data} infodata
		on us.id = infodata.userid $wheresql_userstatus and us.deleted = 0
		inner join {user_info_field}
		on {user_info_field}.shortname $insql_mk
		and infodata.fieldid = {user_info_field}.id
		and infodata.data $insql_makhoa
		group by userid
		order by $sortorder";

		$param = array_merge($inparams_mk, $inparams_makhoa);
		$records = $DB->get_records_sql($sql, $param);
		$userid_khoa = array_keys($records);

		if ($intersect) {
			$userid_arr = array_intersect($userid_khoa, $userid_arr);
		} else {
			$userid_arr = $userid_khoa;
		}

		$intersect = true;
	}

	if ($maloparr && sizeof($maloparr)) {
		list($insql_malop, $inparams_malop) = $DB->get_in_or_equal($maloparr);

		$sql = "SELECT  us.id as userid, us.firstname,us.lastname, infodata.data
		from {user} us
		inner join {user_info_data} infodata
		on us.id = infodata.userid $wheresql_userstatus and us.deleted = 0
		inner join {user_info_field}
		on {user_info_field}.shortname $insql_ml
		and infodata.fieldid = {user_info_field}.id
		and infodata.data $insql_malop
		group by userid
		order by $sortorder";

		$param = array_merge($inparams_ml, $inparams_malop);
		$records = $DB->get_records_sql($sql, $param);
		$userid_lop = array_keys($records);

		if ($intersect) {
			$userid_arr = array_intersect($userid_lop, $userid_arr);
		} else {
			$userid_arr = $userid_lop;
		}
	}

	return $userid_arr;
}

function get_user_filtered($makhoa = null, $malop = null, $userid = null, $user_status = thlib::USER_STATUS_ALL) {
	$userid_arr = [];

	$intersect = false;
	if ($userid && $userid != 0) {
		$userid_arr[$userid] = $userid;
		$intersect = true;
	}

	if ($makhoa && $makhoa != "") {

		$userid_khoa = get_userid_filtered_by_makhoa($makhoa, $user_status);

		if ($intersect) {
			$userid_arr = array_intersect($userid_khoa, $userid_arr);
		} else {
			$userid_arr = $userid_khoa;
		}
		$intersect = true;
	}

	if ($malop && $malop != "") {
		// $malop = $maloparr[$malopid]->data;
		$userid_lop = get_userid_filtered_by_malop($malop, $user_status);
		if ($intersect) {
			$userid_arr = array_intersect($userid_lop, $userid_arr);
		} else {
			$userid_arr = $userid_lop;
		}
		$intersect = true;
	}
	$result = [];
	foreach ($userid_arr as $key => $value) {
		$result[$value] = $value;
	}
	return $result;
}

/**
 * Gets the left rows.
 *
 * @param      <type>  $userid_arr  The userid_arr
 * @param      <type>  $user_arr    The user_arr
 *
 * @return     <type>  array of left_html_rows and left_export_rows.
 */
function get_left_rows($userid_arr, $user_arr, $config = null) {
	global $DB;

	if ($config == null) {
		$config = get_config('local_thlib');
	}

	$leftrows = [];
	$rows_ex_left = array();
	$row_ex = array();
	$headrows = new html_table_row();
	$cell = new html_table_cell(get_string('no_', 'local_thlib'));
	$cell->attributes['class'] = 'cell headingcell';
	$row_ex[] = get_string('no_', 'local_thlib');
	$cell->header = true;
	$headrows->cells[] = $cell;
	$userDBFields = [];

	$profile_fields = profile_get_custom_fields();
	$user_field = array();
	foreach ($profile_fields as $key => $value) {
		$user_field[$value->shortname] = $value->name;
	}

	if ($config->custom_fields1) {
		$custom_fields1 = array_map('trim', explode(",", $config->custom_fields1));
		foreach ($custom_fields1 as $kcf => $cf) {
			if (array_key_exists($cf, $user_field)) {
				$cell = new html_table_cell($user_field[$cf]);
				$row_ex[] = $user_field[$cf];
				$cell->attributes['class'] = 'cell headingcell';
				$cell->header = true;
				$headrows->cells[] = $cell;
			} else if (in_array($cf, thlib::USER_FIELD)) {
				$userDBFields[] = $cf;
				$cell = new html_table_cell(get_string($cf));
				$row_ex[] = get_string($cf);
				$cell->attributes['class'] = 'cell headingcell';
				$cell->header = true;
				$headrows->cells[] = $cell;
			}
		}
	}

	$cell = new html_table_cell(get_string('firstname') . '/' . get_string('lastname'));
	$cell->attributes['class'] = 'cell headingcell';
	$row_ex[] = get_string('firstname') . '/' . get_string('lastname');
	$cell->header = true;
	$headrows->cells[] = $cell;

	if ($config->custom_fields2) {
		$custom_fields2 = array_map('trim', explode(",", $config->custom_fields2));
		foreach ($custom_fields2 as $kcf => $cf) {
			if (array_key_exists($cf, $user_field)) {
				$cell = new html_table_cell($user_field[$cf]);
				$row_ex[] = $user_field[$cf];
				$cell->attributes['class'] = 'cell headingcell';
				$cell->header = true;
				$headrows->cells[] = $cell;
			} else if (in_array($cf, thlib::USER_FIELD)) {
				$userDBFields[] = $cf;
				$cell = new html_table_cell(get_string($cf));
				$row_ex[] = get_string($cf);
				$cell->attributes['class'] = 'cell headingcell';
				$cell->header = true;
				$headrows->cells[] = $cell;
			}
		}
	}

	$leftrows[] = $headrows;
	$rows_ex_left[] = $row_ex;

	if (count($userDBFields) > 0) {
		list($insql, $inparams) = $DB->get_in_or_equal($userid_arr);
	} else {
		list($insql, $inparams) = $DB->get_in_or_equal(['']);
	}

	if (!empty($userDBFields)) {
		$selectfield = implode(',', $userDBFields);
		if (!in_array('id', $userDBFields)) {
			$selectfield = 'id,' . $selectfield;
		}
		$sql = "select $selectfield  from {user} where {user}.id $insql";
		$userrecords = $DB->get_records_sql($sql, $inparams);
	}

	$count = 0;
	foreach ($userid_arr as $key => $userid) {
		$row_ex = array();

		$count++;
		$row = new html_table_row();

		if (property_exists($user_arr[$userid], 'fullname') == false) {
			$user_arr[$userid]->fullname = fullname($user_arr[$userid]);
		} else {
		}

		$fullname = $user_arr[$userid]->fullname;
		if (!empty($userrecords)) {
			$userrecord = $userrecords[$userid];
		}

		$cell = new html_table_cell($count);
		$row->cells[] = $cell;
		$row_ex[] = $count;

		$userfielddatas = profile_get_user_fields_with_data($userid);
		$fielddata = array();
		foreach ($userfielddatas as $fd) {
			if ($fd->field->datatype == 'datetime') {
				$fielddata[$fd->field->shortname] = get_datetime($fd->field->data, "%d-%m-%Y");
			} else {
				$fielddata[$fd->field->shortname] = $fd->field->data;
			}

		}

		if (isset($custom_fields1) && is_array($custom_fields1)) {
			foreach ($custom_fields1 as $kcf => $cfshortname) {
				if (array_key_exists($cfshortname, $fielddata)) {
					$cfvalue = $fielddata[$cfshortname];
					$row_ex[] = $cfvalue;
					$cell = new html_table_cell($cfvalue);
					$cell->attributes['class'] = 'user';
					$row->cells[] = $cell;
				} else if (in_array($cfshortname, thlib::USER_FIELD)) {
					$cfvalue = $userrecord->$cfshortname;
					$row_ex[] = $cfvalue;
					$cell = new html_table_cell($cfvalue);
					$cell->attributes['class'] = 'user';
					$row->cells[] = $cell;
				}
			}
		}

		$cell = new html_table_cell($fullname);
		$cell->text = html_writer::link(new moodle_url('/user/view.php', array('id' => $userid)), $fullname, array(
			'class' => 'username',
		));
		$cell->attributes['class'] = 'user';
		$cell->attributes['data-order'] = $cell->attributes['data-search'] = $fullname;
		$row->cells[] = $cell;
		$row_ex[] = $fullname;

		if (isset($custom_fields2) && is_array($custom_fields2)) {
			foreach ($custom_fields2 as $kcf => $cfshortname) {
				if (array_key_exists($cfshortname, $fielddata)) {
					$cfvalue = $fielddata[$cfshortname];
					$row_ex[] = $cfvalue;
					$cell = new html_table_cell($cfvalue);
					$cell->attributes['class'] = 'user';
					$row->cells[] = $cell;
				} else if (in_array($cfshortname, thlib::USER_FIELD)) {
					$cfvalue = $userrecord->$cfshortname;
					$row_ex[] = $cfvalue;
					$cell = new html_table_cell($cfvalue);
					$cell->attributes['class'] = 'user';
					$row->cells[] = $cell;
				}
			}
		}

		$leftrows[$userid] = $row;
		$rows_ex_left[$userid] = $row_ex;
	}

	return array($leftrows, $rows_ex_left);
}
