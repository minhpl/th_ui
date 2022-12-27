# Tài liệu Báo cáo học viên ghi danh

1. **Tên plugin:** th_enrollmentreport
2. **Kiểu plugin:** block
3. **Project:** VMC
4. **Chức năng chung:** Báo cáo học viên ghi danh dùng để lấy các Học viên được gán vào Khóa học theo thời gian (Ngày, Tuần, Tháng)
5. **Người phát triển:** datdt720@wru.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-121
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_enrollmentreport
   
# 1. Yêu cầu:
- Lấy các Học viên được gán vào Khóa học theo thời gian

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
1. Các capability cần để truy cập và sử dụng tính năng. Đoạn code định nghĩa capabilities
- Các capability cần để truy cập và sử dụng tính năng:
  **block/th_enrollmentreport:view**
- Đoạn code định nghĩa capabilities:
```php
'block/th_enrollmentreport:view' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'write',
		'contextlevel' => CONTEXT_BLOCK,
		'archetypes' => array(
			'teacher' => CAP_ALLOW,
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),
	)
```
2. Hướng dẫn sử dụng
- Đầu vào:
    - Khóa học
    - Ngày bắt đầu 
    - Ngày Kết thúc
    - Lựa chọn theo **Ngày**, **Tuần**, **Tháng**.
- Chú ý: Nếu không chọn **Khóa học** nào thì có nghĩa là chọn tất cả các khóa học trong hệ thống.  
- Đầu ra: thông tin Học viên
    - Tên tài khoản
    - Họ và tên
    - Thư điện tử
    - Ngày ghi danh vào Khóa học
    - Ngày kết thúc ghi danh
    - Thời gian là các khoảng thời sẽ được tìm kiếm.
    - Tổng số là số Học viên được ghi danh trong khoảng thời gian.
- Ví dụ:
    - Đầu vào:
        - Chọn Khóa học **An toàn và bảo mật thông tin**
        - Ngày bắt đầu: **16/06/2021**
        - Ngày kết thúc: **16/12/2022**
        - Lựa chọn là: theo **Ngày**
        ![form](https://user-images.githubusercontent.com/66956549/208045227-c83f7141-60c8-47d3-9848-dd01446a66db.png)
    - Đầu ra:
        - th_enrollmentreport sẽ tìm các **Học viên** trong 4 khoảng thời gian. Với **Đầu vào** như trên 4 khoảng thời gian sẽ là:
            - 16/12/2022 (ngày kết thúc)
            - 15/12/2022 (lùi 1 ngày so với ngày kết thúc)
            - 14/12/2022 (lùi 2 ngày so với ngày kết thúc)
            - 16/06/2021 - 13/12/2022 (khoảng thời gian còn lại)
        - 4 khoảng thời gian này sẽ được hiển thị trong cột **Thời gian**.
            ![ngay](https://user-images.githubusercontent.com/66956549/208045808-c066411c-d93e-456b-8971-18c956f182be.png)
        - Với lựa chọn **Tuần** và **Tháng** cũng tương tự.
        - 4 khoảng thời gian sẽ là
            - Tuần hiện tại/Tháng hiện tại
            - Lùi 1 tuần/Lùi 1 tháng
            - Lùi 2 tuần/Lùi 2 tháng
            - Khoảng thời gian còn lại
        - **Tuần**
            ![tuan](https://user-images.githubusercontent.com/66956549/208046055-b2bf4784-5bbc-402f-b5b7-08b0c295a95b.png)
        - **Tháng**
            ![thang](https://user-images.githubusercontent.com/66956549/208046361-c5ec2f0f-65c9-4500-a6dd-35bb40ff1885.png)
- Có 1 số ô có giá trị là **N/A**, nghĩa là không có giá trị.

# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: Bảng user, user_enrolments, course, enrol, role_assignments
- Các câu truy vấn đã dùng:
```sql
$sql = "SELECT DISTINCT u.id, ue.timestart, ue.timeend
		FROM {user_enrolments} ue, {course} c, {user} u, {enrol} e, {role_assignments} ra
		WHERE c.id=e.courseid AND u.id=ue.userid AND e.id=ue.enrolid AND u.id=ra.userid AND ra.roleid=5 AND u.deleted=0 
        AND c.id = $courseid AND ue.timecreated >= $startday AND ue.timecreated < $endday AND e.status = 0 AND u.suspended = 0";
```
Lấy các Học viên được gán vào Khóa học theo khoảng thời gian

2. Thêm Bảng: Không
3. Database Diagram: Không

## Method
1. Các Method: laytaikhoan
2. Chi tiết các Method:
- laytaikhoan: Lấy các Học viên được gán vào Khóa học theo khoảng thời gian
```php  
/**
 * [laytaikhoan] Lấy các Học viên được ghi danh vào Khóa học theo thời gian
 *
 * @param [int] $startday	Ngày bắt đầu (từ 0 giờ 0 phút 0 giây Ngày bắt đầu)
 * @param [int] $endday		Ngày kết thúc (đến 23 giờ 59 phút 59 giây Ngày kết thúc)
 * @param [int] $courseid	ID của khóa hoc
 * @return [array]          Các Học viên được ghi danh vào khóa học trong khoảng thời gian
 */
function laytaikhoan($startday, $endday, $courseid)
```
# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/blocks/th_enrollmentreport

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)

