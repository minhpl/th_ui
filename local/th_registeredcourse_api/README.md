# Mô tả chung
 - Tên plugin: th_registeredcourse_api
 - Kiểu plugin: local
 - Project: VMC
 - Chức năng: Để thêm người dùng vào khóa học để họ tự kích hoạt vào khóa học. Nếu chưa có tài khoản sẽ tạo. Hủy đăng ký học
 - Người phát triển: datdt720@wru.vn
# Mô tả chi tiết
- Hệ thống sẽ tạo tài khoản (nếu chưa có) và đăng kí người dùng vào khóa học.
- Kiểm tra số điện thoại của người dùng trong hệ thống, nếu chưa có thì tạo tài khoản và đăng kí người dùng vào khóa học
- Hủy Đăng ký học
- Thông tin tài khoản:
    - Tên đăng nhập: số điện thoại
    - Họ và tên: Họ và tên
    - Email: 
        - Email
        - Nếu không có email dùng ‘sodienthoai@nomail.com’
- Nếu người dùng trong hệ thống đã có trùng số điện thoại, không tạo tài khoản, và đăng kí người dùng vào khóa học.

- Method: POST 
- Endpoint: https://elearning.vmcvietnam.org

- Header:
    - Content-Type: application/json
    - Authorization: {token}
    - Accept: application/json

- Đăng ký học: Resource /webservice/restful/server.php/local_th_enrolcourse
JSON Payload sample:

`{
    "enrol":
    {
        "userinfo":
        {
            "userfullname": "Đặng Tiến Đạt",
            "phonenumber": 123456789,
            "email": "datdt@sambala.net"
        },
        "courses":
        [
            {
                "courseshortname": "VMC2105.MAT",
                "campaigncode": "code1",
                "campaignname": "name1",
                "courseprice": 123
            },
            {
                "courseshortname": "VMC2106.MUI",
                "campaigncode": "code2",
                "campaignname": "name2",
                "courseprice": 1234
            }
        ],
        "order":
        {
            "ordercode": "ordercode1",
            "ordername": "test",
            "description": "test",
            "totalprice": 12345
        }
    }
}`

JSON Response sample:
`{
    "success":
    [
        {
            "shortname": "shortname1",
            "fullname": "fullname1"
        }
    ],
    "errors":
    [
        {
            "shortname": "shortname2",
            "error": "no course"
        },
        {
            "shortname": "shortname3",
            "error": "no course"
        }
    ],
    "error": "no phone"
}`

- Hủy đăng ký học: Resource /webservice/restful/server.php/local_th_unenrolcourse
JSON Payload sample:
`{
    "unenrol":
    {
        "userinfo":
        {
            "userfullname": "Nguyen Van A",
            "phonenumber": 1234567890,
            "email": "1234567890@nomail.com"
        },
        "courses":
        [
            {
                "courseshortname": "shortname1",
                "campaigncode": "code1",
                "campaignname": "name1",
                "courseprice": 123
            }
        ],
        "order":
        {
            "ordercode": "code2",
            "totalprice": 12345,
            "status": "huy don hang"
        }
    }
}`

JSON Response sample:
`{
    "success":
    [
        {
            "shortname": "shortname1",
            "fullname": "fullname1",
            "method": "unenrol"
        }
    ],
    "errors":
    [
        {
            "shortname": "shortname2",
            "fullname": "fullname2",
            "error": "no course"
        }
    ],
    "error": "no phone"
}`
Lưu ý:
- Token: dùng thể xác thực, sẽ được gửi riêng ở thư khác.

