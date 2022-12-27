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
 * Settings Custom Navigation local plugin.
 *
 * @package    local_th_dashboard
 * @author     Carlos Escobedo <http://www.twitter.com/carlosagile>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  2017 Carlos Escobedo <http://www.twitter.com/carlosagile>)
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_th_dashboard', get_string('pluginname', 'local_th_dashboard'));
    $settings->add(new admin_setting_configcheckbox('local_th_dashboard/enabled', get_string('activate', 'local_th_dashboard'),
        get_string('stractivate', 'local_th_dashboard'), 0));
    $settings->add(new admin_setting_configcheckbox('local_th_dashboard/flatenabled', get_string('flatenabled', 'local_th_dashboard'),
        get_string('strflatenabled', 'local_th_dashboard'), 0));

    global $DB;

    $sql = "SELECT r.id,shortname FROM {role_context_levels} rcl
            JOIN {role} r ON rcl.roleid = r.id
            WHERE rcl.contextlevel = 10";
    $data = $DB->get_records_sql($sql);
    $role = array();
    foreach ($data as $key => $item) {
        $role[$key] = $key . ' - ' . $item->shortname;
    }

    $settings->add(new admin_setting_configmultiselect('local_th_dashboard/roles',
        get_string('roles', 'local_th_dashboard'), '', [], $role));

    $settings->add(new admin_setting_configtextarea('local_th_dashboard/menuitems',
        get_string('items', 'local_th_dashboard'), get_string('stritems', 'local_th_dashboard'), ''));

    $ADMIN->add('appearance', $settings);
}
