<?php
function get_userid_th_course_access_report_form($mform, $sortorder = null, $required = false) {
	global $DB, $COURSE, $CFG;

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
		'noselectionstring' => get_string('choose_a_user', 'block_th_course_access_report'),
	);

	$element = $mform->addElement('autocomplete', 'userid', get_string('user', 'block_th_course_access_report'), $choice, $options);
	if ($required) {
		$attributes = $element->getAttributes() + ['required' => 'true', 'class' => 'custom_required'];
		$element->setAttributes($attributes);
	}
	return $users;
}
