<?php

require_once $CFG->dirroot . '/lib/formslib.php';
require_once "{$CFG->libdir}/formslib.php";

class edit_form extends moodleform
{

	public function definition()
	{
		global $DB;
		$mform = $this->_form;

		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_RAW);

		$mform->addElement('textarea', 'ma_lop', 'Mã lớp');
		$mform->setType('ma_lop', PARAM_NOTAGS);
		$mform->addRule('ma_lop', '', 'required', null, 'client', false, false);

		$mform->addElement('text', 'ho_ten', 'Họ tên');
		$mform->setType('ho_ten', PARAM_NOTAGS);
		$mform->addRule('ho_ten', '', 'required', null, 'client', false, false);

		$mform->addElement('text', 'sdt', 'Số điện thoại');
		$mform->setType('sdt', PARAM_NOTAGS);
		$mform->addRule('sdt', '', 'required', null, 'client', false, false);

		$mform->addElement('text', 'email', 'Email');
		$mform->setType('email', PARAM_NOTAGS);
		$mform->addRule('email', '', 'required', null, 'client', false, false);

		$ds_role = array(
			'1' => 'GVCN',
			'2' => 'QLHT'
		);

		$ds_gioi_tinh = array(
			'1' => 'Nam',
			'2' => 'Nữ'
		);

		$options = array(
			'multiple'          => true,
			'noselectionstring' => 'Chưa chọn',
		);

		$select = $mform->addElement('select', 'role', 'Chức vụ', $ds_role);
		$select->setSelected('1');

		$select = $mform->addElement('select', 'gioi_tinh', 'Giới tính', $ds_gioi_tinh);
		$select->setSelected('2');

		$this->add_action_buttons(true, get_string('submit'));
	}

	public function validation($data, $files)
	{
		global $DB;

		if (strlen($data['ho_ten']) > 200) {
			return array('ho_ten' => "Họ tên chỉ tối đa 200 kí tự");
		}
		if (strlen($data['sdt']) > 20) {
			return array('sdt' => "SĐT phải nhỏ hơn 20 kí tự");
		} else {
			if (!is_numeric($data['sdt'])) {
				return array('sdt' => "SĐT phải là một số");
			}
		}

		if (strlen($data['email']) > 100) {
			return array('email' => "Email phải nhỏ hơn 100 kí tự");
		} else {
			$email = $data['email'];
			$role = $data['role'];

			if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
				if (empty($data['id'])) {
			        if ($DB->record_exists_sql("SELECT * FROM {th_export_support_dcct} WHERE email = '$email' AND role = '$role'")) {
						return array('email' => "Người dùng có email ($email) đã tồn tại trên hệ thống");
					}
				}
		    } else {
		        return array('email' => "Email sai định dạng");
		    }	
		}

		return array();
	}
}