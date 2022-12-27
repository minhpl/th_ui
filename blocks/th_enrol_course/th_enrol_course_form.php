<?php

    use \block_th_enrol_course\libs;
    require_once($CFG->dirroot . '/lib/formslib.php');
    require_once "{$CFG->libdir}/formslib.php";
    require_once $CFG->dirroot . '/local/thlib/lib.php';
    require_once $CFG->dirroot . '/local/thlib/th_form.php';

    class th_enrol_course_form extends moodleform {

        function definition() {
            global $DB;
            $mform = $this->_form;
            $mform->addElement('header', 'displayinfo', get_string('textfields', 'block_th_enrol_course'));

            $mform = $this->_form;
            $radioarray = array();
            $radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('radiomakhoa', 'block_th_enrol_course'), 0);
            $radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('radiodate', 'block_th_enrol_course'), 1);
            $mform->addGroup($radioarray, 'show_option', get_string('options', 'block_th_enrol_course'), array(''), false);
            

            $mform->setDefault('show_option', 0);

            $libs = new libs();
            $listcourses = $libs->get_list_courses();
            $liststudents = $libs->get_list_students();
            $list_role = $libs->get_list_role();
            
            $options = array(                                                                                                           
                'multiple' => true,                                                  
                'noselectionstring' => get_string('allcourses', 'block_th_enrol_course'),                                                                
            );  
            $element = $mform->addElement('autocomplete', 'course_id', get_string('course_id', 'block_th_enrol_course'), $listcourses, $options);
            $attributes = $element->getAttributes() + ['required' => 'true', 'class' => 'custom_required'];
            $element->setAttributes($attributes);
            

            $mform->addElement('date_selector', 'date', get_string('date', 'block_th_enrol_course'));
            $mform->disabledIf('course_id', 'show_option', 'neq', '0');
		    $mform->disabledIf('date', 'show_option', 'neq', '1');

            $mform->hideif('course_id', 'show_option', 'neq', '0');
		    $mform->hideif('date', 'show_option', 'neq', '1');

            $options2 = array(                                                                                                           
                'multiple' => true,                                                
                'noselectionstring' => get_string('allroles', 'block_th_enrol_course'),                                                                
            );  
            $mform->addElement('autocomplete', 'role', get_string('role', 'block_th_enrol_course'), $list_role, $options2);
            $mform->addRule('role', '', 'required', null, 'client', false, false);

            $options3 = array(                                                                                                           
                'multiple' => true,                                                 
                'noselectionstring' => get_string('allusers', 'block_th_enrol_course'),                                                                
            );  
            $mform->addElement('autocomplete', 'userid', get_string('userid', 'block_th_enrol_course'), $liststudents, $options3);
            $mform->addRule('userid', '', 'required', null, 'client', false, false);

            $this->add_action_buttons(true,  get_string('submit', 'block_th_enrol_course'));
        }
        
        function validation($data, $files) {
            return array();
        }

    }
?>
