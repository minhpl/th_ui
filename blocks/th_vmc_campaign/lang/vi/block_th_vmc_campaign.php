<?php
$string['pluginname'] = 'Quản lý Chiến dịch';
$string['submit'] = 'Tra cứu';
$string['no.'] = 'STT';
$string['campaigncode'] = 'Mã Chiến dịch';
$string['campaignname'] = 'Tên Chiến dịch';
$string['campaigndescription'] = 'Mô tả';
$string['viewall'] = 'Tổng số Chiến dịch: ';
$string['allcampaigns'] = 'Tất cả Chiến dịch';
$string['addcampaigns'] = 'Thêm Chiến dịch';
$string['th_vmc_campaign:view'] = 'Quản lý Chiến dịch';
$string['th_vmc_campaign:addinstance'] = 'Thêm khối Quản lý Chiến dịch';
$string['th_vmc_campaign:myaddinstance'] = 'Thêm khối Quản lý Chiến dịch mới vào trang Moodle của tôi';
$string['content'] = 'Bấm vào liên kết dưới đây để Quản lý Chiến dịch';
$string['reportlink'] = 'Quản lý Chiến dịch';
$string['textfields'] = 'Lọc';
$string['confirm'] = 'Bạn có chắc chắn muốn xóa Chiến dịch {$a->campaignname}?';
$string['editcampaign'] = 'Sửa Chiến dịch';
$string['addcampaign'] = 'Thêm một Chiến dịch';
$string['delcampaign'] = 'Xóa Chiến dịch';
$string['total'] = 'Chiến dịch {$a->name}: {$a->total}';
$string['delete_campaign_successful'] = 'Xóa Chiến dịch thành công';
$string['delete_campaign_error'] = 'Xóa Chiến dịch không thành công. Vì vẫn còn người dùng trong đó';

$string['th_bulkenrol_form_intro'] = 'Tại đây, bạn có thể thêm, xóa hàng loạt người dùng trong chiến dịch của mình. Người dùng được thêm, xóa được xác định bằng e-mail,shortname của khóa học.';
$string['enrol_users_successful'] = 'Thêm theo lô thành công';
$string['unenrol_users_successful'] = 'Xóa người dùng thành công';

$string['enrol_users'] = 'Thêm người dùng';
$string['unenrol_users'] = 'Xóa người dùng';

$string['add_users'] = 'Thêm người dùng vào Chiến dịch';
$string['del_users'] = 'Xóa người dùng khỏi Chiến dịch';

$string['error_no_course'] = 'No course found in line {$a->line} (<em>{$a->content}</em>). This line will be ignored.';

$string['enrolplugin_desc'] = 'The enrolment method to be used to bulk enrol the users. If the configured enrolment method is not active / added in the course when the users are bulk-enrolled, it is automatically added / activated.';
$string['error_enrol_users'] = 'There was a problem when enrolling the users to the course.';
$string['error_enrol_user'] = 'There was a problem when enrolling the user with e-mail <em>{$a->email}</em> to the course.';
$string['error_exception_info'] = 'Exception information';
$string['error_getting_user_for_email'] = 'There was a problem when getting the user record for e-mail address <em>{$a}</em> from the database.';

$string['error_invalid_email'] = 'Địa chỉ email không hợp lệ ở dòng {$a->row} (<em>{$a->email}</em>). Dòng này sẽ được bỏ qua.';
$string['error_more_than_one_record_for_email'] = 'More than one existing Moodle user account with e-mail address <em>{$a}</em>em> found.<br /> This line will be ignored, none of the existing Moodle users will be enrolled.';
$string['error_no_email'] = 'No e-mail address found in line {$a->line} (<em>{$a->content}</em>). This line will be ignored.';
$string['error_no_valid_email_in_list'] = 'Địa chỉ email không hợp lệ.<br />Please <a href=\'{$a->url}\'>go back and check your input</a>.';
$string['error_no_record_found_for_email'] = 'Không có tài khoản người dùng hiện có với địa chỉ e-mail <em>{$a}</em>.<br />This line will be ignored, there won\'t be a Moodle user account created on-the-fly.';
$string['error_usermails_empty'] = 'Danh sách địa chỉ e-mail trống. Vui lòng thêm ít nhất một địa chỉ e-mail.';
$string['usermails_help'] = 'Để đăng ký một người dùng Moodle hiện có vào Chiến dịch này, hãy thêm địa chỉ e-mail của anh ấy, shortname khóa học vào biểu mẫu này, một người dùng / địa chỉ e-mail,shortname khóa học trên mỗi dòng. <br /> <br /> Ví dụ: <br /> alice@example.com,TNU bob@example.com,MMT <br />';

$string['hints'] = 'Gợi ý';
$string['row'] = 'Dòng';
$string['usermails'] = 'Danh sách email,shortname';
$string['users_to_enrol_in_course'] = 'Các người dùng sẽ được thêm vào Chiến dịch';
$string['users_to_unenrol_in_course'] = 'Các người dùng sẽ được xóa khỏi Chiến dịch';

$string['user_enroled_yes'] = 'Người dùng sẽ được thêm';
$string['user_enroled_already'] = 'Người dùng đã được thêm rồi';
$string['user_will_be_unenrolled'] = 'Người dùng sẽ bị xóa';
$string['user_unenrolled_no'] = 'Chưa có trong Chiến dịch';
$string['confirm_delete_user'] = 'Bạn có chắc chắn muốn xóa {$a->username} {$a->fullname}?';
$string['delete_successful'] = 'Xóa thành công';
$string['delete_error'] = 'Xóa không thành công';
$string['delete_user_campaign_course'] = 'Xóa người dùng trong chiến dịch';