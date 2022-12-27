<?php
class block_th_teacher_activity_report extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_th_teacher_activity_report');
    }

    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }
        global $CFG, $COURSE;
        $this->content = new stdClass;
        $context = context_course::instance($COURSE->id);
        if (has_capability('block/th_teacher_activity_report:seeallthings', $context)) {
            $link = $CFG->wwwroot . '/blocks/th_teacher_activity_report/view.php';
            $text = get_string('view', 'block_th_teacher_activity_report');
            $this->content->text = get_string('content', 'block_th_teacher_activity_report');
            $this->content->footer = html_writer::link($link, $text);
        } else {
            $this->content->footer = 'No Permission!';
        }
        return $this->content;
    }
}
