define(['jquery', 'block_th_gradereport/ajaxcalls'],
    function($, Ajaxcalls) {

        function on_select_change_value(onDocumentReady = false) {
            var data = $('.mform').serializeArray().reduce(function(obj, item) {
                obj[item.name] = item.value;
                var selector = "[name='" + item.name + "'] option:selected";
                obj[item.name + "_text"] = $(selector).text();
                return obj;
            }, {});

            var makhoa = data["makhoaid_text"];
            var malop = data["malopid_text"];
            var time_from_day = data["time_from[day]"];
            var time_from_month = data["time_from[month]"];
            var time_from_year = data["time_from[year]"];
            var time_to_day = data["time_to[day]"];
            var time_to_month = data["time_to[month]"];
            var time_to_year = data["time_to[year]"];
            var userid = data["userid"];

            // var time_from = new Date(time_from_year, time_from_month, time_from_day).getTime() / 1000;
            // var time_to = new Date(time_to_year, time_to_month, time_to_day).getTime() / 1000;
            console.log(data);


            if (!userid || userid == '') userid = 0;
            var ajaxx = require("block_th_gradereport/ajaxcalls");
            var ajax1 = new ajaxx();
            ajax1.loadcourses(makhoa, malop, userid, null, null, onDocumentReady);
        }

        return {
            loadCourseOption: function() {
                $(document).ready(function() {

                    on_select_change_value(true);
                    $('select.custom-select ').each(function(index, el) {
                        var el = $(el);
                        if ($(el).attr("id") == 'id_courseidarr') {
                            return;
                        }
                        $(el).on('change', function() {
                            $("#fitem_id_courseidarr .form-autocomplete-selection").html('<span class="mb-3 mr-1">No Selection</span>');
                            on_select_change_value();
                        });
                    });

                    $('#fgroup_id_show_option input.form-check-input').on('change', function() {
                        console.log("Ratio value change");
                        $("#fitem_id_courseidarr .form-autocomplete-selection").html('<span class="mb-3 mr-1">No Selection</span>');
                        on_select_change_value();
                    })
                });
            }
        };
    });