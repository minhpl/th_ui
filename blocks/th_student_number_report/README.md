# **Tài liệu phát triển tính năng báo cáo thống kê số lượng học viên**

1. **Tên plugin**: th_student_number_report
2. **Kiểu plugin**: blocks
3. **Project**: TNU, AOF, TNUT
4. **Chức năng chung**: Thống kê số lượng học viên theo môn học, ngành học, đợt mở môn
5. **Người phát triển**: linhnt720@wru.vn
6. **Người yêu cầu**: Minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-29
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_student_number_report

# 1. Yêu cầu: (bắt buộc)
Thống kê số lượng học viên theo môn học, ngành học, đợt mở môn

![image](https://user-images.githubusercontent.com/13426817/207774164-e59aa15b-678d-47af-b594-d38134a49861.png)


# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt: (bắt buộc)

- Capability có quyền truy cập chức năng:

    ```
    $capabilities = array(
        'block/th_student_number_report:view' => array(
            'riskbitmask' => RISK_SPAM | RISK_XSS,
            'captype' => 'write',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                'manager' => CAP_ALLOW
            ),
	    ),
    );
    ```
### Chức năng thống kê theo môn học

![image](https://user-images.githubusercontent.com/57883256/207771304-0b324ce9-d36a-4c09-a39a-ea7b94fb4763.png)

- Đầu vào:
    - Chọn môn học
    - Chọn khoảng ngày mở môn
    - Chọn số lượng học viên
- Đầu ra:
    - STT
    - Tên môn
    - Ngày mở môn
    - Ngành học
    - Số học viên

### Chức năng thống kê theo ngành học

![image](https://user-images.githubusercontent.com/57883256/207771385-33741ce4-2b43-40b0-9381-091a309f9194.png)

- Đầu vào:
    - Chọn ngành học
    - Chọn khoảng ngày mở môn
    - Chọn số lượng học viên
- Đầu ra:
    - STT
    - Tên môn
    - Ngày mở môn
    - Ngành học
    - Số học viên

### Chức năng thống kê theo đợt mở môn

![image](https://user-images.githubusercontent.com/57883256/207771455-588aef36-1827-4b7d-8b38-8314a0a959c0.png)

- Đầu vào:
    - Chọn khoảng ngày mở môn
    - Chọn số lượng học viên
- Đầu ra:
    - STT
    - Tên môn
    - Ngày mở môn
    - Ngành học
    - Số học viên



# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart 
**Database:**
- Các bảng cần dùng:
    - course
    - course_categories
    - role
    - enrol
    - user_enrolments
    - user
    - context
    - role_assignments
    
- Câu lệnh đếm số lượng học viên trong khóa học
    ```sql
    $roleid = $DB->get_field_sql("SELECT id FROM {role} WHERE shortname = 'student'");
        $count = $DB->get_field_sql("SELECT COUNT(ue.userid) FROM {enrol} as e, 
        {user_enrolments} as ue, {user} as u, {context} as c, {role_assignments} as ra 
        WHERE ue.status = 0 AND e.courseid = '$courseid' AND e.enrol = 'manual' 
        AND e.id = ue.enrolid AND u.id = ue.userid AND c.instanceid = '$courseid' 
        AND c.contextlevel = '50' AND c.id = ra.contextid AND ra.userid = u.id 
        AND ra.roleid = '$roleid'");
    ```

# 4. mã nguồn: hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này (nếu cần)

# 5. Triển khai: (Hướng dẫn triển khai, lưu ý khi upload nên appstore. nếu cần)

# 6. Kiểm thử: (nếu cần)
