<?php

require_once "{$CFG->libdir}/formslib.php";
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';

class th_loginreport_form extends th_form {

	function definition() {
		global $DB, $COURSE;

		$mform = $this->_form;

		$mform = $this->_form;
		$mform->addElement('header', 'displayinfo', get_string('textfields', 'local_thlib'));

		$this->add_show_option_radio();
		$this->add_makhoa_malop_user_filter();

		$this->add_user_status_option_radio();

		$config = get_config('block_th_loginreport');
		$roles_field = $config->roles_field;

		global $DB;
		$records = $DB->get_record("user_info_field", array('shortname' => $roles_field), 'id,param1,defaultdata');
		$param1 = $records->param1;
		$arr_roles = ['0' => get_string('all', 'block_th_loginreport')];
		$arr_roles = array_merge($arr_roles, explode("\n", $param1));

		$this->defaultdata_role = $records->defaultdata;
		$this->arr_roles = $arr_roles;
		$this->user_info_field_id = $records->id;

		$mform->addElement('select', "custom_role", get_string('roles', 'block_th_loginreport'), $arr_roles);
		$mform->disabledIf('custom_role', 'show_option', 'neq', '2');
		$mform->hideif('custom_role', 'show_option', 'neq', '2');

		$this->_name = "numlogin";
		$this->_label = get_string('numlogin', 'block_th_loginreport');
		$objs = array();
		$operators = [get_string('lessthan', 'local_thlib'), get_string('greaterthan', 'local_thlib'), get_string('equalto', 'local_thlib')];

		$objs['op'] = $mform->createElement('select', $this->_name . "_op", null, $operators);
		$mform->setDefault($this->_name . "_op", 0);
		$objs['value'] = $mform->createElement('text', $this->_name, null);
		$mform->setDefault($this->_name, 2);

		$grp = &$mform->addElement('group', $this->_name . '_grp', $this->_label, $objs, '', false);
		$mform->setType($this->_name, PARAM_RAW);

		// $mform->setAdvanced($this->_name . '_grp');
		$this->add_from_to_datetime();
		$this->add_action_buttons(false, get_string("submmit", 'block_th_loginreport'));
	}

	function add_makhoa_malop_user_filter() {
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
			'multiple' => false,
			'noselectionstring' => get_string('no_selection', 'local_thlib'),
		);

		$element = $mform->addElement('autocomplete', 'makhoaid', get_string('makhoa', 'local_thlib'), $choice, $options, array('classs' => 'cohort-cohort'));
		$attributes = $element->getAttributes() + ['id' => 'myid', 'class' => 'custom_required'];

		$element->setAttributes($attributes);
		// Ma lop filter
		$choice = array();
		$choice[''] = '';
		foreach ($this->maloparr as $key => $value) {
			$choice[$value->id] = $value->data;
		}

		$options = array(
			'multiple' => false,
			'noselectionstring' => get_string('no_selection', 'local_thlib'),
		);

		$element = $mform->addElement('autocomplete', 'malopid', get_string('malop', 'local_thlib'), $choice, $options, array('classs' => 'cohort-cohort'));
		$attributes = $element->getAttributes() + ['class' => 'custom_required'];
		$element->setAttributes($attributes);
		// User filter
		$this->user_arr = get_userid_form($mform, $sortorder);

		$mform->disabledIf('makhoaid', 'show_option', 'neq', '0');
		$mform->disabledIf('malopid', 'show_option', 'neq', '1');
		$mform->disabledIf('userid', 'show_option', 'neq', '2');

		$mform->hideif('makhoaid', 'show_option', 'neq', '0');
		$mform->hideif('malopid', 'show_option', 'neq', '1');
		$mform->hideif('userid', 'show_option', 'neq', '2');
	}

	function validation($data, $files) {
		if ($data['show_option'] == 0 && empty($data['makhoaid'])) {
			return array('makhoaid' => get_string('err_required', 'form'));
		}

		if ($data['show_option'] == 1 && empty($data['malopid'])) {
			return array('malopid' => get_string('err_required', 'form'));
		}
	}

}
