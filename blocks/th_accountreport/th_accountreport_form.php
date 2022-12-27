<?php
class th_accountreport_form extends moodleform {
	//Add elements to form
	public function definition() {
		$mform = $this->_form;
		$mform->addElement('header', 'displayinfo', get_string('textfields', 'block_th_accountreport'));
		$mform = $this->_form; // Don't forget the underscore!

		$mform->addElement('date_selector', 'startdate', get_string('from'));
		$date = (new DateTime())->setTimestamp(usergetmidnight(time()));
		$date->modify('-6 month');
		$mform->setDefault('startdate', $date->getTimestamp());
		$mform->addRule('startdate', '', 'required', null, 'client', false, false);

		$mform->addElement('date_selector', 'enddate', get_string('to'));
		$mform->addRule('enddate', '', 'required', null, 'client', false, false);

		$radioarray = array();
		$radioarray[] = $mform->createElement('radio', 'filter', '', get_string('day', 'block_th_accountreport'), 'day');
		$radioarray[] = $mform->createElement('radio', 'filter', '', get_string('week'), 'week');
		$radioarray[] = $mform->createElement('radio', 'filter', '', get_string('month'), 'month');
		$mform->addGroup($radioarray, 'radioar', '', array(' '), false);
		$mform->setDefault('filter', 'day');

		$mform->addElement('submit', 'send', get_string('find', 'block_th_accountreport'));
	}
}