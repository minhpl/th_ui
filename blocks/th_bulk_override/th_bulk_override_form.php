<?php

require_once $CFG->dirroot . '/lib/formslib.php';
require_once "{$CFG->libdir}/formslib.php";

class th_bulk_override_form extends moodleform {

	function definition() {
		global $DB;
		$mform = $this->_form;

		$mform->addElement('header', 'displayinfo', get_string('filter'));
		$radioarray = array();
		$radioarray[] = &$mform->createElement('radio', 'show_option', '', 'Gia hạn theo bài', 0);
		$radioarray[] = &$mform->createElement('radio', 'show_option', '', 'Gia hạn tất cả các bài', 1);
		$mform->addGroup($radioarray, 'show_option', 'Gia hạn khóa học theo:', array(''), false);
		$mform->setDefault('show_option', 0);

		$link = "<a href='example.csv'>example.csv</a>";
		$link1 = "<a href='example1.csv'>example.csv</a>";
		$mform->addElement('static', 'example', get_string('examplecsv', 'block_th_bulk_override'), $link);
		$mform->addElement('static', 'example1', get_string('examplecsv', 'block_th_bulk_override'), $link1);
		$mform->addElement('filepicker', 'list_time', get_string('file'));
		$this->add_action_buttons(true, get_string('submit', 'block_th_bulk_override'));
		$mform->addElement('static', 'note', get_string('note', 'block_th_bulk_override'), get_string('description', 'block_th_bulk_override'));
	}

	function validation($data, $files) {
		return array();
	}

}
