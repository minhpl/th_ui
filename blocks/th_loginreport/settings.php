<?php
// This file is part of block_th_loginreport for Moodle - http://moodle.org/
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
 * Settings page
 *
 * @package       block_th_loginreport
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Simeon Naydenov (moniNaydenov@gmail.com)
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

	$configs = array();

	// More non-HTML characters than this is long
	// $configs[] = new admin_setting_configtext('block_th_loginreport/custom_fields',
	// 	get_string('customfields', 'block_th_loginreport'),
	// 	get_string('customfieldsdesc', 'block_th_loginreport'),
	// 	'', PARAM_TEXT);

	$configs[] = new admin_setting_configtext('block_th_loginreport/roles_field',
		get_string('rolesfield', 'block_th_loginreport'),
		get_string('rolesfielddesc', 'block_th_loginreport'),
		'', PARAM_TEXT);

	foreach ($configs as $config) {
		$config->plugin = 'block_th_loginreport';
		$settings->add($config);
	}

}
