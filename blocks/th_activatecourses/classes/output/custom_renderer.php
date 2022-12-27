<?php

namespace block_th_activatecourses\output;

require_once $CFG->dirroot . "/course/renderer.php";
require_once $CFG->dirroot . "/course/classes/customfield/course_handler.php";

defined('MOODLE_INTERNAL') || die;

use core_course_renderer;
use coursecat_helper;
use html_writer;
use moodle_url;

class custom_renderer extends core_course_renderer {
	protected function coursecat_coursebox_content(coursecat_helper $chelper, $course) {
		global $CFG;
		if ($chelper->get_show_courses() < self::COURSECAT_SHOW_COURSES_EXPANDED) {
			return '';
		}
		if ($course instanceof stdClass) {
			$course = new core_course_list_element($course);
		}
		$content = '';

		// display course overview files
		$contentimages = $contentfiles = '';
		foreach ($course->get_course_overviewfiles() as $file) {
			$isimage = $file->is_valid_image();
			$url = file_encode_url("$CFG->wwwroot/pluginfile.php",
				'/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
				$file->get_filearea() . $file->get_filepath() . $file->get_filename(), !$isimage);
			if ($isimage) {
				$contentimages .= '<div class="courseimage" style="background-image: url(' . $url . ');"></div>';
			} else {
				$image = $this->output->pix_icon(file_file_icon($file, 24), $file->get_filename(), 'moodle');
				$filename = html_writer::tag('span', $image, array('class' => 'fp-icon')) .
				html_writer::tag('span', $file->get_filename(), array('class' => 'fp-filename'));
				$contentfiles .= html_writer::tag('span',
					html_writer::link($url, $filename),
					array('class' => 'coursefile fp-filename-icon'));
			}
		}
		$content .= $contentimages . $contentfiles;

		// display course summary
		$content .= html_writer::start_tag('div', array('class' => $course->visible ? 'summary' : 'summary dimmed'));

		$coursename = $chelper->get_course_formatted_name($course);
		$coursenamelink = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)),
			$coursename, array('class' => $course->visible ? '' : 'dimmed'));
		$content .= html_writer::tag('h3', $coursenamelink, array('class' => 'coursename'));

		$content .= '<div>' . $chelper->get_course_formatted_summary($course,
			array('overflowdiv' => true, 'noclean' => true, 'para' => false)) . '</div>';

		$content .= html_writer::end_tag('div'); // .summary

		// display course contacts. See course_in_list::get_course_contacts()
		if ($course->has_course_contacts()) {
			$content .= '<div class="teachers">';
			$current_role = '';
			$i = 0;
			$list_course_contacts = $course->get_course_contacts();

			foreach ($list_course_contacts as $userid => $coursecontact) {
				if ($i == 0) {
					$current_role = $coursecontact['rolename'];
					$content .= $current_role . ': ';
					$name = html_writer::link(new moodle_url('/user/view.php', array('id' => $userid, 'course' => SITEID)), $coursecontact['username']);
					$content .= $name;
				}
				if (($i > 0) AND ($coursecontact['rolename'] == $current_role)) {
					$content .= ', ';
					$name = html_writer::link(new moodle_url('/user/view.php', array('id' => $userid, 'course' => SITEID)), $coursecontact['username']);
					$content .= $name;
				} else if ($i > 0) {
					$content .= '</div>';
					$content .= '<div class="teachers">';
					$current_role = $coursecontact['rolename'];
					$content .= $current_role . ': ';
					$name = html_writer::link(new moodle_url('/user/view.php', array('id' => $userid, 'course' => SITEID)), $coursecontact['username']);
					$content .= $name;
				}
				$i++;
			}
			$content .= '</div>'; // .teachers
		}

		// Display custom fields.
		if ($course->has_custom_fields()) {
			$content .= '<div class="custom_fields">';
			$handler = \core_course\customfield\course_handler::create();
			$customfields = $handler->display_custom_fields_data($course->get_custom_fields());
			$content .= \html_writer::tag('div', $customfields, ['class' => 'customfields-container']);
			$content .= '</div>';
		}

		global $USER;

		$button = $this->output->single_button(new moodle_url('/blocks/th_activatecourses/activate.php', array('id' => $course->id, 'activate' => true)), get_string('activate', 'block_th_activatecourses'), 'post');

		$content .= "<div class='course-btn'><p>$button</p></div>";

		// display course category if necessary (for example in search results)
		if ($chelper->get_show_courses() == self::COURSECAT_SHOW_COURSES_EXPANDED_WITH_CAT) {
			if ($CFG->version < 2018120300) {
				require_once $CFG->libdir . '/coursecatlib.php';
				if ($cat = coursecat::get($course->category, IGNORE_MISSING)) {
					$content .= html_writer::start_tag('div', array('class' => 'coursecat'));
					$content .= get_string('category') . ': ' .
					html_writer::link(new moodle_url('/course/index.php', array('categoryid' => $cat->id)),
						$cat->get_formatted_name(), array('class' => $cat->visible ? '' : 'dimmed'));
					$content .= html_writer::end_tag('div'); // .coursecat
				}
			} else {
				if ($cat = core_course_category::get($course->category, IGNORE_MISSING)) {
					$content .= html_writer::start_tag('div', array('class' => 'coursecat'));
					$content .= get_string('category') . ': ' .
					html_writer::link(new moodle_url('/course/index.php', array('categoryid' => $cat->id)),
						$cat->get_formatted_name(), array('class' => $cat->visible ? '' : 'dimmed'));
					$content .= html_writer::end_tag('div'); // .coursecat
				}
			}
		}

		return $content;
	}
}
