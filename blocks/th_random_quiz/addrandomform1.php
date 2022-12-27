<?php

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir . '/formslib.php';
require_once $CFG->dirroot . '/blocks/th_random_quiz/lib.php';

class quiz_add_random_form1 extends moodleform {

	protected function definition() {
		global $OUTPUT, $PAGE, $CFG;

		$mform = $this->_form;
		$mform->setDisableShortforms();

		// Random from existing category section.
		$mform->addElement('header', 'existingcategoryheader',
			get_string('randomfromexistingcategory', 'quiz'));

		$listcourses_random = get_list_courses_mau();

		$options = array(
			'multiple' => true,
			'noselectionstring' => 'Không có giá trị nào được chọn',
		);
		$mform->addElement('autocomplete', 'course_id', 'Chọn khóa học', $listcourses_random, $options);

		$list1 = array(
			'0' => 'Ngẫu nhiên cả kho',
			'1' => '20% khó, 40% trung bình, 40% dễ',
		);

		$mform->addElement('select', 'option_add', 'Tạo câu hỏi ngẫu nhiên theo:',
			$list1);

		$mform->addElement('select', 'numbertoadd', get_string('randomnumber', 'quiz'),
			$this->get_number_of_questions_to_add_choices());
		$mform->setDefault('numbertoadd', 50);
		$mform->addElement('checkbox', 'export_all', 'Xuất tất cả NHCH');

		$list = array(
			'1' => 1,
			'2' => 2,
			'3' => 3,
			'4' => 4,
			'5' => 5,
		);

		$mform->addElement('select', 'so_bai_kt', 'Số bài kiểm tra ', $list);

		$this->add_action_buttons(true, get_string('addrandomquestion', 'quiz'));
	}

	public function validation($fromform, $files) {
		$errors = parent::validation($fromform, $files);

		if (!empty($fromform['newcategory']) && trim($fromform['name']) == '') {
			$errors['name'] = get_string('categorynamecantbeblank', 'question');
		}

		return $errors;
	}

	private function get_number_of_questions_to_add_choices($maxrand = 100) {
		$randomcount = array();
		for ($i = 1; $i <= min(100, $maxrand); $i++) {
			$randomcount[$i] = $i;
		}
		return $randomcount;
	}
}

class confirm_form extends moodleform {

	protected function definition() {
		global $SESSION;

		$th_random_quiz_key = $this->_customdata['th_random_quiz_key'];

		$mform = $this->_form;

		$mform->addElement('hidden', 'key');
		$mform->setType('key', PARAM_RAW);
		$mform->setDefault('key', $th_random_quiz_key);

		$showbutton = true;
		$check_random = null;
		if (isset($SESSION->block_th_random_quiz) && array_key_exists($th_random_quiz_key, $SESSION->block_th_random_quiz)) {
			$check_random = $SESSION->block_th_random_quiz[$th_random_quiz_key];
			if (isset($check_random->valid_random_found) && empty($check_random->valid_random_found)) {
				$showbutton = false;
			}
		}

		if ($showbutton) {

			$buttonstring = 'submit';

			$this->add_action_buttons(true, $buttonstring);
		}
	}
}
