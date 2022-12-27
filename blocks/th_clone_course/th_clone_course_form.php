<?php

use \block_th_clone_course\libs;

require_once $CFG->dirroot . '/lib/formslib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';

class th_clone_course_form extends moodleform {

	function definition() {
		global $DB;
		$mform = $this->_form;
		$mform->addElement('header', 'displayinfo', get_string('textfields', 'block_th_clone_course'));

		$mform = $this->_form;
		$radioarray = array();
		$radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('radiokhoahoc', 'block_th_clone_course'), 0);
		$radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('radiofile', 'block_th_clone_course'), 1);
		$mform->addGroup($radioarray, 'show_option', get_string('options', 'block_th_clone_course'), array(''), false);

		$mform->setDefault('show_option', 0);

		$link = "<a href='example.csv'>example.csv</a>";
		$mform->addElement('static', 'example', get_string('examplecsv', 'block_th_clone_course'), $link);

		$mform->addElement('filepicker', 'listcourses', get_string('file'));

		$libs = new libs();
		$listcourses = $libs->get_list_courses();

		foreach ($listcourses as $k => $course) {
			$list[] = $course;
		}

		$max = count($list);
		$arr = [];

		for ($i = 0; $i < $max; ++$i) {
			$str = $list[$i];
			$mau = substr($str, -8);
			$str1 = substr($str, -6);
			$str2 = substr($str, -9, 3);

			if ($mau == ' - mẫu' && $mau == ' - Mẫu' && $mau == ' - MẪU') {
				if (in_array($str, $arr) != true) {
					$arr[] = $str;
				}

			} else if ((int) $str1 != '0' && $str2 == ' - ') {
				$pos = strripos($str, '-', 0);
				$str_new = substr($str, 0, $pos - 1);
				$list_startdate = $libs->get_startdate($str_new);
				$startdate_max = max($list_startdate);

				$sql = "SELECT * FROM {course} WHERE fullname LIKE BINARY '$str%' AND startdate = $startdate_max ";
				if ($DB->record_exists_sql($sql) == 1) {
					if (in_array($str, $arr) != true) {
						$arr[] = $str;
					}
				}

			} else {
				if (in_array($str, $arr) != true) {
					$arr[] = $str;
				}
			}
		}

		$arr_show = $libs->get_list_courses_new($arr);

		$options = array(
			'multiple' => true,
			'noselectionstring' => get_string('allcourses', 'block_th_clone_course'),
		);
		$element = $mform->addElement('autocomplete', 'course_id', get_string('course_id', 'block_th_clone_course'), $arr_show, $options);
		$attributes = $element->getAttributes() + ['required' => 'true', 'class' => 'custom_required'];
		$element->setAttributes($attributes);

		$date = getdate();
		switch ($date['weekday']) {
		case 'Monday':
			$newdate = $date['year'] . '-' . $date['mon'] . '-' . $date['mday'];
			$newdate = strtotime('+6 day', strtotime($newdate));
			$newdate = date('Y-m-j', $newdate);
			break;
		case 'Tuesday':
			$newdate = $date['year'] . '-' . $date['mon'] . '-' . $date['mday'];
			$newdate = strtotime('+5 day', strtotime($newdate));
			$newdate = date('Y-m-j', $newdate);
			break;
		case 'Wednesday':
			$newdate = $date['year'] . '-' . $date['mon'] . '-' . $date['mday'];
			$newdate = strtotime('+4 day', strtotime($newdate));
			$newdate = date('Y-m-j', $newdate);
			break;
		case 'Thursday':
			$newdate = $date['year'] . '-' . $date['mon'] . '-' . $date['mday'];
			$newdate = strtotime('+3 day', strtotime($newdate));
			$newdate = date('Y-m-j', $newdate);
			break;
		case 'Friday':
			$newdate = $date['year'] . '-' . $date['mon'] . '-' . $date['mday'];
			$newdate = strtotime('+2 day', strtotime($newdate));
			$newdate = date('Y-m-j', $newdate);
			break;
		case 'Saturday':
			$newdate = $date['year'] . '-' . $date['mon'] . '-' . $date['mday'];
			$newdate = strtotime('+1 day', strtotime($newdate));
			$newdate = date('Y-m-j', $newdate);
			break;
		case 'Sunday':
			$newdate = $date['year'] . '-' . $date['mon'] . '-' . $date['mday'];
			break;
		}

		$newdate = new DateTime($newdate);
		$defaulttime = $newdate->getTimestamp();
		$mform->addElement('date_selector', 'startdate', get_string('startdate', 'block_th_clone_course'));
		$mform->setDefault('startdate', $defaulttime);

		$mform->disabledIf('course_id', 'show_option', 'neq', '0');
		$mform->disabledIf('startdate', 'show_option', 'neq', '0');
		$mform->disabledIf('listcourses', 'show_option', 'neq', '1');
		$mform->disabledIf('example', 'show_option', 'neq', '1');

		$mform->hideif('course_id', 'show_option', 'neq', '0');
		$mform->hideif('startdate', 'show_option', 'neq', '0');
		$mform->hideif('listcourses', 'show_option', 'neq', '1');
		$mform->hideif('example', 'show_option', 'neq', '1');

		$this->add_action_buttons(true, get_string('submit', 'block_th_clone_course'));
		$mform->addElement('static', 'note', get_string('note', 'block_th_clone_course'), get_string('description', 'block_th_clone_course'));
	}
	//Custom validation should be added here
	function validation($data, $files) {

		if (count($data['course_id']) > 10) {
			return array('course_id' => 'Chỉ được phép copy nhiều nhất 10 khóa học một lúc. Vui lòng chọn lại.');
		}
	}
}
?>
