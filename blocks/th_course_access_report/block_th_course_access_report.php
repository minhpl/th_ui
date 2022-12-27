<?php
class block_th_course_access_report extends block_base {

	public function init() {
		$this->title = get_string('pluginname', 'block_th_course_access_report');
	}

	public function get_content() {
		if ($this->content !== null) {
			return $this->content;
		}
		global $CFG, $COURSE;
		$this->content = new stdClass;
		$context = context_course::instance($COURSE->id);
		if (has_capability('block/th_course_access_report:managepages', $context)) {
			$link_view_report = $CFG->wwwroot . '/blocks/th_course_access_report/view.php';
			$text_view_report = get_string('linkreport', 'block_th_course_access_report');
			$this->content->text = get_string("content", 'block_th_course_access_report');
			$this->content->footer = html_writer::link($link_view_report, $text_view_report);
		} else {
			$this->content->footer = 'No Permission!';
		}
		return $this->content;
	}
	function has_config() {return true;}
}
