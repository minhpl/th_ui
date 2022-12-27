<?php
class block_th_accountreport extends block_base {

	public function init() {
		$this->title = get_string('namereport', 'block_th_accountreport');
	}

	public function get_content() {
		if ($this->content !== null) {
			return $this->content;
		}
		global $CFG;
		$this->content = new stdClass;

		$this->content->text = get_string('content', 'block_th_accountreport');
		$this->content->footer = html_writer::link($CFG->wwwroot . '/blocks/th_accountreport/view.php', get_string('reportlink', 'block_th_accountreport'));
		return $this->content;
	}
}
