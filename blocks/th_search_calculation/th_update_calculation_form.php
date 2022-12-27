<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once $CFG->dirroot . '/lib/formslib.php';
require_once "{$CFG->libdir}/formslib.php";

class th_update_calculation_form extends moodleform {

	function definition() {

		$mform = $this->_form;

		$mform->addElement('header', 'displayinfo', get_string('filter'));

		$options1 = array(
		    'kt' => 'Điểm kiểm tra',
		    'tong' => 'Điểm tổng kết'
		);
		$mform->addElement('select', 'ma_diem', 'Chọn điểm', $options1);
		$mform->addRule('ma_diem', 'Không được bỏ trống', 'required', null, 'server');

		$mform->addElement('text', 'calculation', 'Công thức hiện tại');
		$mform->setType('calculation', PARAM_TEXT);
		$mform->addRule('calculation', 'Không được bỏ trống', 'required', null, 'server');
		$mform->addElement('text', 'calculation_new', 'Công thức mới');
		$mform->setType('calculation_new', PARAM_TEXT);
		$mform->addRule('calculation_new', 'Không được bỏ trống', 'required', null, 'server');

		$this->add_action_buttons(true, get_string('submit'));
	}

	function validation($data, $files) {
		return array();
	}
}

class confirm_form extends moodleform {

	protected function definition() {
		global $SESSION;

		$th_update_calculation_key = $this->_customdata['th_update_calculation_key'];

		$mform = $this->_form;

		$mform->addElement('hidden', 'key');
		$mform->setType('key', PARAM_RAW);
		$mform->setDefault('key', $th_update_calculation_key);

		// Check if we want to show the enrol user button.
		$showenrolebutton = true;
		$checked = null;
		if (isset($SESSION->th_update_calculation) && array_key_exists($th_update_calculation_key, $SESSION->th_update_calculation)) {
			$checked = $SESSION->th_update_calculation[$th_update_calculation_key];
			if (isset($checked->validfound) && empty($checked->validfound)) {
				$showenrolebutton = false;
			}
		}

		// Only show the enrol user button if necessary.
		if ($showenrolebutton) {

			$buttonstring = 'Gửi';

			$this->add_action_buttons(true, $buttonstring);
		}
	}
}
