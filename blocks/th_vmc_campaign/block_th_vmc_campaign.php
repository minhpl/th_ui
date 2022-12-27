<?php
class block_th_vmc_campaign extends block_base {

	public function init() {
		$this->title = get_string('pluginname', 'block_th_vmc_campaign');
	}

	public function get_content() {
		if ($this->content !== null) {
			return $this->content;
		}
		global $CFG;
		$this->content = new stdClass;

		$this->content->text = get_string('content', 'block_th_vmc_campaign');
		$this->content->footer = html_writer::link($CFG->wwwroot . '/blocks/th_vmc_campaign/view.php', get_string('reportlink', 'block_th_vmc_campaign'));
		return $this->content;
	}
}
