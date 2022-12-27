<?php

require_once $CFG->dirroot . '/lib/formslib.php';
require_once "{$CFG->libdir}/formslib.php";

class upload_gvcn_qlht_form extends moodleform
{
	public function definition()
	{
		global $DB;
		$mform = $this->_form;

		$link = "<a href='example_import.csv'>example.csv</a>";
		$mform->addElement('static', 'example', 'Tệp mẫu', $link);

		$mform->addElement('filepicker', 'data_file', get_string('file'));

		$mform->addRule('data_file', get_string('required'), 'required', null, 'client');

		$this->add_action_buttons(true, get_string('submit'));
	}

	public function validation($data, $files)
	{
		return array();
	}
}

class confirm_form2 extends moodleform {

	protected function definition() {
		global $SESSION;

		$th_upload_gvcn_qlht_key = $this->_customdata['th_upload_gvcn_qlht_key'];

		$mform = $this->_form;

		$mform->addElement('hidden', 'key');
		$mform->setType('key', PARAM_RAW);
		$mform->setDefault('key', $th_upload_gvcn_qlht_key);

		$showbutton = true;
		$checkedtimes = null;
		if (isset($SESSION->th_export_support_dcct) && array_key_exists($th_upload_gvcn_qlht_key, $SESSION->th_export_support_dcct)) {
			$checked = $SESSION->th_export_support_dcct[$th_upload_gvcn_qlht_key];
			if (isset($checked->valid_found) && empty($checked->valid_found)) {
				$showbutton = false;
			}
		}

		if ($showbutton) {
			$buttonstring = 'Thêm';
			$this->add_action_buttons(true, $buttonstring);
		}
	}
}