<?php

class block_th_random_quiz extends block_base {
	public function init() {
		$this->title = get_string('title', 'block_th_random_quiz');
	}

	public function get_content() {
		global $CFG;
		if ($this->content !== null) {
			return $this->content;
		}
		$this->content = new stdClass;
		global $COURSE;
		$context = context_course::instance($COURSE->id);
		if (has_capability('block/th_random_quiz:managepages', $context)) {
			$this->content->text = 'Click để thêm nhiều bài kiểm tra có câu hỏi ngẫu nhiên theo tỉ lệ trong khóa học';
			$url = new moodle_url('/blocks/th_random_quiz/addrandom2.php');
			$this->content->footer = html_writer::link($url, 'Thêm bài kiểm tra');
		} else {
			$this->content->footer = 'No Permission!';
		}
		return $this->content;
	}

	public function applicable_formats() {
		return array(
			'my' => true,
			'site-index' => true,
		);
	}
	
	/**
	 * Subclasses should override this and return true if the
	 * subclass block has a settings.php file.
	 *
	 * @return boolean
	 */
	function has_config() {
		return true;
	}
}
?>