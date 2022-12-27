<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once $CFG->libdir . '/formslib.php';
require_once 'blocklib.php';

class confirm_form extends moodleform {

	protected function definition() {
		global $SESSION;

		$th_clone_csvkey = $this->_customdata['th_clone_csvkey'];

		$mform = $this->_form;

		$mform->addElement('hidden', 'key');
		$mform->setType('key', PARAM_RAW);
		$mform->setDefault('key', $th_clone_csvkey);

		// Check if we want to show the enrol user button.
		$showbutton = true;
		$checkedcourses = null;
		if (isset($SESSION->block_th_clone_csv) && array_key_exists($th_clone_csvkey, $SESSION->block_th_clone_csv)) {
			$checkedcourses = $SESSION->block_th_clone_csv[$th_clone_csvkey];
			if (isset($checkedcourses->validemailfound) && empty($checkedcourses->validemailfound) || $checkedcourses->validemailfound > 10) {
				$showbutton = false;
			}
		}

		// Only show the enrol user button if necessary.
		if ($showbutton) {

			$buttonstring = get_string('clone', 'block_th_clone_course');

			$this->add_action_buttons(true, $buttonstring);
		}
	}
}
