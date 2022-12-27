<?php

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/lib/formslib.php';

class th_vmc_campaign_form extends moodleform {

	public function definition() {
		global $CFG;

		$mform = $this->_form;

		$mform->addElement('text', 'campaigncode', get_string('campaigncode', 'block_th_vmc_campaign'), 'maxlength="254" size="50"');
		$mform->addRule('campaigncode', get_string('required'), 'required', null, 'client');
		$mform->setType('campaigncode', PARAM_TEXT);

		$mform->addElement('text', 'campaignname', get_string('campaignname', 'block_th_vmc_campaign'), 'maxlength="254" size="50"');
		$mform->addRule('campaignname', get_string('required'), 'required', null, 'client');
		$mform->setType('campaignname', PARAM_TEXT);

		$mform->addElement('editor', 'description', get_string('campaigndescription', 'block_th_vmc_campaign'));
		$mform->setType('description', PARAM_RAW);

		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_INT);

		$this->add_action_buttons();
	}
}

class th_bulkcampaign_form extends moodleform {

	/**
	 * Form definition. Abstract method - always override!
	 */
	protected function definition() {
		global $CFG, $SESSION;

		require_once $CFG->dirroot . '/blocks/th_vmc_campaign/lib.php';

		$mform = $this->_form;

		// Infotext.
		$msg = get_string('th_bulkenrol_form_intro', 'block_th_vmc_campaign');
		$mform->addElement('html', '<div id="intro">' . $msg . '</div>');

		// Textarea for Emails.
		$mform->addElement('textarea', 'usermails',
			get_string('usermails', 'block_th_vmc_campaign'), 'wrap="virtual" rows="10" cols="80"');
		$mform->addRule('usermails', null, 'required');
		$mform->addHelpButton('usermails', 'usermails', 'block_th_vmc_campaign');

		// Add form content if the user came back to check his input.
		$blockth_bulkenroleditlist = optional_param('editlist', 0, PARAM_ALPHANUMEXT);
		if (!empty($blockth_bulkenroleditlist)) {
			$blockth_bulkenroldata = $blockth_bulkenroleditlist . '_data';
			if (!empty($blockth_bulkenroldata) && !empty($SESSION->block_th_bulkenrol_inputs) &&
				array_key_exists($blockth_bulkenroldata, $SESSION->block_th_bulkenrol_inputs)) {
				$formdatatmp = $SESSION->block_th_bulkenrol_inputs[$blockth_bulkenroldata];
				$mform->setDefault('usermails', $formdatatmp);
			}
		}

		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_RAW);
		$mform->setDefault('id', $this->_customdata['campaignid']);

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
			$retval['usermails'] = get_string('error_usermails_empty', 'block_th_vmc_campaign');
		}

		return $retval;
	}
}
class confirm_form extends moodleform {

	/**
	 * Form definition. Abstract method - always override!
	 */
	protected function definition() {
		global $SESSION;

		$blockth_bulkenrolkey = $this->_customdata['block_th_bulkenrol_key'];
		$campaignid = $this->_customdata['campaignid'];

		$mform = $this->_form;

		$mform->addElement('hidden', 'key');
		$mform->setType('key', PARAM_RAW);
		$mform->setDefault('key', $blockth_bulkenrolkey);

		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_INT);
		$mform->setDefault('id', $campaignid);

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
			$buttonstring = get_string('enrol_users', 'block_th_vmc_campaign');
			if ($option == 0) {
				//enrol
				$buttonstring = get_string('enrol_users', 'block_th_vmc_campaign');
			} else if ($option == 1) {
				//unenrol
				$buttonstring = get_string('unenrol_users', 'block_th_vmc_campaign');
			}
			$this->add_action_buttons(true, $buttonstring);
		}
	}
}