<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once $CFG->libdir . '/formslib.php';
require_once 'blocklib.php';

class confirm_form extends moodleform {

	protected function definition() {
		global $SESSION;

		$th_bulkenrol_csvkey = $this->_customdata['th_bulkenrol_csv_key'];

		$mform = $this->_form;

		$mform->addElement('hidden', 'key');
		$mform->setType('key', PARAM_RAW);
		$mform->setDefault('key', $th_bulkenrol_csvkey);

		// Check if we want to show the enrol user button.
		$showenrolebutton = true;
		$checkedmails = null;
		if (isset($SESSION->th_bulkenrol_csv) && array_key_exists($th_bulkenrol_csvkey, $SESSION->th_bulkenrol_csv)) {
			$checkedmails = $SESSION->th_bulkenrol_csv[$th_bulkenrol_csvkey];
			if (isset($checkedmails->validemailfound) && empty($checkedmails->validemailfound)) {
				$showenrolebutton = false;
			}
		}

		// Only show the enrol user button if necessary.
		if ($showenrolebutton) {

			$buttonstring = get_string('enrol_users', 'block_th_bulk_enrol_student');

			$this->add_action_buttons(true, $buttonstring);
		}
	}
}
