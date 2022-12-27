<?php

	class block_th_bulk_enrol_groups extends block_base {
		public function init() {
			$this->title = get_string('title', 'block_th_bulk_enrol_groups');
		}
		// The PHP tag and the curly bracket for the class definition
		// will only be closed after there is another function added in the next section.

		public function get_content() {
			if ($this->content !== null) {
				return $this->content;
			}
			$this->content = new stdClass;
			global $COURSE;
			$context = context_course::instance($COURSE->id);
			if (has_capability('block/th_bulk_enrol_groups:managepages', $context)) {
				$this->content->text = get_string("content", 'block_th_bulk_enrol_groups');
				$url = new moodle_url('/blocks/th_bulk_enrol_groups/view.php');
				$this->content->footer = html_writer::link($url, get_string('reportlink', 'block_th_bulk_enrol_groups'));
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
	}
?>