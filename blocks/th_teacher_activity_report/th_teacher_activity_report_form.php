<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once $CFG->libdir . '/formslib.php';

class th_teacher_activity_report_form extends moodleform {

    /**
     * Form definition. Abstract method - always override!
     */
    protected function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        $mform->addElement('header', 'displayinfo', get_string('filter'));

        // option
        $radioarray = array();
        $radioarray[] = $mform->createElement('radio', 'show_option', '', get_string('option_start_date', 'block_th_teacher_activity_report'), 0);
        $radioarray[] = $mform->createElement('radio', 'show_option', '', get_string('option_course', 'block_th_teacher_activity_report'), 1);
        $radioarray[] = $mform->createElement('radio', 'show_option', '', get_string('option_teacher', 'block_th_teacher_activity_report'), 2);
        $mform->addGroup($radioarray, 'radioar', get_string('option', 'block_th_teacher_activity_report'), array(' '), false);
        $mform->setDefault('show_option', 0);

        // course start date
        $mform->addElement('date_selector', 'course_start_date', get_string('course_start_date', 'block_th_teacher_activity_report'));

        $sql = "SELECT * FROM {course} WHERE visible = 1 AND id <> 1 ORDER BY startdate DESC";
        $courses = $DB->get_records_sql($sql);
        $list_course = array();
        foreach ($courses as $key => $course) {
            $list_course[$key] = $course->fullname . ',' . $course->shortname;
        }
        $options = array(
            'multiple' => true,
            'noselectionstring' => get_string('no_select', 'block_th_teacher_activity_report'),
        );

        // list course
        $mform->addElement('autocomplete', 'list_course', get_string('option_course', 'block_th_teacher_activity_report'), $list_course, $options);

        $sql = "SELECT u.id,firstname,lastname,email,d.data
                FROM {user_info_data} d
                JOIN {user} u ON d.userid = u.id
                JOIN {user_info_field} f ON d.fieldid = f.id
                WHERE d.data LIKE 'Giảng viên' AND u.deleted = 0 AND u.suspended = 0";
        $teachers = $DB->get_records_sql($sql);
        $list_teacher = array();
        foreach ($teachers as $key => $teacher) {
            $list_teacher[$key] = $teacher->firstname . ' ' . $teacher->lastname . ',' . $teacher->email;
        }

        // list teacher
        $mform->addElement('autocomplete', 'list_teacher', get_string('teacher', 'block_th_teacher_activity_report'), $list_teacher, $options);
        // $mform->addRule('list_tearcher', null, 'required', null, 'client');

        $mform->addElement('static', 'description', '',
            get_string('hint', 'block_th_teacher_activity_report'));

        // From date
        $mform->addElement('date_selector', 'start_date', get_string('start_date', 'block_th_teacher_activity_report'));
        $date = (new DateTime())->setTimestamp(usergetmidnight(time()));
        $date->modify('-90 day');
        $mform->setDefault('start_date', $date->getTimestamp());

        // To date
        $mform->addElement('date_selector', 'end_date', get_string('end_date', 'block_th_teacher_activity_report'));

        $mform->disabledIf('list_course', 'show_option', 'eq', '0');
        $mform->disabledIf('list_teacher', 'show_option', 'eq', '0');
        $mform->hideif('list_course', 'show_option', 'eq', '0');
        $mform->hideif('list_teacher', 'show_option', 'eq', '0');

        $mform->disabledIf('course_start_date', 'show_option', 'eq', '1');
        $mform->disabledIf('list_teacher', 'show_option', 'eq', '1');
        $mform->hideif('course_start_date', 'show_option', 'eq', '1');
        $mform->hideif('list_teacher', 'show_option', 'eq', '1');

        $mform->disabledIf('course_start_date', 'show_option', 'eq', '2');
        $mform->disabledIf('list_course', 'show_option', 'eq', '2');
        $mform->hideif('course_start_date', 'show_option', 'eq', '2');
        $mform->hideif('list_course', 'show_option', 'eq', '2');

        $this->add_action_buttons(true, get_string('view'));
    }

    /**
     * Get each of the rules to validate its own fields
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {

        $mform = $this->_form;
        $retval = array();

        if ($data['show_option'] == 1) {
            if (empty($data['list_course'])) {
                $retval['list_course'] = get_string('no_select_course', 'block_th_teacher_activity_report');
                // $mform->addRule('list_course', null, 'required', null, 'client');
            }
        }
        if ($data['show_option'] == 2) {
            if (empty($data['list_teacher'])) {
                $retval['list_teacher'] = get_string('no_select_user', 'block_th_teacher_activity_report');
                // $mform->addRule('list_teacher', null, 'required', null, 'client');
            }
        }
        return $retval;
    }
}
