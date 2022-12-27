# **Tài liệu phát triển tính năng ghi danh học viên vào nhóm gia hạn hàng loạt**

1. **Tên plugin**: th_bulk_enrol_groups
2. **Kiểu plugin**: block
3. **Project**: TNU, AOF, TNUT
4. **Chức năng chung**: Ghi danh học viên vào nhóm gia hạn hàng loạt
5. **Người phát triển**: linhnt720@wru.vn
6. **Người yêu cầu**: Minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-96
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_bulk_enrol_groups

# 1. Yêu cầu: (bắt buộc)
Ghi danh học viên vào nhóm gia hạn hàng loạt bằng file CSV.
Thông tin cột bao gồm mã môn, tên_nhóm cần gán, email. Ví dụ: 

| ma_mon | ten_nhom | email |
|--------------|-------|------|
| FINT212-211017 | Nhóm gia hạn bài kiểm tra - 17h30p00s-19/3/2022 -> 23h59p00-20/3/2022 - 3 lần | linh@gmail.com |
| FINT212-211017 | Nhóm gia hạn bài kiểm tra - 17h30p00s-19/3/2022 -> 23h59p00-20/3/2022 - 3 lần  | linh2@gmail.com |
| FINT212-211017 | Nhóm gia hạn bài luyện tập - 17h30p00s-19/3/2022 -> 23h59p00-20/3/2022 - 3 lần  | linh3@gmail.com |
| FINT212-211017 | Nhóm gia hạn bài luyện tập - 17h30p00s-19/3/2022 -> 23h59p00-20/3/2022 - 3 lần  | linh4@gmail.com |

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt: (bắt buộc)

- Capability có quyền truy cập chức năng:

    ```
    $capabilities = array(
        'block/th_bulk_enrol_groups:view' => array(
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


- Giao diện chính chức năng:
    ![image](https://user-images.githubusercontent.com/57883256/208007480-244d1ed7-18ac-451f-b7a6-6c8869a4b79c.png)
- Tệp csv mẫu:
    | ma_mon | ten_nhom | email |
    |--------------|-------|------|
    | FINT212-211017 | Nhóm gia hạn bài kiểm tra - 17h30p00s-19/3/2022 -> 23h59p00-20/3/2022 - 3 lần | linh@gmail.com |
    | FINT212-211017 | Nhóm gia hạn bài kiểm tra - 17h30p00s-19/3/2022 -> 23h59p00-20/3/2022 - 3 lần  | linh2@gmail.com |
    | FINT212-211017 | Nhóm gia hạn bài luyện tập - 17h30p00s-19/3/2022 -> 23h59p00-20/3/2022 - 3 lần  | linh3@gmail.com |
    | FINT212-211017 | Nhóm gia hạn bài luyện tập - 17h30p00s-19/3/2022 -> 23h59p00-20/3/2022 - 3 lần  | linh4@gmail.com |

- Người dùng thêm file csv và chọn gửi hệ thống sẽ trả về giao diện xác nhận như hình dưới:
    ![image](https://user-images.githubusercontent.com/57883256/208008351-8ed42556-cf47-44cb-928b-6b97842df3f8.png)
    - Gợi ý (Nếu tìm thấy giá trị lỗi)
    - Các người dùng sẽ được gán vào nhóm gia hạn
        - STT
        - Tên khóa học
        - Nhóm gia hạn
        - Tên học viên
        - Trạng thái
- Sau khi kiểm tra chính xác các thông tin người dùng chọn gán học viên để gán học viên vào nhóm gia hạn. Hệ thống sẽ trả về thông báo thành công.

# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart 
**Database:**
- Các bảng cần dùng:
    - course
    - user
    - groups
    - groups_members
    
```sql
$course = $DB->get_record_sql("SELECT * FROM {course} WHERE shortname = '$shortname'");
$user = $DB->get_record_sql("SELECT * FROM {user} WHERE email = '$email'");
$group = $DB->get_record_sql("SELECT * FROM {groups} WHERE name = '$group_name' AND courseid = '$course_id'");
$user_enrol = $DB->get_records_sql("SELECT * FROM {groups_members} WHERE userid = '$user_id' AND groupid = '$group_id'");
```

# 4. mã nguồn: hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này (nếu cần)

# 5. Triển khai: (Hướng dẫn triển khai, lưu ý khi upload nên appstore. nếu cần)

# 6. Kiểm thử: (nếu cần)
