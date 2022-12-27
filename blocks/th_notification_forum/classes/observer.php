<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Event observer.
 *
 * @package    block_recent_activity
 * @copyright  2021 phamleminh1812@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once $CFG->dirroot . '/local/thlib/lib.php';

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer.
 * Stores all actions about modules create/update/delete in plugin own's table.
 * This allows the block to avoid expensive queries to the log table.
 *
 * @package    block_recent_activity
 * @copyright  2021 phamleminh1812@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_th_notification_forum_observer {

    /**
     * Store all actions about modules create/update/delete in own table.
     *
     * @param \core\event\base $event
     */
    public static function on_discussion_created(\core\event\base $event) {

        global $DB, $CFG;
        $objectid = $event->objectid;
        $courseid = $event->courseid;

        if ($courseid == 1) {

            $record     = $DB->get_record('forum_posts', array('discussion' => $objectid));
            $id         = $record->id;
            $discussion = $record->discussion;
            $mailed     = $record->mailed;
            $mailnow    = $record->mailnow;
            $parent     = $record->parent;
            $userid     = $record->userid;
            $created    = $record->created;
            $modified   = $record->modified;

            $data = array(
                'forumpostsid' => $id,
                'mailed'       => $mailed,
                'mailnow'      => $mailnow,
                'discussion'   => $discussion,
                'parent'       => $parent,
                'userid'       => $userid,
                'created'      => $created,
                'modified'     => $modified,
            );

            $task = new local_th_notification_forum\task\add_record_adhoc_task();
            $task->set_custom_data($data);
            \core\task\manager::queue_adhoc_task($task);

        }
    }

    public static function on_discussion_deleted(\core\event\base $event) {

        global $DB, $CFG;
        $discussion = $event->objectid;
        $courseid   = $event->courseid;

        if ($courseid == 1) {
            $data = array(
                'discussion' => $discussion,
            );
            $next_runtime = 60;

            $task = new local_th_notification_forum\task\delete_record_adhoc_task();
            $task->set_next_run_time($next_runtime);
            $task->set_custom_data($data);
            \core\task\manager::reschedule_or_queue_adhoc_task($task);
        }
    }
}