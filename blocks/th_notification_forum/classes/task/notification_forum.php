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
 * Version details
 *
 * @package   block_th_course_status
 * @copyright 2017 Manoj Solanki (Coventry University)
 * @copyright
 * @copyright
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace local_th_notification_forum\task;
use mod_forum\task\cron_task;

require_once $CFG->libdir . '/datalib.php';

defined('MOODLE_INTERNAL') || die();

/**
 * Task for updating RSS feeds for rss client block
 *
 * @package   block_recent_activity
 * @author    Farhan Karmali <farhan6318@gmail.com>
 * @copyright Farhan Karmali 2018
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class notification_forum extends cron_task {

    /**
     * Execute the scheduled task.
     */
    public function execute() {
        global $CFG, $DB;

        $timenow = time();

        // Delete any really old posts in the digest queue.
        $weekago = $timenow - (7 * 24 * 3600);
        $this->log_start("Removing old digest records from 7 days ago.");
        $DB->delete_records_select('forum_queue', "timemodified < ?", array($weekago));
        $this->log_finish("Removed all old digest records.");

        $config       = get_config("local_th_notification_forum");
        $timesendnext = $config->timesendnext;

        $endtime   = $timenow - $CFG->maxeditingtime;
        $starttime = $endtime - (2 * DAYSECS);
        $this->log_start("Fetching unmailed posts.");
        $posts = $this->get_unmailed_posts($starttime, $endtime, $timenow);

        print_object($timesendnext);
        print_object($CFG->forum_enabletimedposts);
        print_object(date("d/m/Y H:i:s", $starttime));
        print_object(date("d/m/Y H:i:s", $endtime));
        print_object(date("d/m/Y H:i:s", $timenow));
        print_object($posts);
        // exit();

        if (!$posts = $this->get_unmailed_posts($starttime, $endtime, $timenow)) {
            $this->log_finish("No posts found.", 1);
            return false;
        }
        $this->log_finish("Done");

        // Process post data and turn into adhoc tasks.
        $this->process_post_data($posts);

        // Mark posts as read.
        list($in, $params) = $DB->get_in_or_equal(array_keys($posts));
        $DB->set_field_select('local_th_notification_forum', 'mailed', 1, "forumpostsid {$in}", $params);
    }

    /**
     * Add dsta for the current forum post to the structure of adhoc data.
     *
     * @param   \stdClass   $post
     */
    protected function add_data_for_post($post) {
        if (!isset($this->adhocdata[$post->course])) {
            $this->adhocdata[$post->course] = [];
        }

        if (!isset($this->adhocdata[$post->course][$post->forum])) {
            $this->adhocdata[$post->course][$post->forum] = [];
        }

        if (!isset($this->adhocdata[$post->course][$post->forum][$post->discussion])) {
            $this->adhocdata[$post->course][$post->forum][$post->discussion] = [];
        }

        $this->adhocdata[$post->course][$post->forum][$post->discussion][$post->forumpostsid] = $post->forumpostsid;
    }

    /**
     * Returns a list of all new posts that have not been mailed yet
     *
     * @param int $starttime posts created after this time
     * @param int $endtime posts created before this
     * @param int $now used for timed discussions only
     * @return array
    //  */
    protected function get_unmailed_posts($starttime, $endtime, $now = null) {
        global $CFG, $DB;

        $config       = get_config("local_th_notification_forum");
        $timesendnext = $config->timesendnext;
        $now          = time();

        $params               = array();
        $params['mailed']     = FORUM_MAILED_PENDING;
        $params['ptimestart'] = $starttime;
        $params['ptimeend']   = $endtime - $timesendnext;
        $params['mailnow']    = 1;

        if (!empty($CFG->forum_enabletimedposts)) {
            if (empty($now)) {
                $now = time();
            }
            $selectsql             = "AND (p.created >= :ptimestart OR d.timestart >= :pptimestart)";
            $params['pptimestart'] = $starttime;
            $timedsql              = "AND (d.timestart < :dtimestart AND (d.timeend = 0 OR d.timeend > :dtimeend))";
            $params['dtimestart']  = $now;
            $params['dtimeend']    = $now;
        } else {
            $timedsql  = "";
            $selectsql = "AND p.created >= :ptimestart";
        }

        return $DB->get_records_sql(
            "SELECT
                    p.forumpostsid,
                    p.discussion,
                    d.forum,
                    d.course,
                    p.created,
                    p.parent,
                    p.userid
                  FROM {local_th_notification_forum} p
                  JOIN {forum_discussions} d ON d.id = p.discussion
                 WHERE d.course = 1 AND p.mailed = :mailed
                $selectsql
                   AND (p.created < :ptimeend OR (p.mailnow = :mailnow AND p.created < $now - $timesendnext))
                $timedsql
                 ORDER BY p.modified ASC",
            $params);
    }
}