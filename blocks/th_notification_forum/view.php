<?php
require __DIR__ . '/../../config.php';
require_once $CFG->libdir . '/grade/grade_item.php';
require_once 'lib.php';

global $USER, $DB, $COURSE;

require_login();

$page_url = new moodle_url('/local/th_notification_forum/view.php');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

$courseid = $COURSE->id;
require_login($courseid);

$PAGE->set_url($page_url);
$PAGE->set_title("");
$PAGE->set_heading("");
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();

$cron = new \local_th_notification_forum\task\notification_forum();
$name = $cron->execute();

// print_object($name);
//
print_object(!empty($CFG->forum_enabletimedposts));

echo $OUTPUT->footer();