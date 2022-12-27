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
 * Extend navigation to add new options.
 *
 * @package    local_th_dashboard
 * @author     Carlos Escobedo <http://www.twitter.com/carlosagile>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  2017 Carlos Escobedo <http://www.twitter.com/carlosagile>)
 */

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/lib/adminlib.php';

/**
 * user_role_assignment function kiểm tra Tài khoản có quyền trong ngữ cảnh hệ thống không
 *
 * @param [int] $userid         id người dùng
 * @param [int] $roleid         id quyền
 * @param [int] $contextid      id ngữ cảnh
 * @return [bool]               true có, false không
 */
function user_role_assignment($userid, $roleid, $contextid = 0)
{
    global $DB;

    $sql = "SELECT COUNT(ra.id)
                  FROM {role_assignments} ra
                 WHERE ra.userid = :userid AND ra.roleid = :roleid AND ra.contextid = 1"; // contextlevel = 10
    $params           = array();
    $params['userid'] = $userid;
    $params['roleid'] = $roleid;

    $count = $DB->record_exists_sql($sql, $params);
    $count = $DB->get_field_sql($sql, $params);
    return ($count > 0);
}

function user_role_system($userid)
{
    global $DB;

    $sql = "SELECT * FROM {role_assignments} as ra WHERE ra.userid = $userid AND ra.contextid = 1"; // contextlevel = 10

    $roles = $DB->get_records_sql($sql);
    
    return $roles;
}

/**
 * local_th_dashboard_extend_navigation function 
 *
 * @param global_navigation $navigation
 * @return void
 */
function local_th_dashboard_extend_navigation(global_navigation $navigation)
{
    global $USER;
    $userid        = $USER->id;
    $systemcontext = context_system::instance();
    $settings      = get_config('local_th_dashboard');
    $roles = user_role_system($userid);

    if (has_capability('local/th_dashboard:viewthdashboard', $systemcontext) && !empty($roles) OR is_siteadmin($userid)) {
        $th_url              = new moodle_url('/local/th_dashboard/view.php', array('key' => 'thdashboard'));
        $main_node           = $navigation->add(get_string('THname', 'local_th_dashboard'), $th_url, navigation_node::TYPE_CONTAINER, null, 'thdashboard');
        $main_node->nodetype = 1;
        // $main_node->forceopen = true;
        // $main_node->showinflatnavigation = true;

        // $sub_node = $main_node->add(get_string('pluinname', 'local_th_dashboard'),'/local/th_dashboard/');
        if (!empty($settings->menuitems) && $settings->enabled) {
            // print_object($settings->menuitems);
            $menu  = new custom_menu($settings->menuitems, current_language());
            $count = 0;
            if ($menu->has_children()) {
                foreach ($menu->get_children() as $item) {
                    update_role_node($item);
                }
            }
            // print_object($menu);

            if ($menu->has_children()) {

                foreach ($menu->get_children() as $item) {
                    if (is_siteadmin($userid)) {
                        navigation_custom_menu_item($item, 1, $main_node, $settings->flatenabled, $count);
                        continue;
                    }
                    $roleid_str = $item->get_title();
                    $roleid_arr = array();
                    if ($roleid_str) {
                        $roleid_arr = explode(",", trim($roleid_str));
                    }

                    if ($roleid_arr) {
                        foreach ($roleid_arr as $key => $roleid) {

                            $roleid = trim($roleid);

                            if (is_numeric($roleid) && user_role_assignment($userid, $roleid)) {
                                // $check_role = user_role_assignment($userid, $roleid);

                                // if ($check_role) {
                                navigation_custom_menu_item($item, 1, $main_node, $settings->flatenabled, $count, $roleid);
                                break;
                                // }
                            }
                        }
                    }
                }
            }
        }
    }
}

/**
 * update_role_node function vẽ lại cây th_dashboard
 *
 * @param custom_menu_item $menu
 * @return void
 */
function update_role_node($menu)
{
    if ($menu->has_children()) {
        foreach ($menu->get_children() as $items) {
            update_role_node($items);
        }
    }
    $parent       = $menu->get_parent();
    $title_parent = $parent->get_title();
    $title_parent = $title_parent . ',' . $menu->get_title();
    $parent->set_title($title_parent);
}

/**
 * ADD custom menu in navigation recursive childs node
 * Is like render custom menu items
 *
 * @param custom_menu_item $menunode {@link custom_menu_item}
 * @param int $parent is have a parent and it's parent itself
 * @param object $pmasternode parent node
 * @param int $flatenabled show master node in boost navigation
 * @return void
 */
function navigation_custom_menu_item(custom_menu_item $menunode, $parent, $pmasternode, $flatenabled, &$count, $roleid = 0)
{
    global $PAGE, $CFG, $USER;

    $userid              = $USER->id;
    static $submenucount = 0;
    // print_object($menunode);
    if ($menunode->has_children()) {
        // node da cap
        $thkey = get_string('thkey', 'local_th_dashboard') . '_' . $count;
        $submenucount++;
        $url = $CFG->wwwroot;
        // print_object($menunode->get_url());
        if ($menunode->get_url() !== null) {
            $url = new moodle_url($menunode->get_url());
        } else {
            $url = new moodle_url('/local/th_dashboard/view.php', array('key' => $thkey));
        }
        // print_object($url);
        if ($parent > 0) {
            $masternode = $pmasternode->add(local_th_dashboard_get_string($menunode->get_text()),
                $url, navigation_node::TYPE_CONTAINER, null, $thkey);
            $masternode->title($menunode->get_title());
        } else {
            $masternode = $PAGE->navigation->add(local_th_dashboard_get_string($menunode->get_text()),
                $url, navigation_node::TYPE_CONTAINER, null, $thkey);
            $masternode->title($menunode->get_title());
            if ($flatenabled) {
                $masternode->isexpandable         = true;
                $masternode->showinflatnavigation = true;
            }
        }
        $count++;
        foreach ($menunode->get_children() as $menunode) {

            if (is_siteadmin($userid)) {
                navigation_custom_menu_item($menunode, $submenucount, $masternode, $flatenabled, $count);
                continue;
            }

            // $roleid = trim($roleid);
            // if (is_numeric($roleid)) {
            //     $check_role = user_role_assignment($userid, $roleid);

            //     if ($check_role) {
            //         navigation_custom_menu_item($menunode, $submenucount, $masternode, $flatenabled, $count, $roleid);
            //         continue;
            //     }
            // }

            $roleid_str = $menunode->get_title();
            $roleid_arr = array();
            if ($roleid_str) {
                $roleid_arr = explode(",", trim($roleid_str));
            }

            if ($roleid_arr) {
                foreach ($roleid_arr as $key => $role_id) {

                    $role_id = trim($role_id);

                    if (is_numeric($role_id) && user_role_assignment($userid, $role_id)) {
                        // $check_role = user_role_assignment($userid, $role_id);

                        // if ($check_role) {
                        navigation_custom_menu_item($menunode, $submenucount, $masternode, $flatenabled, $count, $role_id);
                        break;
                        // }
                    }

                }

            }

        }
    } else {
        $thkey = get_string('thkey', 'local_th_dashboard') . '_' . $count;
        $url   = $CFG->wwwroot;
        if ($menunode->get_url() !== null) {
            $url = new moodle_url($menunode->get_url());
        } else {
            $url = new moodle_url('/local/th_dashboard/view.php', array('key' => $thkey));
        }
        if ($parent) {
            $childnode = $pmasternode->add(local_th_dashboard_get_string($menunode->get_text()),
                $url, navigation_node::TYPE_CUSTOM, null, $thkey);
            $childnode->title($menunode->get_title());
        } else {
            $masternode = $PAGE->navigation->add(local_th_dashboard_get_string($menunode->get_text()),
                $url, navigation_node::TYPE_CONTAINER, null, $thkey);
            $masternode->title($menunode->get_title());
            if ($flatenabled) {
                $masternode->isexpandable         = true;
                $masternode->showinflatnavigation = true;
            }
        }
        $count++;
    }

    return true;
}

/**
 * Translate Custom Navigation Nodes
 *
 * This function is based in a short peace of Moodle code
 * in  Name processing on user_convert_text_to_menu_items.
 *
 * @param string $string text to translate.
 * @return string
 */
function local_th_dashboard_get_string($string)
{
    $title = $string;
    $text  = explode(',', $string, 2);
    if (count($text) == 2) {
        // Check the validity of the identifier part of the string.
        if (clean_param($text[0], PARAM_STRINGID) !== '') {
            // Treat this as atext language string.
            $title = get_string($text[0], $text[1]);
        }
    }
    return $title;
}
