<?php

require_once "{$CFG->libdir}/formslib.php";
require_once $CFG->dirroot . '/local/thlib/lib.php';

class th_gradereport_form extends th_form {

	function definition() {
		global $DB, $COURSE;
		$mform = $this->_form;

		$mform->addElement('header', 'displayinfo', get_string('textfields', 'local_thlib'));
		$mform = $this->_form;

		$this->add_show_option_radio();

		$this->add_makhoa_malop_user_filter();

		// $mform->addRule('show_option', null, 'required', null, 'server', false, true);
		$this->add_user_status_option_radio();
		$mform->setDefault('show_option', 0);

		$this->add_from_to_datetime();

		$radioarray = array();
		$radioarray[] = &$mform->createElement('radio', 'summary_detail', '', get_string('summary', 'block_th_gradereport'), 0);
		$radioarray[] = &$mform->createElement('radio', 'summary_detail', '', get_string('detail', 'block_th_gradereport'), 1);

		$element = $mform->addGroup($radioarray, 'summary_detail', get_string('summary_detail', 'block_th_gradereport'), array(''), false);
		$attributes = $element->_attributes = ['class' => 'custom_required'];
		$mform->setDefault('summary_detail', DETAIL_MODE);

		$this->add_action_buttons(false, get_string("submmit", 'local_thlib'));
	}

	function add_user_status_option_radio() {
		$mform = $this->_form;
		$radioarray = array();
		$radioarray[] = &$mform->createElement('radio', 'user_status', '', get_string('radio_all', 'local_thlib'), 0);
		$radioarray[] = &$mform->createElement('radio', 'user_status', '', get_string('radio_active', 'local_thlib'), 1);
		$radioarray[] = &$mform->createElement('radio', 'user_status', '', get_string('radio_suppend', 'local_thlib'), 2);

		$element = $mform->addGroup($radioarray, 'user_status', get_string('searchoption', 'local_thlib'), array(''), false);
		$attributes = $element->_attributes = ['class' => 'custom_required'];
		$element->setAttributes($attributes);
	}

	function add_show_option_radio() {
		$mform = $this->_form;
		$radioarray = array();
		$radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('radiomakhoa', 'local_thlib'), 0);
		$radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('radiomalop', 'local_thlib'), 1);
		$radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('radiouser', 'local_thlib'), 2);
		// $radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('radiocourse', 'local_thlib'), 3);

		$element = $mform->addGroup($radioarray, 'show_option', get_string('searchoption', 'local_thlib'), array(''), false);
		$attributes = $element->_attributes = ['class' => 'custom_required'];
		$element->setAttributes($attributes);

		// $mform->addRule('show_option', null, 'required', null, 'server', false, true);

		$mform->setDefault('show_option', 0);
	}

	function add_from_to_datetime() {
		$mform = $this->_form;
		$element = $mform->addElement('date_selector', 'time_from', get_string('fromdate', 'local_thlib'), array('class' => 'ssssssss'));
		$attributes = $element->_attributes = ['class' => 'custom_required'];
		$element->setAttributes($attributes);

		$element = $mform->addElement('date_selector', 'time_to', get_string('todate', 'local_thlib'));
		$attributes = $element->_attributes = ['class' => 'custom_required'];
		$element->setAttributes($attributes);
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

		$this->makhoaarr = get_profile_data($enrollmentcourseshortname);
		$this->maloparr = get_profile_data($classcodeshortname);

		// Ma Khoa Filter
		$choice = array();
		$choice[''] = '';
		foreach ($this->makhoaarr as $key => $value) {
			$choice[$value->id] = $value->data;
		}

		$options = array(
			'multiple' => true,
			'noselectionstring' => get_string('no_selection', 'local_thlib'),
		);

		$element = $mform->addElement('autocomplete', 'makhoaid', get_string('makhoa', 'local_thlib'), $choice, $options);
		// $attributes = $element->getAttributes() + ['class' => 'custom_required'];
		// $element->setAttributes($attributes);
		// Ma lop filter
		$choice = array();
		$choice[''] = '';
		foreach ($this->maloparr as $key => $value) {
			$choice[$value->id] = $value->data;
		}

		$options = array(
			'multiple' => true,
			'noselectionstring' => get_string('no_selection', 'local_thlib'),
		);

		$element = $mform->addElement('autocomplete', 'malopid', get_string('malop', 'local_thlib'), $choice, $options);
		// $attributes = $element->getAttributes() + ['class' => 'custom_required'];
		// $element->setAttributes($attributes);
		// $mform->addRule('malopid', null, 'required', null, 'server');
		// User filter
		$this->user_arr = get_userid_form($mform, $sortorder, false);

		$this->course_arr = get_allcourseid_form($mform);

		$mform->disabledIf('makhoaid', 'show_option', 'neq', '0');
		$mform->disabledIf('malopid', 'show_option', 'neq', '1');
		$mform->disabledIf('userid', 'show_option', 'neq', '2');
		// $mform->disabledIf('courseidarr', 'show_option', 'neq', '3');

		$mform->hideif('makhoaid', 'show_option', 'neq', '0');
		$mform->hideif('malopid', 'show_option', 'neq', '1');
		$mform->hideif('userid', 'show_option', 'neq', '2');
		// $mform->hideif('courseidarr', 'show_option', 'neq', '3');
	}

	function validation($data, $files) {
		return $this->validation_makhoa_malop_user($data, $files);
	}

	function validation_makhoa_malop_user($data, $files) {

		if ($data['show_option'] == 0 && empty($data['makhoaid']) && empty($data['courseidarr'])) {
			return array('makhoaid' => get_string('warningmakhoamamonhoc', 'local_thlib'), 'courseidarr' => get_string('warningmakhoamamonhoc', 'local_thlib'));
		}

		if ($data['show_option'] == 1 && empty($data['malopid']) && empty($data['courseidarr'])) {
			return array('malopid' => get_string('warningmalopmamonhoc', 'local_thlib'), 'courseidarr' => get_string('warningmakhoamamonhoc', 'local_thlib'));
		}

		if ($data['show_option'] == 2 && empty($data['userid']) && empty($data['courseidarr'])) {
			return array('userid' => get_string('warningusermamonhoc', 'local_thlib'), 'courseidarr' => get_string('warningmakhoamamonhoc', 'local_thlib'));
		}
	}
}
