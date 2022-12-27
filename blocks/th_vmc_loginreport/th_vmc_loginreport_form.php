<?php
class th_vmc_loginreport_form extends moodleform {

	public function definition() {
		global $CFG, $DB, $COURSE;

		$mform = $this->_form;
		$mform->addElement('header', 'displayinfo', get_string('textfields', 'block_th_vmc_loginreport'));

		$mform = $this->_form;

		$radioarray = array();
		$radioarray[] = $mform->createElement('radio', 'show_option', '', get_string('course', 'block_th_vmc_loginreport'), '0');
		$radioarray[] = $mform->createElement('radio', 'show_option', '', get_string('student', 'block_th_vmc_loginreport'), '1');
		$mform->addGroup($radioarray, 'radioar', get_string('option', 'block_th_vmc_loginreport'), array(''), false);
		$mform->setDefault('show_option', '0');

		$config = get_config('local_thlib');
		$sortorder = "lastname,firstname";
		if ($config->sortorder == 1) {
			$sortorder = "firstname,lastname";
		}

		$this->user_arr = get_userid_form($mform, $sortorder, false);
		$this->course_arr = get_allcourseid_form($mform);

		$mform->disabledIf('courseidarr', 'show_option', 'eq', '1');
		$mform->disabledIf('userid', 'show_option', 'eq', '0');

		$mform->hideif('courseidarr', 'show_option', 'eq', '1');
		$mform->hideif('userid', 'show_option', 'eq', '0');

		$mform->addElement('submit', 'send', get_string('submmit', 'block_th_vmc_loginreport'));
	}
}