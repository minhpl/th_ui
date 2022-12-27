<?php

    use \block_th_course_enrolment_report\libs;
    require_once($CFG->dirroot . '/lib/formslib.php');
    require_once "{$CFG->libdir}/formslib.php";
    require_once $CFG->dirroot . '/local/thlib/lib.php';
    require_once $CFG->dirroot . '/local/thlib/th_form.php';

    class th_course_enrolment_report_form extends moodleform {

        function definition() {
            global $DB;
            $mform = $this->_form;
            
            $mform->addElement('header', 'displayinfo', get_string('textfields', 'block_th_course_enrolment_report'));

            $mform->addElement('date_selector', 'startdate', get_string('fromdate', 'block_th_course_enrolment_report'));
            $mform->addElement('date_selector', 'enddate', get_string('todate', 'block_th_course_enrolment_report'));

            $libs = new libs();
            $listcourses = $libs->get_list_courses();
            $liststudents = $libs->get_list_students();

            $options1 = array(                                                                                                           
                'multiple' => true,                                                  
                'noselectionstring' => get_string('allcourses', 'block_th_course_enrolment_report'),                                                                
            );  
            $mform->addElement('autocomplete', 'course_id', get_string('course_id', 'block_th_course_enrolment_report'), $listcourses, $options1);

            $options2 = array(                                                                                                           
                'multiple' => true,                                                 
                'noselectionstring' => get_string('allusers', 'block_th_course_enrolment_report'),                                                                
            );  
            $mform->addElement('autocomplete', 'userid', get_string('userid', 'block_th_course_enrolment_report'), $liststudents, $options2);


            $this->add_action_buttons(true,  get_string('submit', 'block_th_course_enrolment_report'));
        }
        
        function validation($data, $files) {
            
            if ($data['enddate'] < $data['startdate']) {
                return array('enddate' => get_string('date_err1', 'block_th_course_enrolment_report'));
            } else {
                $day_count = ($data['enddate'] - $data['startdate']) / (24*60*60);

                if($day_count > 31){
                    return array('enddate' => get_string('date_err2', 'block_th_course_enrolment_report'));
                }
            }

            return array();
        }
    }
?>
