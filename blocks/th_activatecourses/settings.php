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
 * Settings for the th_activatecourses block
 *
 * @package    block_th_activatecourses
 * @copyright  2019 Tom Dickman <tomdickman@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
	require_once $CFG->dirroot . '/blocks/th_activatecourses/lib.php';

	// Presentation options heading.
	$settings->add(new admin_setting_heading('block_th_activatecourses/appearance',
		get_string('appearance', 'admin'),
		''));

	// Display Course Categories on Dashboard course items (cards, lists, summary items).
	$settings->add(new admin_setting_configcheckbox(
		'block_th_activatecourses/displaycategories',
		get_string('displaycategories', 'block_th_activatecourses'),
		get_string('displaycategories_help', 'block_th_activatecourses'),
		1));

	// Enable / Disable available layouts.
	$choices = array(BLOCK_th_activatecourses_VIEW_CARD => get_string('card', 'block_th_activatecourses'),
		BLOCK_th_activatecourses_VIEW_LIST => get_string('list', 'block_th_activatecourses'),
		BLOCK_th_activatecourses_VIEW_SUMMARY => get_string('summary', 'block_th_activatecourses'));
	$settings->add(new admin_setting_configmulticheckbox(
		'block_th_activatecourses/layouts',
		get_string('layouts', 'block_th_activatecourses'),
		get_string('layouts_help', 'block_th_activatecourses'),
		$choices,
		$choices));
	unset($choices);

	// Enable / Disable course filter items.
	$settings->add(new admin_setting_heading('block_th_activatecourses/availablegroupings',
		get_string('availablegroupings', 'block_th_activatecourses'),
		get_string('availablegroupings_desc', 'block_th_activatecourses')));

	$settings->add(new admin_setting_configcheckbox(
		'block_th_activatecourses/displaygroupingallincludinghidden',
		get_string('allincludinghidden', 'block_th_activatecourses'),
		'',
		0));

	$settings->add(new admin_setting_configcheckbox(
		'block_th_activatecourses/displaygroupingall',
		get_string('all', 'block_th_activatecourses'),
		'',
		1));

	$settings->add(new admin_setting_configcheckbox(
		'block_th_activatecourses/displaygroupinginprogress',
		get_string('inprogress', 'block_th_activatecourses'),
		'',
		1));

	$settings->add(new admin_setting_configcheckbox(
		'block_th_activatecourses/displaygroupingpast',
		get_string('past', 'block_th_activatecourses'),
		'',
		1));

	$settings->add(new admin_setting_configcheckbox(
		'block_th_activatecourses/displaygroupingfuture',
		get_string('future', 'block_th_activatecourses'),
		'',
		1));

	$settings->add(new admin_setting_configcheckbox(
		'block_th_activatecourses/displaygroupingcustomfield',
		get_string('customfield', 'block_th_activatecourses'),
		'',
		0));

	$choices = \core_customfield\api::get_fields_supporting_course_grouping();
	if ($choices) {
		$choices = ['' => get_string('choosedots')] + $choices;
		$settings->add(new admin_setting_configselect(
			'block_th_activatecourses/customfiltergrouping',
			get_string('customfiltergrouping', 'block_th_activatecourses'),
			'',
			'',
			$choices));
	} else {
		$settings->add(new admin_setting_configempty(
			'block_th_activatecourses/customfiltergrouping',
			get_string('customfiltergrouping', 'block_th_activatecourses'),
			get_string('customfiltergrouping_nofields', 'block_th_activatecourses')));
	}
	$settings->hide_if('block_th_activatecourses/customfiltergrouping', 'block_th_activatecourses/displaygroupingcustomfield');

	$settings->add(new admin_setting_configcheckbox(
		'block_th_activatecourses/displaygroupingfavourites',
		get_string('favourites', 'block_th_activatecourses'),
		'',
		1));

	$settings->add(new admin_setting_configcheckbox(
		'block_th_activatecourses/displaygroupinghidden',
		get_string('hiddencourses', 'block_th_activatecourses'),
		'',
		1));

	// Presentation options heading.
	$settings->add(new admin_setting_heading('block_th_activatecourses/showemptyblockonlyforadmin',
		get_string('showemptyblockonlyforadmin', 'block_th_activatecourses'),
		get_string('showemptyblockonlyforadmin', 'block_th_activatecourses')));

	// Display Course Categories on Dashboard course items (cards, lists, summary items).
	$settings->add(new admin_setting_configcheckbox(
		'block_th_activatecourses/displayemptyblockonlyforadmin',
		get_string('displayemptyblockonlyforadmin', 'block_th_activatecourses'),
		get_string('displayemptyblockonlyforadmin_help', 'block_th_activatecourses'),
		1));

}
