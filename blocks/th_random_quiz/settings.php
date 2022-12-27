<?php

$settings->add(
	new admin_setting_configtext(
		'block_th_random_quiz/course_save',
		'Chọn khóa học lưu trữ xuất đề thi',
		'Vui lòng nhập shortname khóa học lưu trữ',
		'',
		PARAM_TEXT)
);