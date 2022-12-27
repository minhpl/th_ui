<?php
class block_th_manage_activatecourses extends block_base {

	public function init() {
		$this->title = get_string('pluginname', 'block_th_manage_activatecourses');
	}

	public function get_content() {
		if ($this->content !== null) {
			return $this->content;
		}

		global $CFG, $COURSE;
		$context = context_course::instance($COURSE->id);
		$this->content = new stdClass;

		if (has_capability('block/th_manage_activatecourses:managepages', $context)) {
			$link_view_report = $CFG->wwwroot . '/blocks/th_manage_activatecourses/view.php';
			$text_view_report = get_string('reportlink', 'block_th_manage_activatecourses');
			$this->content->text = get_string("content", 'block_th_manage_activatecourses');
			$this->content->footer = html_writer::link($link_view_report, $text_view_report);
		} else {
			$this->content->footer = 'No Permission!';
		}
		return $this->content;
	}
}
