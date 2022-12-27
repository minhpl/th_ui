<?php
$string['pluginname'] = 'Manage Active Course';
$string['th_manage_activatecourses:view'] = 'View Manage Active Course';
$string['th_manage_activatecourses:addinstance'] = 'Add a new th_manage_activatecourses block';
$string['th_manage_activatecourses:myaddinstance'] = 'Add a new th_manage_activatecourses block to the My Moodle page';
$string['content'] = 'Click the link below to Manage Active Course';
$string['reportlink'] = 'Manage Active Course';
$string['no_selection'] = 'No Selection';
$string['choose_a_course'] = 'Choose a Course';
$string['choose_a_user'] = 'Choose a User';
$string['searchcourse'] = 'Select Courses';
$string['searchuser'] = 'Select User';
$string['confirm'] = 'Are you sure you want delete {$a->user} from the course {$a->course}?';
$string['edit'] = 'Edit Register Courses';
$string['delete'] = 'Delete Register Courses';
$string['no.'] = 'No.';
$string['user'] = 'Student Name';
$string['course'] = 'Course Name';
$string['timecreated'] = 'Time created';
$string['timeactivated'] = 'Time activated';
$string['view'] = 'System: available Register Courses: ';
$string['all'] = 'All';
$string['add'] = 'Register';
$string['delete'] = 'Unregisters';
$string['add_users'] = 'Register bulk';
$string['del_users'] = 'Unregister bulk';
$string['error_no_valid_email_in_list'] = 'No valid e-mail address was found in the given list.<br />Please <a href=\'{$a->url}\'>go back and check your input</a>.';
$string['th_bulkenrol_form_intro'] = 'Here, you can bulk register/unregister. A user to be register/unregister is identified by his e-mail address stored in his Moodle account.';
$string['usermails'] = 'List of e-mail,course shortname';
$string['usermails_help'] = 'To enrol an existing Moodle user into this course, add his e-mail address to this form, one user / e-mail address per line.<br /><br />Example:<br />alice@example.com<br />bob@example.com<br /><br />';
$string['error_no_course'] = 'No course found in line {$a->line} (<em>{$a->content}</em>). This line will be ignored.';
$string['error_no_email'] = 'No e-mail address found in line {$a->line} (<em>{$a->content}</em>). This line will be ignored.';
$string['error_invalid_email'] = 'Invalid e-mail address found in line {$a->row} (<em>{$a->email}</em>). This line will be ignored.';
$string['error_more_than_one_record_for_email'] = 'More than one existing Moodle user account with e-mail address <em>{$a}</em>em> found.<br /> This line will be ignored, none of the existing Moodle users will be enrolled.';
$string['error_no_record_found_for_email'] = 'No existing Moodle user account with e-mail address <em>{$a}</em>.<br />This line will be ignored, there won\'t be a Moodle user account created on-the-fly.';
$string['error_getting_user_for_email'] = 'There was a problem when getting the user record for e-mail address <em>{$a}</em> from the database.';
$string['error_exception_info'] = 'Exception information';
$string['hints'] = 'Hints';
$string['row'] = 'Row';
$string['user_enroled_yes'] = 'User will be add';
$string['user_enroled_already'] = 'User is already';
$string['users_to_enrol_in_course'] = 'Users to be add';
$string['error_enrol_user'] = 'There was a problem when enrolling the user with e-mail <em>{$a->email}</em> to the course.';
$string['error_enrol_users'] = 'There was a problem when enrolling the users to the course.';
$string['enrol_users_successful'] = 'Add bulk successful';
$string['unenrol_users_successful'] = 'Delete bulk successful';
$string['users_to_unenrol_in_course'] = 'Users to be delete into the Campaigns';

$string['user_will_be_unenrolled'] = 'User will be delete';
$string['user_unenrolled_no'] = 'No register';
$string['confirm_delete_user'] = 'Are you sure you want delete {$a->username} {$a->fullname}?';
$string['delete_successful'] = 'Delete successful';
$string['delete_error'] = 'Delete error';
$string['delete_user_course'] = 'Delete record';
$string['successful'] = 'Successful';
$string['error'] = 'Can not add. Because the user is already registered';

$string['campaign'] = 'Select Campaign';
$string['choose_a_campaign'] = 'Choose a Campaigns';
$string['enrol_users'] = 'Enroll users';
$string['unenrol_users'] = 'Unenroll users';
$string['error_usermails_empty'] = 'List of e-mail addresses is empty. Please add at least one e-mail address.';

$string['add_failed_user_enrolled'] = 'Add failed. Because the user is already enrolled in the Course';
$string['add_failed_user_registered'] = 'Add failed. Because the user is already registered Activate Course';
$string['add_failed_user_campaign'] = 'Add failed. Because the user is already registered to the Campaign';
$string['success'] = 'Add success';
$string['campaign_name'] = 'Campaign';
$string['edit_success'] = 'Edit success';
$string['edit_failed'] = 'Edit failed. Because the record already exists';

$string['title'] = 'Thông Báo Gán Khóa học';

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