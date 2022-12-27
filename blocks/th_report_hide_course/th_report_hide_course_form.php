<?php

require_once $CFG->dirroot . '/lib/formslib.php';
require_once "{$CFG->libdir}/formslib.php";

class th_report_hide_course_form extends moodleform {

	function definition() {
		global $DB;
		$mform = $this->_form;

		$mform->addElement('header', 'displayinfo', get_string('filter'));

		$mform->addElement('date_selector', 'startdate', 'Chọn ngày bắt đầu khóa học');
		
		$this->add_action_buttons(true, 'Submit');
	}

	function validation($data, $files) {
		return array();
	}

}
