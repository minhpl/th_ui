# Tài liệu Quản lý các plugin

1. **Tên plugin:** th_dashboard
2. **Kiểu plugin**: local
3. **Project:** VMC,TNU,AOF,TNUT
4. **Chức năng chung:** Để quản lý các plugin theo quyền
5. **Người phát triển:** huyen.phamlt19@gmail.com,datdt720@wru.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-128
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/local/th_dashboard
# 1. Yêu cầu:

- Để quản lý các plugin theo quyền

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
1. Các capability cần để truy cập, sử dụng tính năng và đoạn code định nghĩa capabilities
- Các capability: **local/th_dashboard:viewthdashboard**
- Đoạn code định nghĩa capabilities:
```php
'local/th_dashboard:viewthdashboard' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
			'manager' => CAP_ALLOW,
		),

		'clonepermissionsfrom' => 'moodle/my:manageblocks',
	)
```
2. Hướng dẫn sử dụng
- Đường dẫn cấu hình th_dashboard:
    `Dashboard >Site administration >Appearance >Custom Navigation`
    - Tích vào các checkbox và lưu để sử dụng th_dashboard:
    ![image](https://user-images.githubusercontent.com/66956549/209606449-daa3c49f-a2c8-4dee-89e7-8c0d359b9a61.png)
    - Chỗ cấu hình th_dashboard:
    ![image](https://user-images.githubusercontent.com/66956549/209609492-f71dd55c-e953-4345-bf6d-d4c9a4df27be.png)
    - Nếu viết như trên ta sẽ có cây **TH Dashboard** như sau:
    ![image](https://user-images.githubusercontent.com/66956549/156348673-dbee3323-a2f2-4a0d-9e17-123034b2bf1c.png)
    - Cấu trúc từng dòng là:
    ` Tên (sẽ được hiển thị) | url sẽ được chuyển đến khi kích vào Tên | id của Quyền (có quyền này mới hiển thị dòng trong cây th_dashboard, có thể có 1 hoặc nhiều quyền ở đây ngăn cách bởi dấu phẩy ‘,’)`
    - Các id của Quyền xem ở đây:
    ![image](https://user-images.githubusercontent.com/66956549/209609586-a9b43f9c-bfcb-4e10-8263-1db292a9e23a.png)

    để điền vào cấu trúc dòng.

# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: không
- Các câu truy vấn đã dùng:

```sql
$sql = "SELECT COUNT(ra.id)
        FROM {role_assignments} ra
        WHERE ra.userid = :userid AND ra.roleid = :roleid AND ra.contextid = 1";
```
kiểm tra Tài khoản có quyền trong ngữ cảnh hệ thống không

2. Thêm Bảng: Không
3. Database Diagram: Không

## Method
1. Các Method: user_role_assignment, update_role_node, local_th_dashboard_extend_navigation, navigation_custom_menu_item
2. Chi tiết các Method:
- user_role_assignment: kiểm tra Tài khoản có quyền trong ngữ cảnh hệ thống không
```php  
/**
 * user_role_assignment function kiểm tra Tài khoản có quyền trong ngữ cảnh hệ thống không
 *
 * @param [int] $userid         id người dùng
 * @param [int] $roleid         id quyền
 * @param [int] $contextid      id ngữ cảnh
 * @return [bool]               true có, false không
 */
function user_role_assignment($userid, $roleid, $contextid = 0)
```
- update_role_node: vẽ lại cây th_dashboard
```php
/**
 * update_role_node function vẽ lại cây th_dashboard
 *
 * @param custom_menu_item $menu
 * @return void
 */
function update_role_node($menu)
```

- local_th_dashboard_extend_navigation:
```php
/**
 * local_th_dashboard_extend_navigation function 
 *
 * @param global_navigation $navigation
 * @return void
 */
function local_th_dashboard_extend_navigation(global_navigation $navigation)
```

- navigation_custom_menu_item:
```php
/**
 * ADD custom menu in navigation recursive childs node
 * Is like render custom menu items
 *
 * @param custom_menu_item $menunode {@link custom_menu_item}
 * @param int $parent is have a parent and it's parent itself
 * @param object $pmasternode parent node
 * @param int $flatenabled show master node in boost navigation
 * @return void
 */
function navigation_custom_menu_item(custom_menu_item $menunode, $parent, $pmasternode, $flatenabled, &$count, $roleid = 0)
```
# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/local/th_dashboard

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)

