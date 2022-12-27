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
 * Contains the class for the My overview block.
 *
 * @package    block_th_activatecourses
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * My overview block class.
 *
 * @package    block_th_activatecourses
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_th_activatecourses extends block_base {

	/**
	 * Init.
	 */
	public function init() {
		$this->title = get_string('pluginname', 'block_th_activatecourses');
	}

	/**
	 * Returns the contents.
	 *
	 * @return stdClass contents of block
	 */
	public function get_content() {

		if (isset($this->content)) {
			return $this->content;
		}
		$group = get_user_preferences('block_th_activatecourses_user_grouping_preference');
		$sort = get_user_preferences('block_th_activatecourses_user_sort_preference');
		$view = get_user_preferences('block_th_activatecourses_user_view_preference');
		$paging = get_user_preferences('block_th_activatecourses_user_paging_preference');
		$customfieldvalue = get_user_preferences('block_th_activatecourses_user_grouping_customfieldvalue_preference');

		$renderable = new \block_th_activatecourses\output\main($group, $sort, $view, $paging, $customfieldvalue);
		$renderer = $this->page->get_renderer('block_th_activatecourses');

		$this->content = new stdClass();
		$this->content->text = $renderer->render($renderable);
		$this->content->footer = '';

		return $this->content;
	}

	public function applicable_formats() {
		return array('site-index' => true, 'course-view-*' => true);
	}

	/**
	 * Allow the block to have a configuration page.
	 *
	 * @return boolean
	 */
	public function has_config() {
		return true;
	}

	/**
	 * Return the plugin config settings for external functions.
	 *
	 * @return stdClass the configs for both the block instance and plugin
	 * @since Moodle 3.8
	 */
	public function get_config_for_external() {
		// Return all settings for all users since it is safe (no private keys, etc..).
		$configs = get_config('block_th_activatecourses');

		// Get the customfield values (if any).
		if ($configs->displaygroupingcustomfield) {
			$group = get_user_preferences('block_th_activatecourses_user_grouping_preference');
			$sort = get_user_preferences('block_th_activatecourses_user_sort_preference');
			$view = get_user_preferences('block_th_activatecourses_user_view_preference');
			$paging = get_user_preferences('block_th_activatecourses_user_paging_preference');

			$customfieldvalue = get_user_preferences('block_th_activatecourses_user_grouping_customfieldvalue_preference');

			$renderable = new \block_th_activatecourses\output\main($group, $sort, $view, 3, $customfieldvalue);
			$customfieldsexport = $renderable->get_customfield_values_for_export();
			if (!empty($customfieldsexport)) {
				$configs->customfieldsexport = json_encode($customfieldsexport);
			}
		}

		return (object) [
			'instance' => new stdClass(),
			'plugin' => $configs,
		];
	}

}

?>