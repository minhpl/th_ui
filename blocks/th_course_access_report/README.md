# Tài liệu Báo cáo truy cập khóa học

1. **Tên plugin:** th_course_access_report
2. **Kiểu plugin:** block
3. **Project:** VMC
4. **Chức năng chung:** Lấy Học viên truy cập khóa học theo thời gian
5. **Người phát triển:** datdt720@wru.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-101
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_course_access_report
# 1. Yêu cầu:
- Lấy Học viên truy cập khóa học theo thời gian
- Đầu vào ví dụ:

![image](https://user-images.githubusercontent.com/13426817/208380952-b14de269-527f-43f1-ab2d-3b3a92904fd5.png)
- Đầu ra ví dụ:

![image](https://user-images.githubusercontent.com/13426817/208380732-7fcf4229-71c3-46f5-8064-250fd6c2b6b5.png)


# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
1. Các capability cần để truy cập và sử dụng tính năng. Đoạn code định nghĩa capabilities
- Các capability cần để truy cập và sử dụng tính năng:
  **block/th_course_access_report:view**
- Đoạn code định nghĩa capabilities:
```php
'block/th_course_access_report:view' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'write',
		'contextlevel' => CONTEXT_BLOCK,
		'archetypes' => array(
			'teacher' => CAP_ALLOW,
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),
	),
```
2. Hướng dẫn sử dụng
- Đầu vào: 
    - Ngày bắt đầu
    - Ngày kết thúc
    - Khóa học 
    - Học viên
    - Trạng thái ghi danh
    ![form](https://user-images.githubusercontent.com/66956549/208071291-1a5fc0d4-bcd5-46d5-aa7a-1ee40ee8c2e8.png)
    - Nếu không chọn **Khóa học** thì sẽ lấy tất cả các khóa học trong Hệ thống đủ điều kiện.
    - Nếu không chọn **Học viên** thì sẽ lấy tất cả các Học viên trong Hệ thống đủ điều kiện.
- Đầu ra: thông tin học viên gồm:
    - Tên tài khoản
    - Họ và tên
    - Thư điện tử
    - Ngày kích hoạt: là ngày ghi danh vào khóa học
    - Ngày hết hạn là ngày mà học viên không vào được khóa học nữa.
    - Đã đăng ký là ngày mà học viên được đăng ký vào khóa học (Học viên sẽ tự ghi danh vào khóa học).
    - Số lần truy cập khóa học
    - Quyền là quyền của Học viên trong khóa học.
    - Trạng thái hoạt động có thể là Đình chỉ hoặc Đang hoạt động.
    ![table](https://user-images.githubusercontent.com/66956549/208072012-869936bb-95c4-41b8-afdd-97a790cebca3.png)
    - Có 1 số ô có giá trị là **N/A**, nghĩa là không có giá trị.

# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: Bảng user, user_enrolments, course, enrol, role_assignments, context, role
- Các câu truy vấn đã dùng:
```sql
$sql = "SELECT COUNT(ls.id) as accesscourse
		FROM {logstore_standard_log} ls
		WHERE contextlevel=50 AND target='course' AND courseid = $courseid
		AND userid = $userid AND timecreated > $from_date AND timecreated <= $to_date"
```
Lấy số lần truy cập vào khóa học

```sql
$sql = "SELECT DISTINCT m.*, rc.timecreated AS registered 
        FROM
		(SELECT DISTINCT u.id,c.id AS courseid,c.fullname,c.shortname,ue.timecreated,ue.timeend,ue.status
        FROM {user} u
        JOIN {user_enrolments} ue ON ue.userid = u.id
        JOIN {enrol} e ON e.id = ue.enrolid
        JOIN {role_assignments} ra ON ra.userid = u.id
        JOIN {context} ct ON ct.id = ra.contextid AND ct.contextlevel = 50
        JOIN {course} c ON c.id = ct.instanceid AND e.courseid = c.id
        JOIN {role} r ON r.id = ra.roleid
        WHERE c.visible=1 AND e.status = 0 AND u.suspended = 0 AND u.deleted = 0 AND c.id = :courseid AND ue.status=0 AND (r.id=5 OR r.id=3)
            AND (ue.timeend>:to_date OR ue.timeend=0)) m
        LEFT JOIN
            {th_registeredcourses} rc ON m.id=rc.userid AND m.courseid=rc.courseid
        GROUP BY m.id
        HAVING MAX(m.timecreated)"
```
Lấy các học viên đang hoạt động trong khóa học theo thời gian.

```sql
 $sql = "SELECT DISTINCT m.*, rc.timecreated AS registered 
        FROM
		(SELECT DISTINCT u.id,c.id AS courseid,c.fullname,c.shortname,ue.timecreated,ue.timeend,ue.status
        FROM {user} u
        JOIN {user_enrolments} ue ON ue.userid = u.id
        JOIN {enrol} e ON e.id = ue.enrolid
        JOIN {role_assignments} ra ON ra.userid = u.id
        JOIN {context} ct ON ct.id = ra.contextid AND ct.contextlevel = 50
        JOIN {course} c ON c.id = ct.instanceid AND e.courseid = c.id
        JOIN {role} r ON r.id = ra.roleid
        WHERE (c.visible=1 AND e.status = 0 AND u.suspended = 0 AND u.deleted = 0 AND c.id = :courseid)
            AND (ue.timeend>:from_date AND ue.timeend<=:to_date OR ue.status=1)) m
        LEFT JOIN
            {th_registeredcourses} rc ON m.id=rc.userid AND m.courseid=rc.courseid
        GROUP BY m.id
        HAVING MAX(m.timecreated)"
```
Lấy các học viên bị hết hạn hoặc bị đình chỉ trong khóa học theo thời gian.

1. Thêm Bảng: Không
2. Database Diagram: Không

## Method
1. Các Method cần chú ý: get_access_course
2. Chi tiết các Method:
- get_access_course: Lấy số lần truy cập vào khóa học của học viên theo thời gian
```php  
/**
 * [get_access_course] Lấy số lần truy cập vào khóa học của học viên
 *
 * @param [int] $userid     id của tài khoản
 * @param [int] $courseid   id của khóa học
 * @param [int] $from_date  Ngày bắt đầu (0 giờ 0 phút 0 giây Ngày bắt đầu)
 * @param [int] $to_date    Ngày kết thúc (23 giờ 59 phút 59 giây Ngày kết thúc)
 * @return int              Số lần truy cập khóa học của học viên
 */
public function get_access_course($userid, $courseid, $from_date, $to_date)
```
# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/blocks/th_course_access_report

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)

