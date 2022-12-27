<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once $CFG->libdir . '/formslib.php';

class th_quiz_grade_report_form extends moodleform {

	/**
	 * Form definition. Abstract method - always override!
	 */
	protected function definition() {
		global $CFG, $DB;

		$mform = $this->_form;

		$mform->addElement('header', 'displayinfo', get_string('filter'));

		// course start date
		$mform->addElement('date_selector', 'course_start_date', get_string('course_start_date', 'block_th_quiz_grade_report'));
		$this->add_makhoa_malop_user_filter();

		$this->_name = "grade";
		$this->_label = get_string('grade', 'block_th_quiz_grade_report');
		$objs = array();
		$operators = [get_string('less_than', 'block_th_quiz_grade_report'), get_string('greater_than', 'block_th_quiz_grade_report'), get_string('equal_to', 'block_th_quiz_grade_report')];

		$objs['op'] = $mform->createElement('select', $this->_name . "_op", null, $operators);
		$mform->setDefault($this->_name . "_op", 0);
		$objs['value'] = $mform->createElement('text', $this->_name, null);
		$mform->setDefault($this->_name, 5);

		$grp = &$mform->addElement('group', $this->_name . '_grp', $this->_label, $objs, '', false);
		$mform->setType($this->_name, PARAM_FLOAT);

		$mform->addElement('date_selector', 'start_date', get_string('start_date', 'block_th_quiz_grade_report'));
		$date = (new DateTime())->setTimestamp(usergetmidnight(time()));
		$date->modify('-7 day');
		$mform->setDefault('start_date', $date->getTimestamp());
		$mform->addElement('date_selector', 'end_date', get_string('end_date', 'block_th_quiz_grade_report'));

		$this->add_action_buttons(true, get_string('view'));
	}

	/**
	 * Get each of the rules to validate its own fields
	 *
	 * @param array $data array of ("fieldname"=>value) of submitted data
	 * @param array $files array of uploaded files "element_name"=>tmp_file_path
	 * @return array of "element_name"=>"error_description" if there are errors,
	 *         or an empty array if everything is OK (true allowed for backwards compatibility too).
	 */
	public function validation($data, $files) {

	}

	function get_profile_data($shortname) {

		global $DB;

		$shortnamearr = explode(",", $shortname);
		if (count($shortnamearr) > 0) {
			list($insql, $inparams) = $DB->get_in_or_equal($shortnamearr);
		} else {
			list($insql, $inparams) = $DB->get_in_or_equal(['']);
		}

		$sql = "SELECT distinct {user_info_data}.*
                from {user}
                inner join {user_info_data}
                on {user_info_data}.userid = {user}.id
                inner join {user_info_field}
                on {user_info_data}.fieldid = {user_info_field}.id
                where {user_info_field}.shortname $insql
                and {user_info_data}.data <> ''
                group by {user_info_data}.data";

		return $DB->get_records_sql($sql, $inparams);
	}

	function add_makhoa_malop_user_filter() {
		global $DB;

		$mform = $this->_form;

		$config = get_config('local_thlib');
		$sortorder = "lastname,firstname";
		if ($config->sortorder == 1) {
			$sortorder = "firstname,lastname";
		}

		$enrollmentcourseshortname = trim($config->enrollmentcourseshortname);
		$classcodeshortname = trim($config->classcodeshortname);

		$this->makhoaarr = $this->get_profile_data($enrollmentcourseshortname);

		// Ma Khoa Filter
		$choice = array();
		// $choice[''] = '';
		foreach ($this->makhoaarr as $key => $value) {
			$choice[$value->data] = $value->data;
		}

		$options = array(
			'multiple' => true,
			'noselectionstring' => get_string('no_select', 'block_th_quiz_grade_report'),
		);

		$element = $mform->addElement('autocomplete', 'makhoaid', get_string('course_code', 'block_th_quiz_grade_report'), $choice, $options);
		$mform->addRule('makhoaid', null, 'required', null, 'client');

	}
}
