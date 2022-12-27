<?php
$settings->add(new admin_setting_heading(
            'headerconfig',
            get_string('headerconfig', 'block_th_course_enrolment_report'),
            get_string('descconfig', 'block_th_course_enrolment_report')
        ));
 
$settings->add(new admin_setting_configcheckbox(
            'simplehtml/Allow_HTML',
            get_string('labelallowhtml', 'block_th_course_enrolment_report'),
            get_string('descallowhtml', 'block_th_course_enrolment_report'),
            '0'
        ));