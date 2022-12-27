<?php

require_once $CFG->libdir . '/gradelib.php';
require_once $CFG->dirroot . '/user/renderer.php';
require_once $CFG->dirroot . '/grade/lib.php';
require_once $CFG->dirroot . '/grade/report/grader/lib.php';

function get_events_select1($selectwhere, array $params) {

    $logstores = array('logstore_standard', 'logstore_legacy');
    $return = array();
    static $allreaders = null;

    if (is_null($allreaders)) {
        $allreaders = get_log_manager()->get_readers();
    }

    $processedreaders = 0;

    foreach ($logstores as $name) {
        if (isset($allreaders[$name])) {
            $reader = $allreaders[$name];
            $events = $reader->get_events_select($selectwhere, $params, 'timecreated ASC', 0, 0);
            foreach ($events as $event) {
                $obj = new stdClass();
                $obj->time = $event->timecreated;
                $obj->userid = $event->userid;
                $return[] = $obj;
            }
            if (!empty($events)) {
                $processedreaders++;
            }
        }
    }

    // Sort mixed array by time ascending again only when more of a reader has added events to return array.
    if ($processedreaders > 1) {
        usort($return, function ($a, $b) {
            return $a->time > $b->time;
        });
    }

    return $return;
}
