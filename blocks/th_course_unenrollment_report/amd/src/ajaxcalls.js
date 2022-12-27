define(['jquery', 'core/notification', 'core/ajax'], function($, notification, ajax) {

$(document).ready(function() {
    $(".click").on('click', function() {

        var click_class = $(this).attr("class");
        click_class = click_class.replace('click ', '');
        click_class = click_class.replace(' ', '.');

        var date = $("th." + click_class).eq(0).text();
        var courseid = $(this).parent().attr('id');

        var fullname_course = $(this).siblings("td.c1").text();
        fullname_course = "<h2 class='fullname'>" + fullname_course + "</h2>";

        if ($(".fullname").length) {
            $("table.fullname").parent().remove();
            $("h2.fullname").remove();
        }
        // else {

        function Ajaxcall() {
            this.value = "ajax ok";
        }

        Ajaxcall.prototype.load_users = function(courseid, date) {

            var promises = ajax.call([{
                methodname: 'block_th_course_unenrollment_report_load_users',
                args: {
                    courseid: courseid,
                    date: date
                },
                done: console.log("ajax done"),
                fail: notification.exception
            }]);

            promises[0].done(function(response) {

                // console.log(response);
                var lang = document.documentElement.lang;
                var stt = 1;

                var table = "<table border='1' id='myTable' class='fullname'>";
                table += "<thead>";
                table += "<tr>";
                if (lang == 'vi') {
                    table += "<th>STT</th>";
                    table += "<th>Họ và tên</th>";
                    table += "<th>Tên tài khoản</th>";
                    table += "<th>Quyền</th>";
                    table += "<th>Tổ chức</th>";
                    table += "<th>Ngày ghi danh</th>";
                    table += "<th>Ngày kích hoạt</th>";
                    table += "<th>Ngày hết hạn</th>";
                    table += "<th>Trạng thái</th>";
                } else {
                    table += "<th>No.</th>";
                    table += "<th>Fullname</th>";
                    table += "<th>Username</th>";
                    table += "<th>Role</th>";
                    table += "<th>Institution</th>";
                    table += "<th>Enrolment Date</th>";
                    table += "<th>Enrolment Activation Date</th>";
                    table += "<th>Enrolment Expire Date</th>";
                    table += "<th>Enrolment Status</th>";
                }

                table += "</tr>";
                table += "</thead>";
                table += "<tbody>";

                for (let key in response) {
                    table += "<tr>";
                    table += "<td style='text-align:center;'>" + stt + "</td>";

                    for (let key1 in response[key]) {
                        if (key1 != 'userid') {
                            table += "<td>" + response[key][key1] + "</td>";
                        }
                    }
                    stt++;
                    table += "</tr>";
                }

                table += "</tbody>";
                table += "</table>";
                $('#DataTables_Table_0_wrapper').after(table);

                var configure = {
                    "lengthMenu": [
                        [10, 25, 50, -1],
                        [10, 25, 50, "All"]
                    ],
                    "pageLength": 25,
                    "dom": 'lBfrtip',
                };
                if (lang == 'vi') {
                    configure.language = {
                        "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Vietnamese.json",
                        buttons: {
                            mcopy: "sao chép",
                            print: "in",
                        },
                        "decimal": ","
                    }
                }
                // $("." + courseid).DataTable(configure);
                $("#myTable").DataTable(configure);
                $('#DataTables_Table_0_wrapper').after(fullname_course);

                var url = window.location.href;
                window.location = window.location.pathname + "#myTable";

            }).fail(function(ex) {
                console.log(ex);
            });
        };

        var ajax1 = new Ajaxcall();
        ajax1.load_users(courseid, date);

    // }
    });
});
});