# Tài liệu Báo cáo các Tài khoản được tạo theo khoảng thời gian (ngày, tuần, tháng)

1. **Tên plugin:** th_accountreport
2. **Kiểu plugin**: block
3. **Project:** VMC
4. **Chức năng chung:** Báo cáo các Tài khoản được tạo theo thời gian
5. **Người phát triển:** datdt720@wru.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-94
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_accountreport
# 1. Yêu cầu:

- Lấy các Tài khoản được tạo theo khoảng thời gian (ngày, tuần, tháng)

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
1. Các capability cần để truy cập, sử dụng tính năng và đoạn code định nghĩa capabilities
- Các capability: **block/th_accountreport:view**
- Đoạn code định nghĩa capabilities:
```php
'block/th_accountreport:view' => array(
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
    - **Ngày bắt đầu**
    - **Ngày kết thúc**
    - **Các lựa chọn (ngày, tuần, tháng)**
- Đầu ra:
    - **Tên tài khoản**
        - **Họ và tên**
        - **Thư điện tử**
        - **Ngày tạo tài khoản**
        - **Số tài khoản được tạo**
- Ví dụ
    - Đầu vào:
        - Ngày bắt đầu: **16/06/2022**
        - Ngày kết thúc: **16/12/2022**
        - Lựa chọn là: theo **Ngày**, **Tuần**, **Tháng**

        ![form](https://user-images.githubusercontent.com/66956549/208022081-fee759fe-0dc7-4d5c-a8c8-39a2b9d0de45.png)
    - Đầu ra:
        - th_accountreport sẽ tìm các **Tài khoản** trong 4 khoảng thời gian. Với **Đâù vào** như trên 4 khoảng thời gian sẽ là:
            - 16/12/2022 (ngày kết thúc)
            - 15/12/2022 (lùi 1 ngày so với ngày kết thúc)
            - 14/12/2022 (lùi 2 ngày so với ngày kết thúc)
            - 16/06/2021 - 13/12/2022 (khoảng thời gian còn lại)
        - 4 khoảng thời gian này sẽ được hiển thị trong cột **Thời gian**.
        ![Ngay](https://user-images.githubusercontent.com/66956549/208021542-29d282c5-0c45-4de4-ab89-45662b6594e5.png)
    
    - Với lựa chọn **Tuần** và **Tháng** cũng tương tự.
        - 4 khoảng thời gian sẽ là
            - Tuần hiện tại/Tháng hiện tại
            - Lùi 1 tuần/Lùi 1 tháng
            - Lùi 2 tuần/Lùi 2 tháng
            - Khoảng thời gian còn lại
        - **Tuần**
            ![Tuan](https://user-images.githubusercontent.com/66956549/208021656-5331524d-dcb5-4102-9a62-2a1db2bc6457.png)
        - **Tháng**
            ![Thang](https://user-images.githubusercontent.com/66956549/208021902-82b1c623-fb04-40b3-8862-ee6561e21c7a.png)
- Có 1 số ô có giá trị là **N/A**, nghĩa là không có giá trị.

# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: Bảng user(timecreated)
- Các câu truy vấn đã dùng:

```sql
$sql = "SELECT id,timecreated FROM {user}
		WHERE deleted=0 AND suspended=0 AND timecreated >= $to AND timecreated < $from order by id DESC";
```
Để lấy các tài khoản trong trong khoảng thời gian được chọn (nhận từ form)

2. Thêm Bảng: Không
3. Database Diagram: Không

## Method
1. Các Method: getUser
2. Chi tiết các Method:
- getUser: Lấy các Tài khoản được tạo trong khoản thời gian
```php  /**
 * [getUser Lấy các Tài khoản được tạo trong khoản thời gian] 
 *
 * @param  [int] $to	Ngày bắt đầu (từ 0 giờ 0 phút 0 giây Ngày bắt đầu)
 * @param  [int] $from	Ngày kết thúc (đến 23 giờ 59 phút 59 giây Ngày kết thúc)
 * @return [array]		Các Tài khoản được tạo trong khoảng thời gian
 */
function getUser($to, $from)
```
# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/blocks/th_accountreport

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)

