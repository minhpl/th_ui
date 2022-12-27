# **Tài liệu phát triển tính năng thống kê danh sách giảng viên phụ trách các môn học**

1. **Tên plugin**: th_teacher_list_report
2. **Kiểu plugin**: block
3. **Project**: TNU, AOF, TNUT
4. **Chức năng chung**: Thống kê danh sách giảng viên phụ trách các môn học theo ngày mở môn, môn học, giảng viên
5. **Người phát triển**: linhnt720@wru.vn
6. **Người yêu cầu**: Minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-116
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_teacher_list_report

# 1. Yêu cầu: (bắt buộc)
- Thống kê danh sách giảng viên phụ trách các môn học theo ngày mở môn, môn học, giảng viên
- Mẫu output:
![image](https://user-images.githubusercontent.com/13426817/209254504-c175c828-2a2b-409d-9949-59c3fe5b0081.png)

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt: (bắt buộc)

- Capability có quyền truy cập và sử chức năng: block/th_teacher_list_report:view

    ```php
    $capabilities = array(
        'block/th_teacher_list_report:view' => array(
            'riskbitmask' => RISK_SPAM | RISK_XSS,
            'captype' => 'write',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                'manager' => CAP_ALLOW
            ),
        ),
    );
    ```

- Thống kê giảng viên phụ trách môn học theo ngày mở môn:
    - Giao diện chính chức năng:
    ![image](https://user-images.githubusercontent.com/57883256/209046266-51621eee-0548-4568-9a3b-017e46796527.png)

    - Chọn ngày mở môn sau đó nhấn submit hệ thống sẽ trả về danh sách như hình dưới
    ![image](https://user-images.githubusercontent.com/57883256/209046483-e199ddcf-b19b-4ea0-9dda-b98b6ed4aac2.png)

- Thống kê giảng viên phụ trách môn học theo môn học:
    - Giao diện chính chức năng:
    ![image](https://user-images.githubusercontent.com/57883256/209046819-7621f917-c2b3-4670-9773-9b7cd632c70a.png)

    - Chọn các môn học và chọn khoảng thời gian mở môn sau đó nhấn nút submit hệ thống sẽ trả về danh sách như hình dưới
    ![image](https://user-images.githubusercontent.com/57883256/209047178-16e8a912-a45d-4dc4-8ba6-f43cdc2eaca4.png)

    - Lưu ý: Nếu để trống không chọn môn học nào thì sẽ mặc định chọn tất cả khóa học.

- Thống kê giảng viên phụ trách khóa học theo giảng viên:
    - Giao diện chính chức năng:
    ![image](https://user-images.githubusercontent.com/57883256/209047516-efb30d36-725b-4036-ab7d-505b4b165998.png)

    - Chọn giảng viên và chọn khoảng ngày mở môn sau đó nhấn submit hệ thống sẽ trả về danh sách như hình dưới:
    ![image](https://user-images.githubusercontent.com/57883256/209047724-8110a2aa-3397-4c47-adef-9fb23c3010af.png)

# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart 
**Database:**
- Các bảng cần dùng:
    - user
    - course
    - course_categories
    - enrol
    - user_enrolments
    - context
    - role_assignments

- Lấy danh sách khóa học    
    ```sql
    SELECT c.* FROM {course} as c, {course_categories} as ca WHERE NOT c.id = 1 AND NOT c.category = 1 AND c.visible <> 0 AND c.fullname NOT LIKE '% - mẫu' AND ca.id = c.category AND ca.visible = 1
   ```
- Lấy danh sách giảng viên:

    ```sql
    SELECT u.* FROM {enrol} as e, {user_enrolments} as ue, {user} as u, {context} as c, {role_assignments} as ra WHERE ue.status = 0 AND e.courseid = '$course_id' AND e.enrol = 'manual' AND e.id = ue.enrolid AND u.id = ue.userid AND c.instanceid = '$course_id' AND c.contextlevel = '50' AND c.id = ra.contextid AND ra.userid = u.id AND ra.roleid = '$roleid'
    ```

# 4. mã nguồn: hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này (nếu cần)

# 5. Triển khai: (Hướng dẫn triển khai, lưu ý khi upload nên appstore. nếu cần)

# 6. Kiểm thử: (nếu cần)
