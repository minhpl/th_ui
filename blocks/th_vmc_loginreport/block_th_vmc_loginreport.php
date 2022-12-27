<?php
class block_th_vmc_loginreport extends block_base {

	public function init() {
		$this->title = get_string('pluginname', 'block_th_vmc_loginreport');
	}

	public function get_content() {
		if ($this->content !== null) {
			return $this->content;
		}
		global $CFG;
		$this->content = new stdClass;
		$this->content->text = get_string("content", 'block_th_vmc_loginreport');
		$this->content->footer = html_writer::link($CFG->wwwroot . '/blocks/th_vmc_loginreport/view.php', get_string('linkreport', 'block_th_vmc_loginreport'));
		return $this->content;
	}
}
