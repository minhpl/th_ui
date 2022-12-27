<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once $CFG->libdir . '/formslib.php';
require_once 'blocklib.php';

class confirm_form extends moodleform {

	protected function definition() {
		global $SESSION;

		$th_override_csvkey = $this->_customdata['th_override_csvkey'];

		$mform = $this->_form;

		$mform->addElement('hidden', 'key');
		$mform->setType('key', PARAM_RAW);
		$mform->setDefault('key', $th_override_csvkey);

		$showbutton = true;
		$checkedtimes = null;
		if (isset($SESSION->block_th_override_csv) && array_key_exists($th_override_csvkey, $SESSION->block_th_override_csv)) {
			$checkedtimes = $SESSION->block_th_override_csv[$th_override_csvkey];
			if (isset($checkedtimes->valid_time_found) && empty($checkedtimes->valid_time_found)) {
				$showbutton = false;
			}
		}

		if ($showbutton) {

			$buttonstring = get_string('submit', 'block_th_bulk_override');

			$this->add_action_buttons(true, $buttonstring);
		}
	}
}
