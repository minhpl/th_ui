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
 * Lang strings for the My overview block.
 *
 * @package    block_th_activatecourses
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['th_activatecourses:addinstance'] = 'Add a new th_activatecourses block';
$string['allincludinghidden'] = 'All';
$string['all'] = 'All (except removed from view)';
$string['addtofavourites'] = 'Star this course';
$string['aria:addtofavourites'] = 'Star for';
$string['aria:allcoursesincludinghidden'] = 'Show all courses';
$string['aria:allcourses'] = 'Show all courses except courses removed from view';
$string['aria:card'] = 'Switch to card view';
$string['aria:controls'] = 'Course overview controls';
$string['aria:courseactions'] = 'Actions for current course';
$string['aria:coursesummary'] = 'Course summary text:';
$string['aria:courseprogress'] = 'Course progress:';
$string['aria:customfield'] = 'Show {$a} courses';
$string['aria:displaydropdown'] = 'Display drop-down menu';
$string['aria:favourites'] = 'Show starred courses';
$string['aria:future'] = 'Show future courses';
$string['aria:groupingdropdown'] = 'Grouping drop-down menu';
$string['aria:inprogress'] = 'Show courses in progress';
$string['aria:lastaccessed'] = 'Sort courses by last accessed date';
$string['aria:list'] = 'Switch to list view';
$string['aria:title'] = 'Sort courses by course name';
$string['aria:past'] = 'Show past courses';
$string['aria:removefromfavourites'] = 'Remove star for';
$string['aria:shortname'] = 'Sort courses by course short name';
$string['aria:summary'] = 'Switch to summary view';
$string['aria:sortingdropdown'] = 'Sorting drop-down menu';
$string['availablegroupings'] = 'Available filters';
$string['availablegroupings_desc'] = 'Course filters which are available for selection by users. If none are selected, all courses will be displayed.';
$string['card'] = 'Card';
$string['cards'] = 'Cards';
$string['courseprogress'] = 'Course progress:';
$string['completepercent'] = '{$a}% complete';
$string['customfield'] = 'Custom field';
$string['customfiltergrouping'] = 'Field to use';
$string['customfiltergrouping_nofields'] = 'This option requires a course custom field to be set up and visible to everyone.';
$string['displaycategories'] = 'Display categories';
$string['displaycategories_help'] = 'Display the course category on dashboard course items including cards, list items and summary items.';
$string['favourites'] = 'Starred';
$string['future'] = 'Future';
$string['inprogress'] = 'In progress';
$string['lastaccessed'] = 'Last accessed';
$string['layouts'] = 'Available layouts';
$string['layouts_help'] = 'Course overview layouts which are available for selection by users. If none are selected, the card layout will be used.';
$string['list'] = 'List';
$string['th_activatecourses:myaddinstance'] = 'Add a new course overview block to Dashboard';
$string['th_activatecourses:view'] = 'Can view this block';

$string['nocustomvalue'] = 'No {$a}';
$string['past'] = 'Past';
$string['pluginname'] = 'Các khóa học chưa kích hoạt';
$string['privacy:metadata:overviewsortpreference'] = 'The Course overview block sort preference.';
$string['privacy:metadata:overviewviewpreference'] = 'The Course overview block view preference.';
$string['privacy:metadata:overviewgroupingpreference'] = 'The Course overview block grouping preference.';
$string['privacy:metadata:overviewpagingpreference'] = 'The Course overview block paging preference.';
$string['removefromfavourites'] = 'Unstar this course';
$string['shortname'] = 'Short name';
$string['summary'] = 'Summary';
$string['title'] = 'Course name';
$string['aria:hidecourse'] = 'Remove {$a} from view';
$string['aria:showcourse'] = 'Restore {$a} to view';
$string['aria:hiddencourses'] = 'Show courses removed from view';
$string['hidden'] = 'Courses removed from view';
$string['hidecourse'] = 'Remove from view';
$string['hiddencourses'] = 'Removed from view';
$string['show'] = 'Restore to view';
$string['privacy:request:preference:set'] = 'The value of the setting \'{$a->name}\' was \'{$a->value}\'';

// Deprecated since Moodle 3.7.
$string['complete'] = 'complete';
$string['nocourses'] = 'No courses';

$string['breadcrumb'] = 'Kích hoạt khóa học';
$string['heading'] = 'Kích hoạt khóa học';
$string['activepagetitle'] = 'Kích hoạt khóa học';

$string['showemptyblockonlyforadmin'] = 'Show empty block only for admin';
$string['displayemptyblockonlyforadmin'] = 'Display empty block for only admin';
$string['displayemptyblockonlyforadmin_help'] = 'If this setting is checked, only admin can see the empty blocks, and other user still cannot see empty block. If user have one or more registered course, this block still display for that user';

$string['showemptyblockonlyforadmin'] = 'Chỉ hiển thị khối trống cho admin';
$string['displayemptyblockonlyforadmin'] = 'Chỉ hiển thị khối trống cho admin';
$string['displayemptyblockonlyforadmin_help'] = 'Nếu thiết lập này được chọn, Chỉ admin mới có thể nhìn thấy khối này, Người dùng khác sẽ không thấy khối này. Nếu người dùng có một hoặc nhiều khóa học đã đăng kí, khối này vẫn sẽ hiển thị';

$string['activatecourse'] = 'Kích hoạt khóa học';
$string['activate'] = 'Kích hoạt';
$string['activateconfirm'] = 'Khoá học {$a->course} có thời hạn sử dụng là {$a->duration} kể từ ngày kích hoạt. Bạn có chắc chắn muốn kích hoạt bây giờ ko?';
$string['activateconfirm_nolimit'] = 'Bạn có chắc chắn muốn kích hoạt bây giờ ko?';

$string['title'] = 'Thông Báo Kích hoạt Khóa học';
// $string['body'] = 'Xin chào {$a->userfullname}!
//                     Bạn đã kích hoạt thành công khoá học {$a->coursefullname}.
//                     Khoá học của bạn có thời hạn {$a->duration} ngày kể từ ngày kích hoạt.
//                     Hãy tranh thủ thời gian để học tập, chăm sóc sức khoẻ chủ động cho bản thân và những những người thân yêu nhé!
//                     Sức khỏe của bạn là niềm vui của chúng tôi.

//                     Nếu có bất cứ thắc mắc gì, vui lòng liên hệ:
//                         • Hotline: 096 600 0643
//                         • Email: trungtamvmc@gmail.com

//                     Trân trọng cảm ơn!
//                     Trung Tâm VMC Việt Nam';

$string['body'] = 'Xin chào {$a->userfullname}!

Bạn đã kích hoạt thành công  khoá học {$a->coursefullname}.
Khoá học của bạn có thời hạn {$a->duration} kể từ ngày kích hoạt. Hãy tranh thủ thời gian để học tập, chăm sóc sức khoẻ chủ động cho bản thân và những những người thân yêu nhé!
Sức khỏe của bạn là niềm vui của chúng tôi.


Nếu có bất cứ thắc mắc gì, vui lòng liên hệ:
-   Hotline: 096 600 0643
-   Email: trungtamvmc@gmail.com

Trân trọng cảm ơn!
Trung Tâm VMC Việt Nam
';

$string['nolimit'] = 'không giới hạn';