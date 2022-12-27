# Mô tả chung
 - Tên plugin: th_notification_forum
 - Kiểu plugin: local
 - Project: VMC
 - Chức năng: Để gửi thông báo cho người dùng thêm 1 lần nữa (lần 1 hệ thống có sẵn rồi, lần 2 chỉ với Diễn đàn có courseid = 1).
 - Người phát triển: datdt720@wru.vn
# Mô tả chi tiết
- Mỗi khi có bài viết trên diễn đàn (forum) thì 1 thông báo sẽ được gửi về cho người dùng thông qua:
    - Email
    - Notification (về ứng dụng mobile),
    - web notification (hình cái chuông trên web, và trong ứng dụng).
    ![image](https://user-images.githubusercontent.com/66956549/156488301-ae182a76-9c45-48cb-9c77-ff32c54146d5.png)
-  Muốn nhận được thông báo web notification cần bật chức năng nhận thông báo của Diễn đàn:
![image](https://user-images.githubusercontent.com/66956549/156488388-cf4ba2c4-4a91-4198-bcd6-77ab02f2489e.png)
## Thêm bài viết vào Diễn đàn:
- Trên trang chủ phần **Thông báo mới nhất** chọn thêm **Thêm một chủ đề mới…**
![image](https://user-images.githubusercontent.com/66956549/156490897-596ee20f-2ad8-4cef-9702-343c7efa03c5.png)
- Nhập thông báo
![Untitled](https://user-images.githubusercontent.com/66956549/156489475-7c802728-d625-4b52-833f-c11f824ef210.png)
- Nếu không chọn *“Gửi thông báo bài đăng diễn đàn mà không kèm với trì hoãn thời gian chỉnh sửa”* thì sau mặc định là 30 phút, thì thông báo lần 1 mới gửi đến cho người dùng.
- Còn nếu chọn thì thông báo lần 1 sẽ gửi luôn đến cho người dùng.
- Chọn **Gửi bài viết lên Diễn đàn**
![image](https://user-images.githubusercontent.com/66956549/156489610-bf15d452-e0c9-4bf1-ad5d-e8dcac65c2f8.png)
- Thông báo lần 2 thì sẽ gửi sau 1 khoảng thời gian (đặt ở phần setting) kể từ khi thông báo lần 1 được gửi.
![image](https://user-images.githubusercontent.com/66956549/156490036-2e9288de-946a-48a2-92a8-e0cdaac50fff.png)
- Thời gian gửi thông báo 1, vào setting theo đường dẫn:
`Quản trị khu vực>Bảo mật>Chính sách của hệ thống`
![image](https://user-images.githubusercontent.com/66956549/156490494-9eb0bb23-3da4-4704-80be-c0f01891dc31.png)
- Thông báo sẽ nhận được qua Email và web Notification
![image](https://user-images.githubusercontent.com/66956549/156490685-808cd7a7-321d-4d14-a519-459024ed46ec.png)
- Chọn thông báo để xem thông tin chi tiết