<?php

require_once '../../config.php';
require_once $CFG->dirroot . '/group/lib.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';
require_once 'th_bulk_enrol_groups_form.php';
require_once 'blocklib.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

$th_enrol_groups_csvkey = optional_param('key', 0, PARAM_ALPHANUMEXT);
// Check for all required variables.
$courseid = $COURSE->id;
$returnto = optional_param('returnto', 'course', PARAM_ALPHANUM); // Generic navigation return page switch.
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_bulk_enrol_groups', $courseid);
}

require_login($courseid);
require_capability('block/th_bulk_enrol_groups:view', context_course::instance($COURSE->id));

$pageurl = '/blocks/th_bulk_enrol_groups/view.php';
$title = get_string('title', 'block_th_bulk_enrol_groups');
$PAGE->set_url('/blocks/th_bulk_enrol_groups/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_bulk_enrol_groups', 'block_th_bulk_enrol_groups'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_bulk_enrol_groups'));
$PAGE->requires->js_call_amd('local_thlib/main', 'addAsteriskToCustomRequiredFieldForm', array($CFG->wwwroot));

$editurl = new moodle_url('/blocks/th_bulk_enrol_groups/view.php');
$settingsnode = $PAGE->navbar->add(get_string('breadcrumb', 'block_th_bulk_enrol_groups'), $editurl);
$settingsnode->make_active();

if (empty($th_enrol_groups_csvkey)) {
	$th_enrol_groups = new th_bulk_enrol_groups_form();

	if ($th_enrol_groups->is_cancelled()) {
		$courseurl = new moodle_url('/my');
		redirect($courseurl);
	} else if ($fromform = $th_enrol_groups->get_data()) {
		$content      = $th_enrol_groups->get_file_content('list_groups');
		$contents     = th_parse_groups($content);
		$list_groups   = th_enrol_groups_get_content($contents);
		$checked_groups = th_check_groups($list_groups);
		// Save data in Session.
		$th_enrol_groups_csvkey                                  = $courseid . '_' . time();
		$SESSION->block_th_enrol_groups[$th_enrol_groups_csvkey] = $checked_groups;

	} else {
		// form didn't validate or this is the first display
		echo $OUTPUT->header();
		echo $OUTPUT->heading("<center>$title</center>");
		$th_enrol_groups->display();
		echo $OUTPUT->footer();
	}
}

if ($th_enrol_groups_csvkey) {
	$form2 = new confirm_form(null, array('th_enrol_groups_csvkey' => $th_enrol_groups_csvkey));

	if ($form2->is_cancelled()) {
		$courseurl = new moodle_url('/blocks/th_bulk_enrol_groups/view.php');
		redirect($courseurl);
	} else if ($formdata = $form2->get_data()) {
		if (
			!empty($th_enrol_groups_csvkey) && !empty($SESSION->block_th_enrol_groups) &&
			array_key_exists($th_enrol_groups_csvkey, $SESSION->block_th_enrol_groups)
		) {
			$data          = $SESSION->block_th_enrol_groups[$th_enrol_groups_csvkey];
			$enrol_groups = $data->enrol_groups;

			foreach ($enrol_groups as $k => $enrol_group) {
				$groupid = $enrol_group->group_id;
				$user_id = $enrol_group->user_id;
				groups_add_member($groupid, $user_id);
			}

			$link  = "<a href='view.php'>tiếp tục thêm học viên vào nhóm gia hạn</a>";
			$link1 = $CFG->wwwroot . '/my';
			$home  = "<a href='$link1'>trang chủ</a>";
			$wn    = 'Gán học viên vào nhóm gia hạn thành công bạn có muốn ' . $link . ' hoặc quay lại ' . $home;

			$notification = new \core\output\notification(
				$wn,
				\core\output\notification::NOTIFY_WARNING
			);
			$notification->set_show_closebutton(false);
			echo $OUTPUT->header();
			echo $OUTPUT->heading(get_string('pluginname', 'block_th_bulk_enrol_groups'));
			echo $OUTPUT->render($notification);
			echo $OUTPUT->footer();
		}
	} else {
		echo $OUTPUT->header();
		echo $OUTPUT->heading("<center>$title</center>");
		if (
			!empty($th_enrol_groups_csvkey) && !empty($SESSION->block_th_enrol_groups) &&
			array_key_exists($th_enrol_groups_csvkey, $SESSION->block_th_enrol_groups)
		) {
			$blockth_enrol_groups_csvdata = $SESSION->block_th_enrol_groups[$th_enrol_groups_csvkey];
			if (!empty($blockth_enrol_groups_csvdata->error_messages)) {
				$errors = $blockth_enrol_groups_csvdata->error_messages;
				$html1 = th_display_table_error($errors);
				echo $OUTPUT->heading('Gợi ý');
				echo $html1;
			}
			if (!empty($blockth_enrol_groups_csvdata->enrol_groups)) {
				$enrol_groups = $blockth_enrol_groups_csvdata->enrol_groups;
				$html = th_display_table_enrol_groups($enrol_groups);
				echo $OUTPUT->heading('<center><h3>CÁC HỌC VIÊN SẼ ĐƯỢC THÊM VÀO NHÓM GIA HẠN</h3></center>');
				echo $html;
			}
		}

		if (
			!empty($blockth_enrol_groups_csvdata) && isset($blockth_enrol_groups_csvdata->valid_groups_found) &&
			empty($blockth_enrol_groups_csvdata->valid_groups_found)
		) {
			$a      = new stdClass();
			$url    = new moodle_url('/blocks/th_bulk_enrol_groups/view.php');
			$a->url = $url->out();
			$wn = get_string('error_no_valid_groups_in_list', 'block_th_bulk_enrol_groups', $a);
			$notification = new \core\output\notification(
				$wn,
				\core\output\notification::NOTIFY_WARNING
			);
			$notification->set_show_closebutton(false);
			echo $OUTPUT->render($notification);
		} else {
			echo $form2->display();
		}
		echo $OUTPUT->footer();
	}
}
?>

