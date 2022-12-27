<?php

require_once '../../config.php';
require_once 'lib.php';
require_once $CFG->dirroot . '/blocks/th_activatecourses/classes/external.php';
require_once $CFG->dirroot . '/enrol/locallib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/enrol/externallib.php';

global $DB, $OUTPUT, $PAGE, $COURSE;

// Check for all required variables.
$courseid = required_param('id', PARAM_INT);

// Next look for optional variables.
$isactivate = optional_param('activate', false, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_activatecourses', $courseid);
}

require_login($COURSE->id);
require_capability('block/th_activatecourses:view', context_course::instance($COURSE->id));

$courseidarr = block_th_activatecourses_get_registered_courseids();
if (!in_array($courseid, $courseidarr)) {
	print_error('invalidcourse', 'block_th_activatecourses', $courseid);
}

$pageurl = "/blocks/th_activatecourses/activate.php?id=$courseid";
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('activepagetitle', 'block_th_activatecourses'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('activepagetitle', 'block_th_activatecourses'));

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

$editurl = new moodle_url('/blocks/th_activatecourses/active.php');
$settingsnode = $PAGE->settingsnav->add(get_string('breadcrumb', 'block_th_activatecourses'), $editurl);
$settingsnode->make_active();

// $a = core_enrol_external::get_users_courses(2);
// print_object($a);

if ($isactivate) {

	$manager = new course_enrolment_manager($PAGE, $course);
	$instance = null;
	foreach ($manager->get_enrolment_instances() as $tempinstance) {
		if ($tempinstance->enrol == 'manual') {
			if ($instance === null) {
				$instance = $tempinstance;
				break;
			}
		}
	}
	$plugins = $manager->get_enrolment_plugins(true);
	$plugin = $plugins[$instance->enrol];

	$duration = $instance->enrolperiod;
	$duration_hr = local_thlib_secondsToTime($duration);

	if (!$confirm) {

		echo $OUTPUT->header();

		$optionsno = new moodle_url($pageurl);
		$optionsyes = new moodle_url('/blocks/th_activatecourses/activate.php', array('activate' => true, 'id' => $courseid, 'confirm' => 1, 'sesskey' => sesskey()));

		$warning = get_string('activateconfirm', 'block_th_activatecourses', ['course' => $course->fullname, 'duration' => $duration_hr]);
		if ($duration == 0) {
			$warning = get_string('activateconfirm_nolimit', 'block_th_activatecourses');
		}
		echo $OUTPUT->confirm($warning, $optionsyes, $optionsno);

		echo $OUTPUT->footer();

	} else {
		if (confirm_sesskey()) {
			$timestart = time();
			$timeend = 0;
			if ($duration > 0) {
				$timeend = $timestart + $duration;
			}

			$plugin->enrol_user($instance, $USER->id, 5, $timestart, $timeend);

			$context = context_course::instance($courseid);
			if (is_enrolled($context, $USER->id, '', true)) {

				$DB->set_field('th_registeredcourses', 'timeactivated', time(), ['userid' => $USER->id, 'courseid' => $courseid, 'timeactivated' => 0]);

				//Viet gui email
				$coursefullname = $DB->get_record('course', array('id' => $courseid), 'fullname')->fullname;
				$user = $DB->get_record('user', array('id' => $USER->id));
				$userfullname = fullname($user);
				$linkactive = html_writer::link($CFG->wwwroot . '/blocks/th_activatecourses/activate.php?id=' . $courseid, 'ĐÂY');
				$userfrom = \core_user::get_support_user();
				$title = get_string('title', 'block_th_activatecourses');

				$daylimit = $duration_hr;
				if ($duration == 0) {
					$daylimit = get_string('nolimit', 'block_th_activatecourses');
				}

				$content = get_string('body', 'block_th_activatecourses', array('userfullname' => $userfullname, 'coursefullname' => $coursefullname, 'duration' => $daylimit));
				email_to_user($user, $userfrom, $title, $content);

				$courseurl = new moodle_url("/course/view.php", array('id' => $courseid));
				\core\notification::success(get_string('youenrolledincourse', 'enrol'));
				redirect($courseurl);
			}
		}
	}
} else {

	echo $OUTPUT->header();

	echo $OUTPUT->heading(get_string('activepagetitle', 'block_th_activatecourses'));

	$courserenderer = $PAGE->get_renderer('block_th_activatecourses', 'custom');
	echo $courserenderer->course_info_box($course);

	echo $OUTPUT->footer();
}
