<?php

require_once $CFG->dirroot . '/lib/formslib.php';

class th_bulk_enrol_groups_form extends moodleform
{

	function definition()
	{
		$mform = $this->_form;
		$mform->addElement('header', 'displayinfo', get_string('filter'));
		$link = "<a href='example.csv'>example.csv</a>";
		$mform->addElement('static', 'example', 'examplecsv', $link);
		$mform->addElement('filepicker', 'list_groups', get_string('file'));
		$this->add_action_buttons(true, get_string('submit'));
	}

	function validation($data, $files)
	{
		return array();
	}
}

class confirm_form extends moodleform
{

	protected function definition()
	{
		global $SESSION;
		$th_enrol_groups_csvkey = $this->_customdata['th_enrol_groups_csvkey'];
		$mform = $this->_form;
		$mform->addElement('hidden', 'key');
		$mform->setType('key', PARAM_RAW);
		$mform->setDefault('key', $th_enrol_groups_csvkey);

		$showbutton = true;
		$checked_groups = null;
		if (isset($SESSION->block_th_enrol_groups) && array_key_exists($th_enrol_groups_csvkey, $SESSION->block_th_enrol_groups)) {
			$checked_groups = $SESSION->block_th_enrol_groups[$th_enrol_groups_csvkey];
			if (isset($checked_groups->valid_groups_found) && empty($checked_groups->valid_groups_found)) {
				$showbutton = false;
			}
		}

		if ($showbutton) {
			$buttonstring = 'Gán học viên';
			$this->add_action_buttons(true, $buttonstring);
		}
	}
}