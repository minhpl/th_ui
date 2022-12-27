<?php

require_once "{$CFG->libdir}/formslib.php";
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';

class th_qaareport_form extends th_form {

	function definition() {
		global $DB, $COURSE;

		$mform = $this->_form;
		$mform->addElement('header', 'displayinfo', get_string('textfields', 'block_th_qaareport'));

		$mform = $this->_form;
		$radioarray = array();
		$radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('radiomakhoa', 'local_thlib'), 0);
		$radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('radiomalop', 'local_thlib'), 1);
		$radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('radiouser', 'local_thlib'), 2);
		$radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('radioteaching', 'local_thlib'), 3);
		// $radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('radiocourse', 'local_thlib'), 4);

		$element = $mform->addGroup($radioarray, 'show_option', get_string('searchoption', 'local_thlib'), array(''), false);
		$attributes = $element->_attributes = ['class' => 'custom_required'];

		$mform->setDefault('show_option', 0);

		$config = get_config('local_thlib');
		$sortorder = "lastname,firstname";
		if ($config->sortorder == 1) {
			$sortorder = "firstname,lastname";
		}

		$enrollmentcourseshortname = trim($config->enrollmentcourseshortname);
		$classcodeshortname = trim($config->classcodeshortname);

		$this->makhoaarr = get_profile_data($enrollmentcourseshortname);
		$this->maloparr = get_profile_data($classcodeshortname);

		// Ma Khoa Filter
		$choice = array();
		$choice[''] = '';
		foreach ($this->makhoaarr as $key => $value) {
			$choice[$value->id] = $value->data;
		}

		$options = array(
			'multiple' => true,
			'noselectionstring' => get_string('no_selection', 'local_thlib'),
		);

		$element = $mform->addElement('autocomplete', 'makhoaid', get_string('makhoa', 'block_th_qaareport'), $choice, $options);
		// $attributes = $element->getAttributes() + ['required' => 'true', 'class' => 'custom_required'];
		// $element->setAttributes($attributes);

		// Ma lop filter
		$choice = array();
		$choice[''] = '';
		foreach ($this->maloparr as $key => $value) {
			$choice[$value->id] = $value->data;
		}

		$options = array(
			'multiple' => true,
			'noselectionstring' => get_string('no_selection', 'local_thlib'),
		);

		$element = $mform->addElement('autocomplete', 'malopid', get_string('malop', 'block_th_qaareport'), $choice, $options, array('classs' => 'cohort-cohort'));
		// $attributes = $element->getAttributes() + ['required' => 'true', 'class' => 'custom_required'];
		// $element->setAttributes($attributes);

		// User filter
		$choice = array();
		$choice[''] = '';
		$this->user_arr = get_userid_form($mform, $sortorder, false);
		$context = context_course::instance($COURSE->id);
		if (has_capability('local/thlib:seeallthings', $context)) {
			//if user has admin or manager system role
			$courses = get_courses();
			foreach ($courses as $key => $course) {
				$context = context_course::instance($course->id);
				$users = get_role_users(array(3), $context, false, 'ra.id,u.lastname, u.firstname, u.id', null);
				foreach ($users as $ku => $user) {
					$choice[$ku] = $user->firstname . ' ' . $user->lastname;
				}
			}
		} else {
			$courses = enrol_get_my_courses();
			foreach ($courses as $key => $course) {
				$context = context_course::instance($course->id);
				if (has_capability('block/th_qaareport:view', $context)) {
					$users = get_role_users(array(3), $context, false, 'ra.id,u.lastname, u.firstname, u.id', null);
					foreach ($users as $ku => $user) {
						$choice[$ku] = $user->firstname . ' ' . $user->lastname;
					}
				}
			}
		}

		$options = array(
			'multiple' => true,
			'noselectionstring' => get_string('no_selection', 'local_thlib'),
		);
		$element = $mform->addElement('autocomplete', 'teachingid', get_string('searchteaching', 'block_th_qaareport'), $choice, $options);
		// $attributes = $element->getAttributes() + ['required' => 'true', 'class' => 'custom_required'];
		// $element->setAttributes($attributes);

		$this->course_arr = get_allcourseid_form($mform);

		$this->add_user_status_option_radio();

		$element = $mform->addElement('date_selector', 'time_from', get_string('fromdate', 'block_th_qaareport'));
		$attributes = $element->_attributes = ['class' => 'custom_required'];
		$element = $mform->addElement('date_selector', 'time_to', get_string('todate', 'block_th_qaareport'));
		$attributes = $element->_attributes = ['class' => 'custom_required'];

		$mform->disabledIf('makhoaid', 'show_option', 'neq', '0');
		$mform->disabledIf('malopid', 'show_option', 'neq', '1');
		$mform->disabledIf('userid', 'show_option', 'neq', '2');
		$mform->disabledIf('teachingid', 'show_option', 'neq', '3');
		$mform->disabledIf('courseidarr', 'show_option', 'eq', '3');

		$mform->hideif('makhoaid', 'show_option', 'neq', '0');
		$mform->hideif('malopid', 'show_option', 'neq', '1');
		$mform->hideif('userid', 'show_option', 'neq', '2');
		$mform->hideif('teachingid', 'show_option', 'neq', '3');
		$mform->hideif('courseidarr', 'show_option', 'eq', '3');

		$this->add_action_buttons(false, get_string("submmit", 'block_th_qaareport'));
	}

	function validation($data, $files) {

		if ($data['show_option'] == 0 && empty($data['makhoaid']) && empty($data['courseidarr'])) {
			return array('makhoaid' => get_string('warningmakhoamamonhoc', 'local_thlib'), 'courseidarr' => get_string('warningmakhoamamonhoc', 'local_thlib'));
		}

		if ($data['show_option'] == 1 && empty($data['malopid']) && empty($data['courseidarr'])) {
			return array('malopid' => get_string('warningmalopmamonhoc', 'local_thlib'), 'courseidarr' => get_string('warningmakhoamamonhoc', 'local_thlib'));
		}

		if ($data['show_option'] == 2 && empty($data['userid']) && empty($data['courseidarr'])) {
			return array('userid' => get_string('warningusermamonhoc', 'local_thlib'), 'courseidarr' => get_string('warningmakhoamamonhoc', 'local_thlib'));
		}

		if ($data['show_option'] == 3 && empty($data['teachingid'])) {
			return array('teachingid' => get_string('err_required', 'form'));
		}

	}
}
