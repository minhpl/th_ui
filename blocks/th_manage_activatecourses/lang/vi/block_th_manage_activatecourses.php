<?php
$string['pluginname'] = 'Quản lý Kích hoạt Khóa học';
$string['th_manage_activatecourses:view'] = 'Quản lý kích hoạt khóa học';
$string['th_manage_activatecourses:addinstance'] = 'Thêm khối Quản lý Kích hoạt khóa học';
$string['th_manage_activatecourses:myaddinstance'] = 'Thêm khối Quản lý Kích hoạt khóa học mới vào trang Moodle của tôi';
$string['content'] = 'Bấm vào liên kết dưới đây để Quản lý Kích hoạt khóa học';
$string['reportlink'] = 'Quản lý Kích hoạt khóa học';
$string['no_selection'] = 'Chưa chọn';
$string['choose_a_course'] = 'Chọn một Khóa học';
$string['choose_a_user'] = 'Chọn một Học viên';
$string['searchcourse'] = 'Chọn Khóa học';
$string['searchuser'] = 'Chọn Người dùng';
$string['confirm'] = 'Bạn có chắc chắn muốn xóa Đăng ký {$a->user} ra khỏi khóa học {$a->course}?';
$string['edit'] = 'Sửa Kích hoạt Khóa học';
$string['delete'] = 'Xóa Kích hoạt Khóa học';
$string['no.'] = 'STT';
$string['user'] = 'Tên Học viên';
$string['course'] = 'Tên Khóa học';
$string['timecreated'] = 'Ngày tạo';
$string['timeactivated'] = 'Ngày kích hoạt';
$string['view'] = 'Hệ thống: các Khóa học chưa Kích hoạt hiện có: ';
$string['all'] = 'Tất cả';
$string['add'] = 'Đăng ký';
$string['delete'] = 'Hủy đăng ký';
$string['add_users'] = 'Đăng ký theo lô';
$string['del_users'] = 'Hủy đăng ký theo lô';
$string['error_no_valid_email_in_list'] = 'Địa chỉ email không hợp lệ.<br />Please <a href=\'{$a->url}\'>go back and check your input</a>.';
$string['th_bulkenrol_form_intro'] = 'Tại đây, bạn có thể Đăng ký, hủy đăng ký hàng loạt người dùng. Người dùng được Đăng ký, hủy đăng ký được xác định bằng e-mail,shortname của khóa học.';
$string['usermails'] = 'Danh sách email,shortname';
$string['usermails_help'] = 'Để đăng ký một người dùng Moodle hiện có vào Chiến dịch này, hãy thêm địa chỉ e-mail của anh ấy, shortname khóa học vào biểu mẫu này, một người dùng / địa chỉ e-mail,shortname khóa học trên mỗi dòng. <br /> <br /> Ví dụ: <br /> alice@example.com,UNISHORTNAME bob@example.com,MMT <br />';
$string['error_no_course'] = 'No course found in line {$a->line} (<em>{$a->content}</em>). This line will be ignored.';
$string['error_no_email'] = 'No e-mail address found in line {$a->line} (<em>{$a->content}</em>). This line will be ignored.';
$string['error_invalid_email'] = 'Địa chỉ email không hợp lệ ở dòng {$a->row} (<em>{$a->email}</em>). Dòng này sẽ được bỏ qua.';
$string['error_more_than_one_record_for_email'] = 'More than one existing Moodle user account with e-mail address <em>{$a}</em>em> found.<br /> This line will be ignored, none of the existing Moodle users will be enrolled.';
$string['error_no_record_found_for_email'] = 'Không có tài khoản người dùng hiện có với địa chỉ e-mail <em>{$a}</em>.<br />This line will be ignored, there won\'t be a Moodle user account created on-the-fly.';
$string['error_getting_user_for_email'] = 'There was a problem when getting the user record for e-mail address <em>{$a}</em> from the database.';
$string['error_exception_info'] = 'Exception information';
$string['hints'] = 'Gợi ý';
$string['row'] = 'Dòng';
$string['user_enroled_yes'] = 'Người dùng sẽ được thêm';
$string['user_enroled_already'] = 'Người dùng đã được thêm rồi';
$string['users_to_enrol_in_course'] = 'Các người dùng sẽ được thêm';
$string['error_enrol_user'] = 'There was a problem when enrolling the user with e-mail <em>{$a->email}</em> to the course.';
$string['error_enrol_users'] = 'There was a problem when enrolling the users to the course.';
$string['enrol_users_successful'] = 'Thêm theo lô thành công';
$string['unenrol_users_successful'] = 'Xóa người dùng thành công';
$string['users_to_unenrol_in_course'] = 'Các người dùng sẽ bị xóa';

$string['user_will_be_unenrolled'] = 'Người dùng sẽ bị xóa';
$string['user_unenrolled_no'] = 'Chưa đăng ký';
$string['confirm_delete_user'] = 'Bạn có chắc chắn muốn xóa {$a->username} {$a->fullname}?';
$string['delete_successful'] = 'Xóa thành công';
$string['delete_error'] = 'Xóa không thành công';
$string['delete_user_course'] = 'Xóa người dùng';
$string['successful'] = 'Thêm thành công';
$string['error'] = 'Thêm không thành công. Vì người dùng đã được đăng ký rồi!';

$string['campaign'] = 'Chọn Chiến dịch';
$string['choose_a_campaign'] = 'Chọn một Chiến dịch';
$string['enrol_users'] = 'Thêm người dùng';
$string['unenrol_users'] = 'Xóa người dùng';
$string['error_usermails_empty'] = 'Danh sách địa chỉ e-mail trống. Vui lòng thêm ít nhất một địa chỉ e-mail.';

$string['add_failed_user_enrolled'] = 'Thêm không thành công. Vì người dùng đã được ghi danh vào Khóa học';
$string['add_failed_user_registered'] = 'Thêm không thành công. Vì người dùng đã được đăng ký Kích hoạt Khóa học';
$string['add_failed_user_campaign'] = 'Thêm không thành công. Vì người dùng đã được đăng ký vào Chiến dịch';
$string['success'] = 'Thêm thành công';
$string['campaign_name'] = 'Chiến dịch';
$string['edit_success'] = 'Sửa thành công';
$string['edit_failed'] = 'Sửa không thành công. Vì bản ghi đã tồn tại';

$string['title'] = 'Thông Báo Gán Khóa học';
// $string['body'] = 'Xin chào {$a->userfullname}!
//                      Bạn đã được gán vào khoá học {$a->coursefullname}.
//                      Hãy đăng nhập và Kích hoạt khóa học tại: {$a->linkactive}

//                      Nếu bạn chưa nhận được thông tin tài khoản đăng nhập của mình qua email,
//                      vui lòng kiểm tra trong thư mục Quảng cáo / Spam hoặc liên hệ với chúng tôi qua đường dây nóng/Zalo 0395414348,
//                      hoặc gửi email tới trungtamvmc@gmail.com để được hỗ trợ.
//                      Nếu có bất cứ thắc mắc gì, vui lòng liên hệ:
//                           • Hotline: 096 600 0643
//                           • Email: trungtamvmc@gmail.com
//                      Trân trọng cảm ơn!
//                      Trung Tâm VMC Việt Nam';
$string['body'] = 'Xin chào {$a->userfullname}!

Bạn đã được gán vào khoá học {$a->coursefullname}.
Hãy đăng nhập và Kích hoạt khóa học tại: {$a->linkactive}

Nếu bạn chưa nhận được thông tin tài khoản đăng nhập của mình qua email, vui lòng kiểm tra trong thư mục Quảng cáo / Spam hoặc liên hệ với chúng tôi qua đường dây nóng/Zalo 0395414348, hoặc gửi email tới trungtamvmc@gmail.com để được hỗ trợ.

Nếu có bất cứ thắc mắc gì, vui lòng liên hệ:
-   Hotline: 096 600 0643
-   Email: trungtamvmc@gmail.com

Trân trọng cảm ơn!
Trung Tâm VMC Việt Nam
';
