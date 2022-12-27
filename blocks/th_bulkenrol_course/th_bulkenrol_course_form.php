<?php

    use \block_th_bulkenrol_course\libs;
    require_once($CFG->dirroot . '/lib/formslib.php');
    require_once($CFG->libdir.'/csvlib.class.php');
    require_once($CFG->dirroot . '/user/editlib.php');

    class th_bulkenrol_course_form extends moodleform {

        function definition() {
            global $DB;
            $mform = $this->_form; 

            $mform->addElement('header', 'settingsheader', get_string('upload'));

            $url = new moodle_url('example.csv');
            $link = html_writer::link($url, 'example.csv');
            $mform->addElement('static', 'examplecsv', get_string('examplecsv', 'block_th_bulkenrol_course'), $link);
            $mform->addHelpButton('examplecsv', 'examplecsv', 'tool_uploaduser');

            $mform->addElement('filepicker', 'usermails', get_string('file'));
            $mform->addRule('usermails', null, 'required');

            // Add form content if the user came back to check his input.
            $localth_bulkenrol_csveditlist = optional_param('editlist', 0, PARAM_ALPHANUMEXT);
            if (!empty($localth_bulkenrol_csveditlist)) {
                $localth_bulkenrol_csvdata = $localth_bulkenrol_csveditlist . '_data';
                if (!empty($localth_bulkenrol_csvdata) && !empty($SESSION->local_th_bulkenrol_csv_inputs) &&
                    array_key_exists($localth_bulkenrol_csvdata, $SESSION->local_th_bulkenrol_csv_inputs)) {
                    $formdata = $SESSION->local_th_bulkenrol_csv_inputs[$localth_bulkenrol_csvdata];
                    $formdatatmp = $formdata->usermails;

                    $identifiertype = $formdata->identifiertype;
                    $option = $formdata->option;

                    $mform->setDefault('usermails', $formdatatmp);
                    $mform->setDefault('options', $option);
                    $mform->setDefault('identifiertype', $identifiertype);
                }
            }
            $mform->addElement('static', 'note', get_string('note', 'block_th_bulkenrol_course'), get_string('description', 'block_th_bulkenrol_course'));

            $this->add_action_buttons(true,  get_string('submit', 'block_th_bulkenrol_course'));
        }
        
        function validation($data, $files) {
            $retval = array();

            if (empty($data['usermails'])) {
                $retval['usermails'] = get_string('error_usermails_empty', 'local_th_bulkenrol_csv');
            }

            return $retval;
        }

    }
?>
