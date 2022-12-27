# **Tài liệu phát triển tính năng xuất hỗ trợ học tập và giảng viên chuyên môn dcct theo mã lớp**

1. **Tên plugin**: th_export_support_dcct
2. **Kiểu plugin**: block
3. **Project**: TNU, AOF, TNUT
4. **Chức năng chung**: Xuất hỗ trợ học tập và giảng viên chuyên môn dcct theo mã lớp
5. **Người phát triển**: linhnt720@wru.vn
6. **Người yêu cầu**: Minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-17
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_export_support_dcct

# 1. Yêu cầu: (bắt buộc)
Xuất hỗ trợ học tập và giảng viên chuyên môn dcct theo mã lớp.

Thông tin giáo viên chủ nhiệm, quản lý học tập của các lớp được lưu trong csdl. 

Người dùng up file excel chứa thông tin đợt mở môn bao gồm: tên môn, danh sách mã lớp, họ và tên giảng viên, học vị, sđt, email. Hệ thống xuất ra file world chứa thông tin giảng viên, qlht, gvcm của môn học để sử dụng trong file đề cương chi tiết

![image](https://user-images.githubusercontent.com/13426817/208017941-4931b0ad-e976-4c54-ac48-41ec2f175da0.png)


# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt: (bắt buộc)

- Capability có quyền truy cập chức năng:

    ```
    $capabilities = array(
        'block/th_export_support_dcct:view' => array(
            'riskbitmask' => RISK_SPAM | RISK_XSS,
            'captype' => 'write',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                'manager' => CAP_ALLOW
            ),
	    )
    );
    ```
- Chức năng xuất hỗ trợ học tập, giảng viên chuyên môn:
    - Giao diện chính chức năng:
    ![image](https://user-images.githubusercontent.com/57883256/207773976-21a9f2db-12ab-4400-96e3-f5014cd64fb0.png)
        - Tệp tin csv mẫu:
        ![image](https://user-images.githubusercontent.com/57883256/207773644-c8e31e4c-a437-4701-9a53-6c924eac1e5f.png)
    - Thêm file csv và chọn gửi hệ thống sẽ trả về giao diện xác nhận như hình dưới:
    ![image](https://user-images.githubusercontent.com/57883256/208005862-95d782c0-466a-4e77-b3a1-083ad2a4f636.png)    
    - Sau khi kiểm tra chính xác các thông tin người dùng chọn xuất file word để xuất ra file word chứa danh sách GVCM,GVCN,QLHT như hình dưới:

        ![image](https://user-images.githubusercontent.com/57883256/207774115-4e8d041c-d9f9-45dc-87f7-59a2d760308e.png)
- Chức năng thêm hỗ trợ học tập
    - Giao diện chính chức năng:

    ![image](https://user-images.githubusercontent.com/57883256/208002255-f5abe25a-a12b-455d-9e0d-ab1c8dd0caab.png)
    - Sau khi điền đầy đủ thông tin người dùng chọn gửi hệ thống kiểm tra và trả về thông báo thêm thành công !
- Chức năng sửa hỗ trợ học tập:
    ![image](https://user-images.githubusercontent.com/57883256/208002900-8ef9ee99-40c5-484b-be89-2821adfd60e9.png)
- Chức năng xóa hỗ trợ học tập:
    ![image](https://user-images.githubusercontent.com/57883256/208003453-07bc739b-95b5-44ae-8ded-64c9ecbb5da5.png)
- Chức năng thêm hỗ trợ học tập hàng loạt:
    - Giao diện chính chức năng:
    ![image](https://user-images.githubusercontent.com/57883256/208003759-9571fd1b-1286-4ec4-b5d0-02195c0c27d9.png)

    - File csv mẫu:

        ![image](https://user-images.githubusercontent.com/57883256/208006085-17c93670-3226-425c-b5be-258d133079a1.png)
    - Thêm file csv và chọn gửi hệ thống sẽ trả về giao diện xác nhận như hình dưới:
    ![image](https://user-images.githubusercontent.com/57883256/208004133-d5b780c9-31ff-4847-8d85-0d2ef19e8d08.png)
    - Sau khi kiểm tra thông tin người dùng cần thêm đã chính xác chọn thêm để hoàn thành

# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart 
**Database:**

- Bảng mới:bảng th_export_support_dcct

| Tên        | Kiểu      | Chú thích     |
| ---------- | --------- | ------------- |
| id         | int(10)   |               |
| ma\_lop    | text      | Mã lớp        |
| ho\_ten    | char(200) | Họ tên        |
| sdt        | int(20)   | Số điện thoại |
| email      | char(100) | Email         |
| role       | int(10)   | Quyền         |
| gioi\_tinh | int(10)   | Giới tính     |

# 4. mã nguồn: hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này (nếu cần)

# 5. Triển khai: (Hướng dẫn triển khai, lưu ý khi upload nên appstore. nếu cần)

# 6. Kiểm thử: (nếu cần)
