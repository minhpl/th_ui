<?php

	use \block_th_enrol_course\libs;
	require_once '../../config.php';
	require_once 'th_enrol_course_form.php';
	require_once($CFG->dirroot.'/enrol/manual/locallib.php');
    require_once $CFG->dirroot . '/local/thlib/lib.php';
    require_once $CFG->dirroot . '/local/thlib/th_form.php';

	global $DB, $OUTPUT, $PAGE, $COURSE;

	// Check for all required variables.
	$courseid = $COURSE->id;

	if (!$course = $DB->get_record('course', array('id' => $courseid))) {
		print_error('invalidcourse', 'block_th_enrol_course', $courseid);
	}

	require_login($courseid);
	require_capability('block/th_enrol_course:view', context_course::instance($COURSE->id));

	$pageurl = '/blocks/th_enrol_course/view.php';
	$title = get_string('enrolcoursetitle', 'block_th_enrol_course');
	$PAGE->set_url('/blocks/th_enrol_course/view.php');
	$PAGE->set_pagelayout('standard');
	$PAGE->set_heading(get_string('th_enrol_course', 'block_th_enrol_course'));
	$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_enrol_course'));
	$PAGE->requires->jquery();
	$PAGE->requires->jquery_plugin('ui');
	$PAGE->requires->jquery_plugin('ui-css');
	$PAGE->requires->js_call_amd('local_thlib/main', 'addAsteriskToCustomRequiredFieldForm', array($CFG->wwwroot));

	$editurl = new moodle_url('/blocks/th_enrol_course/view.php');
	$settingsnode = $PAGE->settingsnav->add(get_string('breadcrumb', 'block_th_enrol_course'), $editurl);
	$settingsnode->make_active();

	if (!$enrol_manual = enrol_get_plugin('manual')) {
		throw new coding_exception('Can not instantiate enrol_manual');
	}

	$th_enrol_course = new th_enrol_course_form();

	if ($th_enrol_course->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/my');
		redirect($courseurl);
	} 
	else if ($fromform = $th_enrol_course->get_data()) {
		$course_id = [];
		if ($fromform) {
			$course_id = $fromform->course_id;
		}

		if(sizeof($course_id)) {
			$max = count($course_id);
			$table1 = new html_table();
			$table1->head = array(get_string('STT', 'block_th_enrol_course'), get_string('Fullname', 'block_th_enrol_course'), get_string('Status', 'block_th_enrol_course'));
			$stt = 0;

			for ($i=0; $i < $max; ++$i) { 
				$enrolid = $fromform->course_id[$i];
				$libs = new libs();
				$course_name = $libs->get_course_name($enrolid);
				$instance = $DB->get_record('enrol', array('courseid'=>$enrolid, 'enrol'=>'manual'), '*', MUST_EXIST);
				$timestart = time();
				$timeend = 0;
				$user_id = $fromform->userid;
				$role_id = $fromform->role;
				$max1 = count($role_id);
				for ($j=0; $j<$max1; ++$j) {
					$max2 = count($user_id);
					for ($x=0; $x < $max2; ++$x) {
						$userid = $user_id[$x];
						$roleid = $role_id[$j];
						$fullname = $libs->get_full_name($userid);
						$sql = "SELECT * FROM {user_enrolments} WHERE userid = $userid AND enrolid = $instance->id";
						$sql1 = "SELECT id FROM {context} WHERE instanceid = $course_id[$i] AND contextlevel = 50";
						$context_id = $DB->get_record_sql($sql1);
						$sql2 = "SELECT * FROM {role_assignments} WHERE userid = $userid AND roleid = $roleid AND contextid = $context_id->id";

						if ($DB->record_exists_sql($sql) == 1 && $DB->record_exists_sql($sql2) == 1) {
							$fromform->status = 0;
						} else {
							$fromform->status = 1;
							$enrol_manual->enrol_user($instance, $userid, $roleid, $timestart, $timeend);
						}  
						$sql3 = "SELECT shortname FROM {role} WHERE id = $role_id[$j]";
						$vaitro = $DB->get_field_sql($sql3);  
						$link = new moodle_url('/user/index.php', ['id' => $course_id[$i]]); 
        				$link_edit = html_writer::link($link, $course_name);
						if ($fromform->status == 0) {
							$status = 'User bạn muốn thêm đã tồn tại trong khóa học ' .$link_edit.' với vai trò '.$vaitro.'';
						} else {
							$status = 'Thêm thành công user vào khóa học: ' .$link_edit.' với vai trò '.$vaitro.'';
						}
						$stt = $stt +1;
						$row = new html_table_row();
						$cell = new html_table_cell($stt);
						$row->cells[] = $cell;
						$cell = new html_table_cell($fullname);
						$row->cells[] = $cell;
						$cell = new html_table_cell($status);
						$row->cells[] = $cell;

						if ($fromform->status == 0) {
			        		$cell->attributes = array('class' => "bg-danger");
			        	} else {
			        		$cell->attributes = array('class' => "bg-success");
			        	}

						$table1->data[] = $row;
					}	
				}
			}
			
		} else {
			$date = $fromform->date;
			$libs = new libs();
			$list_course = $libs->get_course_date($date);
			
			$max = count($list_course);
			$table1 = new html_table();
			$table1->head = array(get_string('STT', 'block_th_enrol_course'), get_string('Fullname', 'block_th_enrol_course'), get_string('Status', 'block_th_enrol_course'));
			$stt = 0;

			for ($i=0; $i < $max; ++$i) { 
				$enrolid = $list_course[$i];
				$course_name = $libs->get_course_name($enrolid);
				$instance = $DB->get_record('enrol', array('courseid'=>$enrolid, 'enrol'=>'manual'), '*', MUST_EXIST);
				$timestart = time();
				$timeend = 0;
				$user_id = $fromform->userid;
				$role_id = $fromform->role;
				$max1 = count($role_id);
				for ($j=0; $j<$max1; ++$j) {
					$max2 = count($user_id);
					for ($x=0; $x < $max2; ++$x) {
						$userid = $user_id[$x];
						$roleid = $role_id[$j];
						$fullname = $libs->get_full_name($userid);
						$sql = "SELECT * FROM {user_enrolments} WHERE userid = '$userid' AND enrolid = '$instance->id'";
						$sql1 = "SELECT id FROM {context} WHERE instanceid = $list_course[$i] AND contextlevel = 50";
						$context_id = $DB->get_record_sql($sql1);
						$sql2 = "SELECT * FROM {role_assignments} WHERE userid = $userid AND roleid = $roleid AND contextid = $context_id->id";
						if ($DB->record_exists_sql($sql) == 1 && $DB->record_exists_sql($sql2) == 1) {
							$fromform->status = 0;
						} else {
							$fromform->status = 1;
							$enrol_manual->enrol_user($instance, $userid, $roleid, $timestart, $timeend);
						} 
						$sql3 = "SELECT shortname FROM {role} WHERE id = $role_id[$j]";
						$vaitro = $DB->get_field_sql($sql3); 
						$link = new moodle_url('/user/index.php', ['id' => $list_course[$i]]); 
        				$link_edit = html_writer::link($link, $course_name);
						if ($fromform->status == 0) {
							$status = 'User bạn muốn thêm đã tồn tại trong khóa học ' .$link_edit.' với vai trò '.$vaitro.'';
						} else {
							$status = 'Thêm thành công user vào khóa học: ' .$link_edit.' với vai trò '.$vaitro.'';
						}
						$stt = $stt +1;
						$row = new html_table_row();
						$cell = new html_table_cell($stt);
						$row->cells[] = $cell;
						$cell = new html_table_cell($fullname);
						$row->cells[] = $cell;
						$cell = new html_table_cell($status);
						$row->cells[] = $cell;

						if ($fromform->status == 0) {
			        		$cell->attributes = array('class' => "bg-danger");
			        	} else {
			        		$cell->attributes = array('class' => "bg-success");
			        	}

						$table1->data[] = $row;
					}
				}	
			}
		}

		$table1->attributes = array('class' => 'th-table', 'border' => '1');
		$table1->attributes['style'] = "width: 100%; text-align:center;";
        $html = html_writer::table($table1);
		echo $OUTPUT->header();
		echo $OUTPUT->heading($title);
		echo "</br>";
		$th_enrol_course->display();
		echo "</br>";
		echo $OUTPUT->heading(get_string('enrolprocess', 'block_th_enrol_course'));
		echo "</br>";
		echo $html;
		echo "</br>";
		$lang = current_language();
		echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
		$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th-table', 'GHI DANH NGƯỜI DÙNG VÀO KHÓA HỌC', $lang));
		echo $OUTPUT->footer();
		
	} else {
	// form didn't validate or this is the first display
		echo $OUTPUT->header();
		echo $OUTPUT->heading($title);
		echo "</br>";
		$th_enrol_course->display();
		echo $OUTPUT->footer();
	}
?>
<script type="text/javascript">
    $(document).ready(function() {
    $('input[type=radio][name=show_option]').change(function() {
		$('#fitem_id_course_id .col-form-label').removeAttr('hidden');
		$('#fitem_id_course_id .col-form-label .word-break').removeAttr('hidden');
		$('#fitem_id_date .col-form-label').removeAttr('hidden');
		$('#fitem_id_date .col-form-label .word-break').removeAttr('hidden');
    });
});
</script>

