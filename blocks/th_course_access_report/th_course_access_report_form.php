<?php

require_once $CFG->dirroot . '/blocks/th_course_access_report/classes/function.php';

class th_course_access_report_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $COURSE;

        $mform = $this->_form;
        $mform->addElement('header', 'displayinfo', get_string('textfields', 'block_th_course_access_report'));

        $from_date = get_string('from_date', 'block_th_course_access_report');
        $mform->addElement('date_selector', 'from_date', $from_date);
        $date = (new DateTime())->setTimestamp(usergetmidnight(time()));
        $number_day_config = get_config('block_th_course_access_report', 'date');
        $date->modify('-' . $number_day_config . 'day');
        $mform->setDefault('from_date', $date->getTimestamp());

        $to_date = get_string('to_date', 'block_th_course_access_report');
        $mform->addElement('date_selector', 'to_date', $to_date);

        $config = get_config('local_thlib');
        $sortorder = "lastname,firstname";
        if ($config->sortorder == 1) {
            $sortorder = "firstname,lastname";
        }

        $this->course_arr = \th_course_access_report\lib::get_allcourseid_form($mform);
        $this->user_arr = get_userid_th_course_access_report_form($mform, $sortorder, false);

        $radio_arr = array();
        $option1 = get_string('option1', 'block_th_course_access_report');
        $option2 = get_string('option2', 'block_th_course_access_report');
        $radio_arr[] = $mform->createElement('radio', 'show_option', '', $option1, '1');
        $radio_arr[] = $mform->createElement('radio', 'show_option', '', $option2, '2');
        $title_option = get_string('title_option', 'block_th_course_access_report');
        $mform->addGroup($radio_arr, 'radioar', $title_option, array(''), false);
        $mform->setDefault('show_option', '1');

        $this->add_action_buttons(true, get_string('submmit', 'block_th_course_access_report'));
    }

    function validation($data, $files) {

        $config = get_config('block_th_course_access_report');
        $number_day_config = $config->date;
        $one_day = 60 * 60 * 24; // so giay trong 1 ngay
        $number_day = $number_day_config * $one_day;

        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $number_day_diff = $to_date - $from_date;

        if ($number_day < $number_day_diff) {
            return array('to_date' => get_string('error_number_day', 'block_th_course_access_report', array('day' => $number_day_config)));
        }

        return array();
    }
}