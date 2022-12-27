<?php

require_once $CFG->dirroot . '/lib/formslib.php';
require_once "{$CFG->libdir}/formslib.php";
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';
require_once 'libs.php';

class th_student_number_report_form extends moodleform {

	function definition() {
		global $DB;
		$mform = $this->_form;

		$mform->addElement('header', 'displayinfo', get_string('filter'));
		$radioarray = array();
		$radioarray[] = &$mform->createElement('radio', 'show_option', '', 'Môn học', 0);
		$radioarray[] = &$mform->createElement('radio', 'show_option', '', 'Ngành học', 1);
		$radioarray[] = &$mform->createElement('radio', 'show_option', '', 'Đợt mở môn', 2);
		$mform->addGroup($radioarray, 'show_option', 'Thống kê theo:', array(''), false);
		$mform->setDefault('show_option', 0);

		$list_course = ds_course();

		$list_shortname = [];
		
		foreach($list_course as $k => $shortname){
			$pos = strpos($shortname, '-');

			if ($pos !== false) {
				$shortname_arr = explode('-', $shortname);
				$shortname = $shortname_arr[0];
			}

			if (!in_array($shortname, $list_shortname)){
				$pos2 = strrpos($k, ' - ');

				if ($pos2 !== false) {
					$fullname = substr($k, 0, $pos2);
				} else {
					$fullname = $k;
				}
				
				$list_shortname[$shortname] = $fullname;
			}
		}

		$ds_nganh = ds_nganh();

		$options = array(
			'multiple' => true,
			'Chưa chọn'
		);

		$element = $mform->addElement('autocomplete', 'course_id', 'Chọn môn học', $list_shortname, $options);
		// $attributes1 = $element2->getAttributes() + ['required' => 'true', 'class' => 'custom_required'];
		// $element1->setAttributes($attributes1);

		$element2 = $mform->addElement('autocomplete', 'nganh', 'Chọn ngành', $ds_nganh, $options);
		// $attributes2 = $element2->getAttributes() + ['required' => 'true', 'class' => 'custom_required'];
		// $element2->setAttributes($attributes2);

		$mform->addElement('date_selector', 'startdate', 'Từ ngày');
		$mform->addElement('date_selector', 'enddate', 'Đến ngày');

		$options1 = array(
		    '0' => 'Lớn hơn',
		    '1' => 'Nhỏ hơn',
		    '2' => 'Bằng'
		);

		$this->_name = 'so_luong';
		$this->_label = 'Số lượng học viên';
		$objs = array();

		$objs['op'] = $mform->createElement('select', 'option', null, $options1);
		$mform->setDefault('option', 0);

		$objs['value'] = $mform->createElement('text', $this->_name, null);
		$mform->setDefault($this->_name, 0);

		$grp = $mform->addElement('group', $this->_name . '_grp', $this->_label, $objs, '', false);
		$mform->setType($this->_name, PARAM_INT);

		$mform->disabledIf('course_id', 'show_option', 'neq', '0');
		$mform->disabledIf('nganh', 'show_option', 'neq', '1');

		$mform->hideif('course_id', 'show_option', 'neq', '0');
		$mform->hideif('nganh', 'show_option', 'neq', '1');
		
		$this->add_action_buttons(true, 'Submit');
	}

	function validation($data, $files) {
		return array();
	}

}
