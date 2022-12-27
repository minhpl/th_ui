<?php

class block_th_student_number_report extends block_base {
	public function init() {
		$this->title = get_string('title', 'block_th_student_number_report');
	}

	public function get_content() {
		global $CFG;
		if ($this->content !== null) {
			return $this->content;
		}
		$this->content = new stdClass();
		global $COURSE;
		$context = context_course::instance($COURSE->id);
		if (has_capability('block/th_student_number_report:managepages', $context)) {
			$url = new moodle_url('/blocks/th_student_number_report/view.php');
			$this->content->footer = html_writer::link($url, 'Thống kê số lượng sinh viên');
		} else {
			$this->content->footer = 'No Permission!';
		}
		return $this->content;
	}
}
?>