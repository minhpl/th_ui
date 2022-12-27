<?php
$string['pluginname'] = 'Manage Campaign';
$string['submit'] = 'Submit';
$string['no.'] = 'No.';
$string['campaigncode'] = 'Campaign Code';
$string['campaignname'] = 'Campaign Name';
$string['campaigndescription'] = 'Description';
$string['viewall'] = 'Sum Campaigns: ';
$string['allcampaigns'] = 'All Campaigns';
$string['addcampaigns'] = 'Add Campaign';
$string['th_vmc_campaign:view'] = 'Manage Campaign';
$string['th_vmc_campaign:addinstance'] = 'Add a new th_vmc_campaign block';
$string['th_vmc_campaign:myaddinstance'] = 'Add a new th_vmc_campaign block to the My Moodle page';
$string['content'] = 'Click the link below to Manage Campaign';
$string['reportlink'] = 'Manage Campaign';
$string['textfields'] = 'Filter';
$string['confirm'] = 'Are you sure you want delete {$a->campaignname}?';
$string['editcampaign'] = 'Edit Campaign';
$string['addcampaign'] = 'Add new Campaign';
$string['delcampaign'] = 'Delete Campaign';
$string['total'] = 'Campaign {$a->name}: {$a->total}';
$string['delete_campaign_successful'] = 'Delete campaign successful';
$string['delete_campaign_error'] = 'Delete failed Campaign. Because there are still users in it';

$string['th_bulkenrol_form_intro'] = 'Here, you can bulk enrol users to Campaign. A user to be enrolled is identified by his e-mail address stored in his Moodle account.';
$string['enrol_users_successful'] = 'Add bulk successful';
$string['unenrol_users_successful'] = 'Delete bulk successful';

$string['enrol_users'] = 'Enroll users';
$string['unenrol_users'] = 'Unenroll users';

$string['add_users'] = 'Add users Campaign';
$string['del_users'] = 'Delete users Campaign';

$string['error_no_course'] = 'No course found in line {$a->line} (<em>{$a->content}</em>). This line will be ignored.';

$string['enrolplugin_desc'] = 'The enrolment method to be used to bulk enrol the users. If the configured enrolment method is not active / added in the course when the users are bulk-enrolled, it is automatically added / activated.';
$string['error_enrol_users'] = 'There was a problem when enrolling the users to the course.';
$string['error_enrol_user'] = 'There was a problem when enrolling the user with e-mail <em>{$a->email}</em> to the course.';
$string['error_exception_info'] = 'Exception information';
$string['error_getting_user_for_email'] = 'There was a problem when getting the user record for e-mail address <em>{$a}</em> from the database.';

$string['error_invalid_email'] = 'Invalid e-mail address found in line {$a->row} (<em>{$a->email}</em>). This line will be ignored.';
$string['error_more_than_one_record_for_email'] = 'More than one existing Moodle user account with e-mail address <em>{$a}</em>em> found.<br /> This line will be ignored, none of the existing Moodle users will be enrolled.';
$string['error_no_email'] = 'No e-mail address found in line {$a->line} (<em>{$a->content}</em>). This line will be ignored.';
$string['error_no_valid_email_in_list'] = 'No valid e-mail address was found in the given list.<br />Please <a href=\'{$a->url}\'>go back and check your input</a>.';
$string['error_no_record_found_for_email'] = 'No existing Moodle user account with e-mail address <em>{$a}</em>.<br />This line will be ignored, there won\'t be a Moodle user account created on-the-fly.';
$string['error_usermails_empty'] = 'List of e-mail addresses is empty. Please add at least one e-mail address.';

$string['usermails_help'] = 'To enrol an existing Moodle user into this course, add his e-mail address to this form, one user / e-mail address per line.<br /><br />Example:<br />alice@example.com<br />bob@example.com<br /><br />Optionally, you are able to create groups and add the enrolled users to the groups. All you have to do is to add a heading line with a hash sign and the group\'s name, separating the list of users.<br /><br />Example:<br /># Group 1<br />alice@example.com<br />bob@example.com<br /># Group 2<br />carol@example.com<br />dave@example.com';

$string['hints'] = 'Hints';
$string['row'] = 'Row';
$string['usermails'] = 'List of e-mail,course shortname';
$string['users_to_enrol_in_course'] = 'Users to be add into the Campaigns';
$string['users_to_unenrol_in_course'] = 'Users to be delete into the Campaigns';

$string['user_enroled_yes'] = 'User will be add';
$string['user_enroled_already'] = 'User is already';
$string['user_will_be_unenrolled'] = 'User will be delete';
$string['user_unenrolled_no'] = 'No in Campaign';
$string['confirm_delete_user'] = 'Are you sure you want delete {$a->username} {$a->fullname}?';
$string['delete_successful'] = 'Delete successful';
$string['delete_error'] = 'Delete error';
$string['delete_user_campaign_course'] = 'Delete record';