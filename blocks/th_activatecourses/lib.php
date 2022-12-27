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
 * Library functions for overview.
 *
 * @package   block_th_activatecourses
 * @copyright 2021 phamleminh1812@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Constants for the user preferences grouping options
 */
define('BLOCK_th_activatecourses_GROUPING_ALLINCLUDINGHIDDEN', 'allincludinghidden');
define('BLOCK_th_activatecourses_GROUPING_ALL', 'all');
define('BLOCK_th_activatecourses_GROUPING_INPROGRESS', 'inprogress');
define('BLOCK_th_activatecourses_GROUPING_FUTURE', 'future');
define('BLOCK_th_activatecourses_GROUPING_PAST', 'past');
define('BLOCK_th_activatecourses_GROUPING_FAVOURITES', 'favourites');
define('BLOCK_th_activatecourses_GROUPING_HIDDEN', 'hidden');
define('BLOCK_th_activatecourses_GROUPING_CUSTOMFIELD', 'customfield');

/**
 * Allows selection of all courses without a value for the custom field.
 */
define('BLOCK_th_activatecourses_CUSTOMFIELD_EMPTY', -1);

/**
 * Constants for the user preferences sorting options
 * timeline
 */
define('BLOCK_th_activatecourses_SORTING_TITLE', 'title');
define('BLOCK_th_activatecourses_SORTING_LASTACCESSED', 'lastaccessed');
define('BLOCK_th_activatecourses_SORTING_SHORTNAME', 'shortname');

/**
 * Constants for the user preferences view options
 */
define('BLOCK_th_activatecourses_VIEW_CARD', 'card');
define('BLOCK_th_activatecourses_VIEW_LIST', 'list');
define('BLOCK_th_activatecourses_VIEW_SUMMARY', 'summary');

/**
 * Constants for the user paging preferences
 */
define('BLOCK_th_activatecourses_PAGING_3', 3);
define('BLOCK_th_activatecourses_PAGING_6', 6);
define('BLOCK_th_activatecourses_PAGING_9', 9);
define('BLOCK_th_activatecourses_PAGING_12', 12);
define('BLOCK_th_activatecourses_PAGING_ALL', 0);

/**
 * Constants for the admin category display setting
 */
define('BLOCK_th_activatecourses_DISPLAY_CATEGORIES_ON', 'on');
define('BLOCK_th_activatecourses_DISPLAY_CATEGORIES_OFF', 'off');

/**
 * Get the current user preferences that are available
 *
 * @return mixed Array representing current options along with defaults
 */
function block_th_activatecourses_user_preferences() {
	$preferences['block_th_activatecourses_user_grouping_preference'] = array(
		'null' => NULL_NOT_ALLOWED,
		'default' => BLOCK_th_activatecourses_GROUPING_ALL,
		'type' => PARAM_ALPHA,
		'choices' => array(
			BLOCK_th_activatecourses_GROUPING_ALLINCLUDINGHIDDEN,
			BLOCK_th_activatecourses_GROUPING_ALL,
			BLOCK_th_activatecourses_GROUPING_INPROGRESS,
			BLOCK_th_activatecourses_GROUPING_FUTURE,
			BLOCK_th_activatecourses_GROUPING_PAST,
			BLOCK_th_activatecourses_GROUPING_FAVOURITES,
			BLOCK_th_activatecourses_GROUPING_HIDDEN,
			BLOCK_th_activatecourses_GROUPING_CUSTOMFIELD,
		),
	);

	$preferences['block_th_activatecourses_user_grouping_customfieldvalue_preference'] = [
		'null' => NULL_ALLOWED,
		'default' => null,
		'type' => PARAM_RAW,
	];

	$preferences['block_th_activatecourses_user_sort_preference'] = array(
		'null' => NULL_NOT_ALLOWED,
		'default' => BLOCK_th_activatecourses_SORTING_TITLE,
		'type' => PARAM_ALPHA,
		'choices' => array(
			BLOCK_th_activatecourses_SORTING_TITLE,
			BLOCK_th_activatecourses_SORTING_LASTACCESSED,
			BLOCK_th_activatecourses_SORTING_SHORTNAME,
		),
	);
	$preferences['block_th_activatecourses_user_view_preference'] = array(
		'null' => NULL_NOT_ALLOWED,
		'default' => BLOCK_th_activatecourses_VIEW_CARD,
		'type' => PARAM_ALPHA,
		'choices' => array(
			BLOCK_th_activatecourses_VIEW_CARD,
			BLOCK_th_activatecourses_VIEW_LIST,
			BLOCK_th_activatecourses_VIEW_SUMMARY,
		),
	);

	$preferences['/^block_th_activatecourses_hidden_course_(\d)+$/'] = array(
		'isregex' => true,
		'choices' => array(0, 1),
		'type' => PARAM_INT,
		'null' => NULL_NOT_ALLOWED,
		'default' => 'none',
	);

	$preferences['block_th_activatecourses_user_paging_preference'] = array(
		'null' => NULL_NOT_ALLOWED,
		'default' => BLOCK_th_activatecourses_PAGING_3,
		'type' => PARAM_INT,
		'choices' => array(
			BLOCK_th_activatecourses_PAGING_3,
			BLOCK_th_activatecourses_PAGING_6,
			BLOCK_th_activatecourses_PAGING_9,
			BLOCK_th_activatecourses_PAGING_12,
			BLOCK_th_activatecourses_PAGING_ALL,
		),
	);

	return $preferences;
}

/**
 * Pre-delete course hook to cleanup any records with references to the deleted course.
 *
 * @param stdClass $course The deleted course
 */
function block_th_activatecourses_pre_course_delete(\stdClass $course) {
	// Removing any favourited courses which have been created for users, for this course.
	$service = \core_favourites\service_factory::get_service_for_component('core_course');
	$service->delete_favourites_by_type_and_item('courses', $course->id);
}

function block_th_activatecourses_get_registered_courseids($userid = null) {
	global $USER, $DB;

	if (!$userid || $userid == 0) {
		$userid = $USER->id;
	}

	$courseidarr = [];
	$records = $DB->get_records('th_registeredcourses', ['userid' => $userid, 'timeactivated' => 0]);
	foreach ($records as $rc) {
		$courseidarr[] = $rc->courseid;
	}

	$filteredcourses = [];

	foreach ($courseidarr as $key => $courseid) {
		$context = context_course::instance($courseid);
		if (!is_enrolled($context, $userid, '', true)) {
			$filteredcourses[$courseid] = $courseid;
		}
	}
	return $filteredcourses;
}