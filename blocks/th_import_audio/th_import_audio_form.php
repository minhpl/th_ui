<?php

    require_once($CFG->dirroot . '/lib/formslib.php');
    require_once("lib.php");

    class th_import_audio_form extends moodleform {

        function definition() {
            global $DB;
            $mform = $this->_form;
            $mform->addElement('header', 'displayinfo', get_string('filter'));

            $listcourses = th_import_audio_list_courses();
            $options = array(                                                                                                           
                'multiple' => false,                                                  
                'noselectionstring' => 'Chưa chọn',                                                              
            );  
            $element = $mform->addElement('autocomplete', 'course_id', 'Chọn khóa học', $listcourses, $options);

            $link = "<a href='example.zip'>example.zip</a>";
            $mform->addElement('static', 'example', 'Tệp zip mẫu', $link);		

            $mform->addElement('filepicker', 'newfile', get_string('import'), null,
                array(
                    'accepted_types' => array('.zip'),
                    'areamaxbytes' => 100000000,
                    'maxfiles' => 1,
                )
            );

            $mform->addRule('newfile', null, 'required', null, 'client');

            $this->add_action_buttons(true,  get_string('submit'));

            $mform->addElement('static', 'note', '<strong>Lưu ý</strong>', '<strong>Tên file audio là tên của câu hỏi trong ngân hàng câu hỏi.</strong>');
        }
        
        function validation($data, $files) {
            if($data['course_id'] == 0){
                return array('course_id' => 'Chưa có khóa học nào được chọn. Vui lòng chọn khóa học.');
            }
        }
    }

    class confirm_form extends moodleform {

    protected function definition() {
        global $SESSION;

        $th_import_audio_key = $this->_customdata['th_import_audio_key'];

        $mform = $this->_form;

        $mform->addElement('hidden', 'key');
        $mform->setType('key', PARAM_RAW);
        $mform->setDefault('key', $th_import_audio_key);

        $showbutton = true;
        $check_import = null;
        if (isset($SESSION->block_th_import_audio) && array_key_exists($th_import_audio_key, $SESSION->block_th_import_audio)) {
            $check_import = $SESSION->block_th_import_audio[$th_import_audio_key];
            if (isset($check_import->valid_import_found) && empty($check_import->valid_import_found)) {
                $showbutton = false;
            }
        }

        if ($showbutton) {

            $buttonstring = 'submit';

            $this->add_action_buttons(true, $buttonstring);
        }
    }
}
?>
