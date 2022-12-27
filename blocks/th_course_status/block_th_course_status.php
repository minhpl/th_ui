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
 * Moodle TH Course status block.  Displays Visibility status of a course.
 *
 * Allows users with appropriate permissions to publish / unpublish the course (make it visible / non-visible).
 *
 * @package block_th_course_status
 * @copyright 2018 Manoj Solanki (Coventry University)
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die;

require_once $CFG->dirroot . '/user/profile/lib.php';
require_once $CFG->dirroot . '/user/lib.php';
require_once $CFG->libdir . '/externallib.php';
require_once $CFG->dirroot . '/course/lib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/blocks/th_course_status/lib.php';

global $USER, $DB;
const MAX_COUNT = 5;

/**
 * TH Course status block implementation class.
 *
 * @package block_th_course_status
 * @copyright 2017 Manoj Solanki (Coventry University)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_th_course_status extends block_base {

	/** @var int Display Mode tabs */
	const DISPLAY_MODE_TABS = 1;

	/**
	 * Adds title to block instance.
	 */
	public function init() {
		$this->title = get_string('pluginname', 'block_th_course_status');
	}

	/**
	 * Set up any configuration data.
	 *
	 * The is called immediatly after init().
	 */
	public function specialization() {
		$config = get_config("block_th_course_status");

		// Use the title as defined in plugin settings, if one exists.
		if (!empty($config->title)) {
			$this->title = $config->title;
		} else {
			$this->title = 'TH Course status';
		}
	}

	/**
	 * Which page types this block may appear on.
	 */
	public function applicable_formats() {
		return array('site-index' => true, 'course-view-*' => true);
	}

	/**
	 * Get block instance content.
	 */
	public function get_content() {

		global $COURSE, $USER, $OUTPUT, $ME, $DB;

		if ($this->content !== null) {
			return $this->content;
		}

		$config = get_config("block_th_course_status");

		$this->content = new stdClass();
		$this->content->text = '';

		$displayblock = false;

		$content = '';

		if (WS_SERVER) {

		} else {
			// Check if this is a course page.  Allow display on any course page and section pages (but not activities).
			// Check if $PAGE->url is set.  It should be, but also using a fallback.
			$url = null;
			if ($this->page->has_set_url()) {
				$url = $this->page->url;
			} else if ($ME !== null) {
				$url = new moodle_url(str_ireplace('/index.php', '/', $ME));
			}

			// Check if on a course page and if the URL contains course/view.php to be safe.
			if ($COURSE->id != SITEID) {
				// In practice, $url should always be valid.
				if ($url !== null) {
					// Check if this is the course view page.
					if (($url !== null) && (strstr($url->raw_out(), 'course/view.php'))) {
						$displayblock = true;
					} else {
						return null;
					}
				}
			} else {
				return null;
			}
		}

		// Check the user has update or visibility setting capability within their role.
		$capabilities = array(
			'moodle/course:update',
			'moodle/course:visibility',
			'moodle/course:viewhiddencourses',
		);
		
		$context = context_course::instance($COURSE->id);
		if (has_any_capability($capabilities, $context)) {
			$displayblock = true;
		} else {
			return null;
		}

		// Get course and URL details for action buttons.
		$baseurl = new moodle_url(
			'/blocks/th_course_status/management.php',
			array('courseid' => $COURSE->id, 'categoryid' => $COURSE->category, 'sesskey' => sesskey())
		);

		if (!empty($config->publishedicon)) {
			$publishedicon = '<i class="fa fa-' . $config->publishedicon . '"></i> ';
		} else {
			$publishedicon = '<i class="fa fa-' . get_string('publishedicon', 'block_th_course_status') . '"></i> ';
		}

		if (!empty($config->unpublishedicon)) {
			$unpublishedicon = '<i class="fa fa-' . $config->unpublishedicon . '"></i> ';
		} else {
			$unpublishedicon = '<i class="fa fa-' . get_string('unpublishedicon', 'block_th_course_status') . '"></i> ';
		}

		$unpublishonclick = 'onclick="myunpublish(event)"';
		$publishonclick = 'onclick="mypublish(event)"';

		$records = $DB->get_records('block_th_course_status', array('course' => $COURSE->id), $sort = 'timecreated desc');

		$isvisible = $COURSE->visible;
		if (count($records)) {
			$lastrecords = array_values($records)[0];
			$isvisible = !$lastrecords->ishidden;
		}

		$startdate = $COURSE->startdate;
		$is_huypheduyetable = true;

		if ($isvisible == 1 && $startdate <= time()) {
			$is_huypheduyetable = false;
		}

		if ($isvisible) {
			$unpublishedclass = 'btn-unpublish';
			$unpublishedlabel = get_string('unpublish', 'block_th_course_status');
			$publishedclass = 'btn-published';
			$publishedlabel = $publishedicon . get_string('published', 'block_th_course_status');

			$button = '<button class="btn-course-status ' .
				$unpublishedclass . '" title="' . $unpublishedlabel . '" ' . $unpublishonclick . '>' . $unpublishedlabel . '</button>';
			if ($is_huypheduyetable) {
				$content .= html_writer::link(new moodle_url($baseurl,
					array('action' => 'hidecourse', 'redirect' => $this->page->url)), $button);
			} else {
				$content .= $button;
			}

			$content .= html_writer::tag('button', $publishedlabel, array('class' => 'btn-course-status ' . $publishedclass));

		} else {
			$unpublishedclass = 'btn-unpublished';
			$unpublishedlabel = $unpublishedicon . get_string('unpublished', 'block_th_course_status');
			$publishedclass = 'btn-publish';
			$publishedlabel = get_string('publish', 'block_th_course_status');

			$content .= html_writer::tag('button', $unpublishedlabel, array('class' => 'btn-course-status ' . $unpublishedclass));
			$content .= html_writer::link(new moodle_url($baseurl,
				array('action' => 'showcourse', 'redirect' => $this->page->url)), '<button class="btn-course-status ' .
				$publishedclass . '" title="' . $publishedlabel . '" ' . $publishonclick . '>' . $publishedlabel . '</button>');

		}

		if ($is_huypheduyetable) {
			$content .= '
			<script>
					function myunpublish(e) {

					    if (confirm("' . get_string('unpublishconfirm', 'block_th_course_status') . '")) {
					        return true;
					    } else {
					        e.preventDefault();
					        return false;
					    }
					}

					function mypublish(e) {
					    if (confirm("' . get_string('publishconfirm', 'block_th_course_status') . '")) {
					        return true;
					    } else {
					        e.preventDefault();
					        return false;
					    }
					}
			</script>
			';
		} else {
			$content .= '
		<script>
				function myunpublish(e) {
				    alert("' . get_string('alert_cannotpuplish', 'block_th_course_status') . '");
				}

				function mypublish(e) {
				    if (confirm("' . get_string('publishconfirm', 'block_th_course_status') . '")) {
				        return true;
				    } else {
				        e.preventDefault();
				        return false;
				    }
				}
		</script>
		';
		}

		$content .= '<small>';
		$content .= '<ul>';
		$content .= '<li>' . get_string('unpublished', 'block_th_course_status') . ': ' . get_string('legendunpublished', 'block_th_course_status') . '</li>';
		$content .= '<li>' . get_string('published', 'block_th_course_status') . ': ' . get_string('legendpublished', 'block_th_course_status') . '</li>';
		$content .= '</ul>';
		$content .= '</small>';

		$table = new html_table();

		$count = 0;
		foreach ($records as $key => $value) {
			if ($count >= MAX_COUNT) {
				break;
			}

			$count++;
			$row = new html_table_row();

			$teachingid = $value->teachingid;
			$ishidden = $value->ishidden;
			$text = $ishidden == true ? get_string('invisible', 'block_th_course_status') : get_string('visible', 'block_th_course_status');
			$timecreated = $value->timecreated;

			$teachingname = get_userid_fullname($teachingid);
			$teachingname = html_writer::link(new moodle_url('/user/view.php', array('id' => $teachingid)), $teachingname);

			$row->cells[] = new html_table_cell($teachingname);
			$row->cells[] = new html_table_cell($text);
			$row->cells[] = new html_table_cell(get_datetime($timecreated));
			$table->data[] = $row;
		}

		// $table->attributes = array('class' => 'th_course_status_table');
		$html = html_writer::table($table);
		$html = $OUTPUT->container($html, 'th_course_status_table');
		$content .= $html;

		$tasks = get_scheduled_task_bycourseid($COURSE->id);
		$taskinfo = "";
		foreach ($tasks as $key => $task) {
			$nextruntime = $task->nextruntime;
			$taskinfo .= html_writer::tag('div', get_string('autoshow', 'block_th_course_status') . ' ' . get_datetime($nextruntime));
		}
		$content .= $taskinfo;

		$this->content->text = $content;
		return $this->content;
	}

	/**
	 * Allows multiple instances of the block.
	 */
	public function instance_allow_multiple() {
		return false;
	}

	/**
	 * Allow the block to have a configuration page
	 *
	 * @return boolean
	 */
	public function has_config() {
		return true;
	}

	/**
	 * Sets block header to be hidden or visible
	 *
	 * @return bool if true then header will be visible.
	 */
	public function hide_header() {
		$config = get_config("block_th_course_status");

		// If title in settings is empty, hide header.
		if (!empty($config->title)) {
			return false;
		} else {
			return true;
		}
	}

}
