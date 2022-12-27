<?php

require_once '../../config.php';
require_once 'th_bulk_override_form.php';
require_once 'confirm_form.php';
require_once 'blocklib.php';
require_once $CFG->dirroot . '/group/lib.php';
require_once $CFG->dirroot . '/group/group_form.php';
require_once $CFG->dirroot . '/mod/quiz/classes/event/group_override_created.php';
require_once $CFG->dirroot . '/mod/quiz/lib.php';
require_once $CFG->dirroot . '/mod/quiz/locallib.php';
require_once $CFG->dirroot . '/mod/quiz/override_form.php';

global $DB, $OUTPUT, $PAGE, $COURSE;

$th_override_csvkey = optional_param('key', 0, PARAM_ALPHANUMEXT);
$courseid = $COURSE->id;
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_bulk_override', $courseid);
}

require_login($courseid);
require_capability('block/th_bulk_override:view', context_course::instance($COURSE->id));

$pageurl = '/blocks/th_bulk_override/view.php';
$title   = get_string('enrolcoursetitle', 'block_th_bulk_override');
$PAGE->set_url('/blocks/th_bulk_override/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading('Gia hạn bài kiểm tra hàng loạt');
$PAGE->set_title('Gia hạn bài kiểm tra hàng loạt');
$editurl = new moodle_url('/local/th_bulk_override/index.php');
$PAGE->settingsnav->add('Gia hạn bài kiểm tra hàng loạt', $editurl);

if (empty($th_override_csvkey)) {
	$th_bulk_override = new th_bulk_override_form();

	if ($th_bulk_override->is_cancelled()) {
		$courseurl = new moodle_url('/my');
		redirect($courseurl);
	} else if ($fromform = $th_bulk_override->get_data()) {
		if ($fromform) {
			$show_option = $fromform->show_option;
		}

		if(empty($show_option)){
			$content      = $th_bulk_override->get_file_content('list_time');
			$contents     = th_override_csv_parse_time($content);
			$list_time    = th_get_content($contents);
			$checkedtimes = th_override_check_times($list_time);

			// Save data in Session.
			$th_override_csvkey                                  = $courseid . '_' . time();
			$SESSION->block_th_override_csv[$th_override_csvkey] = $checkedtimes;
		} else {
			$content      = $th_bulk_override->get_file_content('list_time');
			$contents     = th_override_csv_parse_time($content);
			$list_time    = th_get_content($contents);
			$checkedtimes = th_override_check_times1($list_time);
			// Save data in Session.
			$th_override_csvkey                                  = $courseid . '_' . time();
			$SESSION->block_th_override_csv[$th_override_csvkey] = $checkedtimes;
		}

	} else {
		// form didn't validate or this is the first display
		echo $OUTPUT->header();
		echo $OUTPUT->heading($title);
		echo "</br>";
		$th_bulk_override->display();
		echo $OUTPUT->footer();
	}
}

if ($th_override_csvkey) {
	$form2 = new confirm_form(null, array('th_override_csvkey' => $th_override_csvkey));

	if ($form2->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/blocks/th_bulk_override/view.php');
		redirect($courseurl);
	} else if ($formdata = $form2->get_data()) {
		if (
			!empty($th_override_csvkey) && !empty($SESSION->block_th_override_csv) &&
			array_key_exists($th_override_csvkey, $SESSION->block_th_override_csv)
		) {
			$data          = $SESSION->block_th_override_csv[$th_override_csvkey];
			$quiz_override = $data->quiz_override;

			foreach ($quiz_override as $k => $quiz) {
				// add group
				$courseid = $quiz->id;
				$course   = $DB->get_record('course', array('id' => $courseid));
				$context       = context_course::instance($course->id);
				$editoroptions = array('maxfiles'                           => EDITOR_UNLIMITED_FILES, 'maxbytes'                           => $course->maxbytes, 'trust'                           => false, 'context'                           => $context, 'noclean'                           => true);
				$editform      = new group_form(null, array('editoroptions' => $editoroptions));

				$data_group                  = new stdClass();
				$date                        = $quiz->time_open_close;
				$attempts                    = $quiz->so_lan_lam;
				$group_name                  = $quiz->group_name;
				$data_group->name            = $group_name;
				$data_group->idnumber        = $group_name;
				$data_group->enrolmentkey    = null;
				$data_group->enablemessaging = 0;
				$data_group->hidepicture     = 0;
				$data_group->imagefile       = null;
				$data_group->id              = 0;
				$data_group->courseid        = $courseid;

				$data_group->description_editor['text']   = null;
				$data_group->description_editor['format'] = 1;
				$data_group->description_editor['itemid'] = null;

				$groups = $DB->get_record_sql("SELECT * FROM {groups} WHERE name = '$group_name' AND courseid = '$courseid'");
				if (empty($groups)) {
					$id = groups_create_group($data_group, $editform, $editoroptions);
				} else {
					$id = $groups->id;
				}
				//add group override
				$quizid              = $quiz->quiz_id;
				$timeopen_timestamp  = $quiz->timeopen_timestamp;
				$timeclose_timestamp = $quiz->timeclose_timestamp;

				$data_gr_override            = new stdClass();
				$data_gr_override->groupid   = $id;
				$data_gr_override->password  = null;
				$data_gr_override->timeopen  = $timeopen_timestamp;
				$data_gr_override->timeclose = $timeclose_timestamp;
				$data_gr_override->timelimit = null;
				$data_gr_override->attempts  = $attempts;
				$data_gr_override->quiz      = $quizid;

				$check = $DB->get_records_sql("SELECT * FROM {quiz_overrides} WHERE quiz = '$quizid' AND groupid = '$id' AND
					timeopen = '$timeopen_timestamp' AND timeclose = '$timeclose_timestamp'");
				if(empty($check)){
					$data_gr_override->id = $DB->insert_record('quiz_overrides', $data_gr_override);
				}
			}
			$link  = "<a href='view.php'>tiếp tục gia hạn bài kiểm tra</a>";
			$link1 = $CFG->wwwroot . '/my';
			$home  = "<a href='$link1'>trang chủ</a>";
			$wn    = 'Gia hạn bài kiểm tra thành công bạn có muốn ' . $link . ' hoặc quay lại ' . $home;

			$notification = new \core\output\notification(
				$wn,
				\core\output\notification::NOTIFY_WARNING
			);
			$notification->set_show_closebutton(false);
			echo $OUTPUT->header();
			echo $OUTPUT->heading(get_string('pluginname', 'block_th_bulk_override'));
			echo $OUTPUT->render($notification);
			echo $OUTPUT->footer();
		}
	} else {
		echo $OUTPUT->header();
		echo $OUTPUT->heading('<center>GIA HẠN BÀI KIỂM TRA HÀNG LOẠT</center>');
		if (
			!empty($th_override_csvkey) && !empty($SESSION->block_th_override_csv) &&
			array_key_exists($th_override_csvkey, $SESSION->block_th_override_csv)
		) {

			$blockth_override_csvdata = $SESSION->block_th_override_csv[$th_override_csvkey];

			if (!empty($blockth_override_csvdata->error_messages)) {

				$errors = $blockth_override_csvdata->error_messages;
				$html1 = th_display_table_error($errors);
				echo $OUTPUT->heading(get_string('Hints', 'block_th_bulk_override'));
				echo $html1;
			}
			if (!empty($blockth_override_csvdata->quiz_override)) {

				$quiz_override = $blockth_override_csvdata->quiz_override;
				$html = th_display_table_override($quiz_override);
				echo $OUTPUT->heading('<center><h3>CÁC BÀI KIỂM TRA SẼ ĐƯỢC GIA HẠN</h3></center>');
				echo $html;
			}
		}

		if (
			!empty($blockth_override_csvdata) && isset($blockth_override_csvdata->valid_time_found) &&
			empty($blockth_override_csvdata->valid_time_found)
		) {
			$a      = new stdClass();
			$url    = new moodle_url('/blocks/th_bulk_override/view.php');
			$a->url = $url->out();
			$wn = get_string('error_no_valid_time_in_list', 'block_th_bulk_override', $a);
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

<script type="text/javascript">
	$(document).ready(function() {
		var element = document.getElementById('fitem_id_example');
		var element1 = document.getElementById('fitem_id_example1');
		var fitem_id_note = document.getElementById('fitem_id_note');
		element1.setAttribute("hidden", "hidden");

		$('input[type=radio][value=0]').change(function() {
			element1.setAttribute("hidden", "hidden");
			element.removeAttribute("hidden");
			fitem_id_note.removeAttribute("hidden");
		});

		$('input[type=radio][value=1]').change(function() {
			element1.removeAttribute("hidden");
			element.setAttribute("hidden", "hidden");
			fitem_id_note.setAttribute("hidden", "hidden");
		});
	});
</script>