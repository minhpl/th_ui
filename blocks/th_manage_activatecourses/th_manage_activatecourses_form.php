<?php

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/local/thlib/lib.php';

class th_manage_activatecourses_form extends moodleform {
	public function definition() {
		global $CFG, $DB;

		$mform = $this->_form;
		$customdata = $this->_customdata;

		if ($customdata) {

			$course_arr = $customdata['courseid'];

		} else {

			$sql = "SELECT id,fullname,shortname FROM {course} WHERE summaryformat=1 AND visible =1";

			$courses = $DB->get_records_sql($sql);
			$course_arr = array();
			///$course_arr = ["" => ""];
			foreach ($courses as $course) {
				$course_arr[$course->id] = $course->fullname . ', ' . $course->shortname;
			}
		}

		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_RAW);

		$users = $DB->get_records_sql("SELECT * FROM {user} WHERE id <> 1 AND deleted = 0 AND suspended = 0");
		$user_arr = array();
		$user_arr = ["" => ""];
		foreach ($users as $userid => $user) {
			$user_arr[$userid] = fullname($user) . ', ' . $user->username . ', ' . $user->email;
		}

		$options_user = array(
			'multiple' => false,
			'noselectionstring' => get_string('choose_a_user', 'block_th_manage_activatecourses'),
		);
		$mform->addElement('autocomplete', 'userid', get_string('searchuser', 'block_th_manage_activatecourses'), $user_arr, $options_user);
		$mform->addRule('userid', '', 'required', null, 'client', false, false);

		$options_course = array(
			'multiple' => false,
			'noselectionstring' => get_string('choose_a_course', 'block_th_manage_activatecourses'),
		);
		$mform->addElement('autocomplete', 'courseid', get_string('searchcourse', 'block_th_manage_activatecourses'), $course_arr, $options_course);
		$mform->addRule('courseid', '', 'required', null, 'client', false, false);

		$allCampaign = array();
		$allCampaign = ['' => ''];
		$th_manage_activatecourses = new th_manage_activatecourses;
		$listCampaign = $th_manage_activatecourses->get_all_campaign();

		foreach ($listCampaign as $key => $value) {
			$allCampaign[$key] = $value;
		}

		$options = array(
			'multiple' => false,
			'noselectionstring' => get_string('choose_a_campaign', 'block_th_manage_activatecourses'),
		);
		$title_campaign = get_string('campaign', 'block_th_manage_activatecourses');
		$mform->addElement('autocomplete', 'campaignid', $title_campaign, $allCampaign, $options);

		$mform->disabledIf('campaignid', 'id', 'neq', '0');
		$mform->hideif('campaignid', 'id', 'neq', '0');
		$mform->disabledIf('userid', 'id', 'neq', '0');
		$mform->hideif('userid', 'id', 'neq', '0');

		$this->add_action_buttons(true, get_string('submit'));
	}

	public function definition_after_data() {

		global $CFG, $DB;
		$mform = $this->_form;
		$id = $mform->getElement('id')->getValue();

		if (!$id) {
			$mform->_elements[2]->_attributes['multiple'] = 1;
		} else {

			unset($mform->_rules['userid']);
			unset($mform->_required[0]);
		}
	}
}
class th_bulk_form extends moodleform {

	/**
	 * Form definition. Abstract method - always override!
	 */
	protected function definition() {
		global $CFG, $SESSION;

		//require_once $CFG->dirroot . '/blocks/th_manage_activatecourses/lib.php';

		$mform = $this->_form;

		// Infotext.
		$msg = get_string('th_bulkenrol_form_intro', 'block_th_manage_activatecourses');
		$mform->addElement('html', '<div id="intro">' . $msg . '</div>');

		// Textarea for Emails.
		$mform->addElement('textarea', 'usermails',
			get_string('usermails', 'block_th_manage_activatecourses'), 'wrap="virtual" rows="10" cols="80"');
		$mform->addRule('usermails', null, 'required');
		$mform->addHelpButton('usermails', 'usermails', 'block_th_manage_activatecourses');

		$allCampaign = array();
		$allCampaign = ['' => ''];
		$th_manage_activatecourses = new th_manage_activatecourses;
		$listCampaign = $th_manage_activatecourses->get_all_campaign();

		foreach ($listCampaign as $key => $value) {
			$allCampaign[$key] = $value;
		}

		$options = array(
			'multiple' => false,
			'noselectionstring' => get_string('choose_a_campaign', 'block_th_manage_activatecourses'),
		);
		$title_campaign = get_string('campaign', 'block_th_manage_activatecourses');
		$mform->addElement('autocomplete', 'campaignid', $title_campaign, $allCampaign, $options);
		//$mform->addRule('campaign', '', 'required', null, 'client', false, false);

		// Add form content if the user came back to check his input.
		$blockth_bulkenroleditlist = optional_param('editlist', 0, PARAM_ALPHANUMEXT);
		if (!empty($blockth_bulkenroleditlist)) {
			$blockth_bulkenroldata = $blockth_bulkenroleditlist . '_data';
			if (!empty($blockth_bulkenroldata) && !empty($SESSION->block_th_bulkenrol_inputs) &&
				array_key_exists($blockth_bulkenroldata, $SESSION->block_th_bulkenrol_inputs)) {
				$formdatatmp = $SESSION->block_th_bulkenrol_inputs[$blockth_bulkenroldata];
				$mform->setDefault('usermails', $formdatatmp);

				$formdata = $SESSION->block_th_bulkenrol_inputs[$blockth_bulkenroldata];
				$formdatatmp = $formdata->usermails;
				$mform->setDefault('usermails', $formdatatmp);

				$campaignid = $formdata->campaignid;
				$mform->setDefault('campaignid', $campaignid);
			}
		}

		// $mform->addElement('hidden', 'id');
		// $mform->setType('id', PARAM_RAW);
		// $mform->setDefault('id', $this->_customdata['campaignid']);

		$mform->addElement('hidden', 'option');
		$mform->setType('option', PARAM_RAW);
		$mform->setDefault('option', $this->_customdata['option']);
		$this->add_action_buttons(true, get_string('submit'));
	}

	/**
	 * Get each of the rules to validate its own fields
	 *
	 * @param array $data array of ("fieldname"=>value) of submitted data
	 * @param array $files array of uploaded files "element_name"=>tmp_file_path
	 * @return array of "element_name"=>"error_description" if there are errors,
	 *         or an empty array if everything is OK (true allowed for backwards compatibility too).
	 */
	public function validation($data, $files) {
		$retval = array();

		if (empty($data['usermails'])) {
			$retval['usermails'] = get_string('error_usermails_empty', 'block_th_manage_activatecourses');
		}

		return $retval;
	}

	public function definition_after_data() {
		$mform = $this->_form;
		$option = $mform->getElement('option')->getValue();

		$mform->disabledIf('campaignid', 'option', 'eq', '1');
		$mform->hideif('campaignid', 'option', 'eq', '1');
		if ($option == 1) {
			$mform->setDefault('campaignid', "");
		}
	}
}
class confirm_form extends moodleform {

	/**
	 * Form definition. Abstract method - always override!
	 */
	protected function definition() {
		global $SESSION;

		$blockth_bulkenrolkey = $this->_customdata['block_th_bulkenrol_key'];
		//$campaignid = $this->_customdata['campaignid'];

		$mform = $this->_form;

		$mform->addElement('hidden', 'key');
		$mform->setType('key', PARAM_RAW);
		$mform->setDefault('key', $blockth_bulkenrolkey);

		// $mform->addElement('hidden', 'id');
		// $mform->setType('id', PARAM_INT);
		// $mform->setDefault('id', $campaignid);

		$mform->addElement('hidden', 'option');
		$mform->setType('option', PARAM_RAW);
		$mform->setDefault('option', $this->_customdata['option']);
		// Check if we want to show the enrol user button.
		$showenrolebutton = true;
		$checkedmails = null;
		if (isset($SESSION->block_th_bulkenrol) && array_key_exists($blockth_bulkenrolkey, $SESSION->block_th_bulkenrol)) {
			$checkedmails = $SESSION->block_th_bulkenrol[$blockth_bulkenrolkey];
			if (isset($checkedmails->validemailfound) && empty($checkedmails->validemailfound)) {
				$showenrolebutton = false;
			}
		}

		// Only show the enrol user button if necessary.
		if ($showenrolebutton) {

			$option = $SESSION->block_th_bulkenrol_options[$blockth_bulkenrolkey];
			$buttonstring = get_string('enrol_users', 'block_th_manage_activatecourses');
			if ($option == 0) {
				//enrol
				$buttonstring = get_string('enrol_users', 'block_th_manage_activatecourses');
			} else if ($option == 1) {
				//unenrol
				$buttonstring = get_string('unenrol_users', 'block_th_manage_activatecourses');
			}
			$this->add_action_buttons(true, $buttonstring);
		}
	}
}