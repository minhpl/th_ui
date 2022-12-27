# Mô tả chung
 - Tên plugin: th_vmc_loginreport
 - Kiểu plugin: block
 - Project: VMC
 - Chức năng: Báo cáo Đăng nhập giúp tra cứu thông tin Học viên trong Khóa học (lần truy cập cuối, số lần truy cập khóa học, Tình trạng hoàn thành khóa học, trạng thái ghi danh).
 - Người phát triển: datdt720@wru.vn
# Mô tả chi tiết
## Giao diện chính
![image](https://user-images.githubusercontent.com/66956549/156478782-0fb7b91f-5913-492b-b4f2-e995b8e85c51.png)
- Có 2 Lựa chọn để tìm kiếm thông tin là
    - Theo Khóa học
    - Theo Học viên
### Theo Khóa học
![image](https://user-images.githubusercontent.com/66956549/156478782-0fb7b91f-5913-492b-b4f2-e995b8e85c51.png)
- Đầu vào:
    - Mã khóa học
    - Ghi danh (những Học viên nào được ghi danh trong khoảng thời gian này)
    - Chưa truy cập khóa học (những Học viên chưa vào khóa học trong khoảng thời gian này)
- Nếu Đầu vào GHI DANH và CHƯA TRUY CẬP KHÓA HỌC giống nhau thì plugin sẽ không dùng Đầu vào CHƯA TRUY CẬP KHÓA HỌC để tìm kiếm Học viên mà chỉ dùng 2 Đầu vào còn lại là Mã khóa học và Ghi danh để tìm kiếm Học viên
- Nếu không chọn Mã khóa học thì Plugin hiểu là chọn tất cả khóa học trong hệ thống.
- Đầu ra:
    - Tên tài khoản
    - Họ và tên
    - Thư điện tử
    - Lần truy cập cuối
- Nếu Đầu vào GHI DANH và CHƯA TRUY CẬP KHÓA HỌC giống nhau thì sẽ lấy ra các Học viên được Ghi danh trong khoảng thời gian (Đầu vào Ghi danh).
![image](https://user-images.githubusercontent.com/66956549/156482733-bef7d523-00fb-4736-9efd-d56713469f7c.png)
- Nếu Đầu vào GHI DANH và CHƯA TRUY CẬP KHÓA HỌC khác nhau thì sẽ lấy ra các Học viên chưa truy cập khóa học trong khoảng thời gian (Đầu vào Ghi danh).
![image](https://user-images.githubusercontent.com/66956549/156482677-1202a914-5fee-488d-8a9c-e90815bc6660.png)
### Theo Học viên
![image](https://user-images.githubusercontent.com/66956549/156482853-c7f0c679-3152-4f1a-bd12-6144c43edaee.png)
- Đầu vào:
    - Tài khoản Học viên
- Nếu không chọn tài khoản của Học viên thì Hệ thống sẽ hiểu là chọn tất cả các Học viên trong tất cả các khóa học trên hệ thống.
- Đầu ra:
    - Tên tài khoản
    - Họ và tên
    - Thư điện tử
    - Khóa học
    - Lần truy cập gần nhất
    - Số lần truy cập
    - Tình trạng hoàn thành khóa học
    - Trạng thái ghi danh
![image](https://user-images.githubusercontent.com/66956549/156483432-6b868ab3-de70-42dd-832a-8be56cac82dc.png)