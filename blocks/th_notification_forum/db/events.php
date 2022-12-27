<?php
defined('MOODLE_INTERNAL') || die();

$observers = array(

    array(
        'eventname' => '\mod_forum\event\discussion_created',
        'callback'  => 'local_th_notification_forum_observer::on_discussion_created',
        'internal'  => false,
        'priority'  => 1000,
    ),

    array(
        'eventname' => '\mod_forum\event\discussion_deleted',
        'callback'  => 'local_th_notification_forum_observer::on_discussion_deleted',
        'internal'  => false,
        'priority'  => 1000,
    ),
);