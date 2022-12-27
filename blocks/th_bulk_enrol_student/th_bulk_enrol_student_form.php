<?php

require_once $CFG->dirroot . '/lib/formslib.php';

class th_bulk_enrol_student_form extends moodleform {

	function definition() {

		$mform = $this->_form;
		$mform->addElement('header', 'displayinfo', get_string('filter'));
		$link = "<a href='example.csv'>example.csv</a>";
		$mform->addElement('static', 'example', 'example csv', $link);
		$mform->addElement('filepicker', 'list_students', get_string('file'));
		$this->add_action_buttons(true, get_string('submit'));
	}

	function validation($data, $files) {
		return array();
	}
}

class confirm_form2 extends moodleform {

	protected function definition() {
		global $SESSION;
		$th_bulkenrol_csvkey = $this->_customdata['th_bulkenrol_csvkey'];
		$mform = $this->_form;
		$mform->addElement('hidden', 'key');
		$mform->setType('key', PARAM_RAW);
		$mform->setDefault('key', $th_bulkenrol_csvkey);

		$showbutton = true;
		$checked_student = null;
		if (isset($SESSION->block_th_enrol_students) && array_key_exists($th_bulkenrol_csvkey, $SESSION->block_th_enrol_students)) {
			$checked_student = $SESSION->block_th_enrol_students[$th_bulkenrol_csvkey];
			if (isset($checked_student->validemailfound) && empty($checked_student->validemailfound)) {
				$showbutton = false;
			}
		}

		if ($showbutton) {
			$buttonstring = 'Gán học viên';
			$this->add_action_buttons(true, $buttonstring);
		}
	}
}