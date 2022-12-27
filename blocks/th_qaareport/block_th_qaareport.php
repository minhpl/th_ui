<?php
class block_th_qaareport extends block_base {
	public function init() {
		$this->title = get_string('title', 'block_th_qaareport');
	}
	// The PHP tag and the curly bracket for the class definition
	// will only be closed after there is another function added in the next section.

	public function get_content() {
		if ($this->content !== null) {
			return $this->content;
		}

		$this->content = new stdClass;
		$this->content->text = get_string("content", 'block_th_qaareport');

		global $COURSE;

		$url = new moodle_url('/blocks/th_qaareport/view.php');

		$this->content->footer = html_writer::link($url, get_string('reportlink', 'block_th_qaareport'));
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