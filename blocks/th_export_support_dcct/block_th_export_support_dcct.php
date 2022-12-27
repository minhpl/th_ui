<?php

class block_th_export_support_dcct extends block_base {
	public function init() {
		$this->title = get_string('title', 'block_th_export_support_dcct');
	}

	public function get_content() {
		global $CFG;
		if ($this->content !== null) {
			return $this->content;
		}
		$this->content = new stdClass;
		global $COURSE;
		$context = context_course::instance($COURSE->id);
		if (has_capability('block/th_export_support_dcct:managepages', $context)) {
			$url = new moodle_url('/blocks/th_export_support_dcct/view.php');
			$url1 = new moodle_url('/blocks/th_export_support_dcct/edit.php');
			$this->content->text = 'Nhấp vào liên kết bên dưới để xuất danh sách GVCN/QLHT theo mã lớp';
			$this->content->footer = html_writer::link($url, 'Xuất file');
		} else {
			$this->content->footer = 'No Permission!';
		}
		return $this->content;
	}
}
?>