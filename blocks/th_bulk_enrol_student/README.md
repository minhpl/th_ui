
# **Tài liệu phát triển tính năng gán học viên theo lô bằng file excel có nhiều sheet hoặc file csv**

1. **Tên plugin**: th_bulk_enrol_student
2. **Kiểu plugin**: block
3. **Project**: TNU, AOF, TNUT
4. **Chức năng chung**: Gán học viên theo lô bằng file excel có nhiều sheet hoặc file csv
5. **Người phát triển**: linhnt720@wru.vn
6. **Người yêu cầu**: Minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-97
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_bulk_enrol_student

# 1. Yêu cầu: (bắt buộc)
Gán học viên theo lô bằng file excel hoặc file csv. Nếu gán học viên bằng file excel, thông tin học viên trong mỗi môn học sẽ đặt trong một sheet

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt: (bắt buộc)

- Capability có quyền truy cập chức năng:

    ```
    $capabilities = array(
        'block/th_bulk_enrol_student:view' => array(
            'riskbitmask' => RISK_SPAM | RISK_XSS,
            'captype' => 'write',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                'teacher' => CAP_ALLOW,
                'editingteacher' => CAP_ALLOW,
                'manager' => CAP_ALLOW,
            ),
        ),
    );
    ```

- Chức năng gán học viên bằng file excel
    - Giao diện chính chức năng:
        ![image](https://user-images.githubusercontent.com/57883256/208010714-bb143693-a30f-4889-92c1-0f734c73d283.png)
    - Tệp tin excel mẫu:

        ![image](https://user-images.githubusercontent.com/57883256/208010987-3f149df6-e3e1-4808-8fcb-425cfa63eb9f.png)
    - Thêm file excel và chọn upload hệ thống sẽ trả về giao diện xác nhận như hình dưới:
        ![image](https://user-images.githubusercontent.com/57883256/208011563-80682680-c546-4eaf-975e-c00fecb8c421.png)
        - Gợi ý (Nếu tìm thấy giá trị lỗi)
        - Các người dùng sẽ được gán vào khóa học
            - STT
            - Địa chỉ thư điện tử
            - Họ tên
            - Tên khóa học
            - Tên rút gọn khóa học
            - Trạng thái
            - Ghi chú
    - Sau khi kiểm tra đầy đủ các thông tin người dùng chọn ghi danh để gán học viên.
- Chức năng gán học viên bằng file csv
    - Giao diện chính chức năng:
        ![image](https://user-images.githubusercontent.com/57883256/208011816-ca4d3fb4-eb17-4095-972c-411318e77d2d.png)
    - Tệp tin csv mẫu:
        
        ![image](https://user-images.githubusercontent.com/57883256/208012017-694e5c8f-aabc-41ad-a07a-8d4a72743054.png)
    - Người dùng thêm file csv và chọn gửi hệ thống sẽ trả về giao diện xác nhận như hình dưới:
        ![image](https://user-images.githubusercontent.com/57883256/208012364-b6094c8d-9829-4378-b9c1-3f677b5bbaa5.png)
        - Gợi ý (Nếu tìm thấy giá trị lỗi)
        - Các người dùng sẽ được gán vào khóa học
            - STT
            - Địa chỉ thư điện tử
            - Họ tên
            - Tên khóa học
            - Tên rút gọn khóa học
            - Trạng thái
            - Ghi chú
    - Sau khi kiểm tra chính xác các thông tin người dùng chọn gán học viên để hệ thống thực hiện gán học viên vào khóa học

# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart 
**Database:**
- Các bảng cần dùng:
    - user
    - course
    - role
    - user_enrolments
    - role_assignments
    - context
    
```sql
$sql1 = "SELECT * FROM {user_enrolments} WHERE userid = $userid AND enrolid = $instance->id";
$sql2 = "SELECT id FROM {context} WHERE instanceid = $courseid AND contextlevel = 50";
$sql3 = "SELECT * FROM {role_assignments} WHERE $userid AND roleid = $roleid AND contextid = $contextid->id";
```

# 4. mã nguồn: hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này (nếu cần)

# 5. Triển khai: (Hướng dẫn triển khai, lưu ý khi upload nên appstore. nếu cần)

# 6. Kiểm thử: (nếu cần)
