<?php

defined('MOODLE_INTERNAL') || die();

require_once "$CFG->libdir/externallib.php";

class local_th_registeredcourse_api_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */
    public static function enrolcourse_parameters() {
        global $CFG;
        $enrol = [
            'userinfo' => new external_single_structure(
                [
                    'userfullname' => new external_value(PARAM_TEXT, 'User fullname', VALUE_OPTIONAL),
                    'phonenumber' => new external_value(PARAM_TEXT, 'Phone', VALUE_OPTIONAL),
                    'email' => new external_value(PARAM_TEXT, 'User email', VALUE_OPTIONAL),
                ]
                , 'User info', VALUE_OPTIONAL),

            'courses' => new external_multiple_structure(
                new external_single_structure(
                    [
                        'courseshortname' => new external_value(PARAM_TEXT, 'Course short name', VALUE_OPTIONAL),
                        'campaigncode' => new external_value(PARAM_TEXT, 'Campaign code', VALUE_OPTIONAL),
                        'campaignname' => new external_value(PARAM_TEXT, 'Campaign name', VALUE_OPTIONAL),
                        'courseprice' => new external_value(PARAM_TEXT, 'Course price', VALUE_OPTIONAL),
                    ]
                ), 'Enrol course', VALUE_OPTIONAL),

            'order' => new external_single_structure(
                [
                    'ordercode' => new external_value(PARAM_TEXT, 'Order code', VALUE_OPTIONAL),
                    'ordername' => new external_value(PARAM_TEXT, 'Order name', VALUE_OPTIONAL),
                    'description' => new external_value(PARAM_TEXT, 'Description', VALUE_OPTIONAL),
                    'totalprice' => new external_value(PARAM_TEXT, 'Total price', VALUE_OPTIONAL),
                ], 'Order', VALUE_OPTIONAL),
        ];
        return new external_function_parameters(
            [
                'enrol' => new external_single_structure($enrol),
            ]
        );
    }
    /**
     * registeredcourse of users.
     *
     * Function throw an exception at the first error encountered.
     * @param array $registeredcourses  An array of user registeredcourse
     * @since Moodle 2.2
     */
    public static function enrolcourse($enrol) {
        global $DB, $CFG;

        require_once $CFG->libdir . '/enrollib.php';
        require_once $CFG->dirroot . '/user/lib.php';

        $results = array();

        $ordercode = $ordername = $description = $totalprice = '';

        if (isset($enrol['order'])) {
            $order = $enrol['order'];
            if (isset($order['ordercode'])) {
                $ordercode = trim($order['ordercode']);
            } else {
                $results['error'] = 'Không nhận được Mã đơn hàng';
            }
            if (isset($order['ordername'])) {
                $ordername = trim($order['ordername']);
            }
            if (isset($order['description'])) {
                $description = trim($order['description']);
            }
            if (isset($order['totalprice'])) {
                $totalprice = trim($order['totalprice']);
            }
        } else {
            $results['error'] = 'Không nhận được Đơn hàng';
        }
        $th_order = new stdClass();
        $th_order->ordercode = $ordercode;
        $th_order->ordername = $ordername;
        $th_order->description = $description;
        $th_order->totalprice = $totalprice;
        if ($orderid = $DB->get_field_sql("SELECT id FROM {th_order} WHERE ordercode LIKE '" . $ordercode . "'")) {

            $th_order->id = $orderid;
            $DB->update_record('th_order', $th_order);

        } else {

            $th_order->timecreated = time();
            $orderid = $DB->insert_record('th_order', $th_order, true);
        }

        $userfullname = $phonenumber = $email = '';

        if (isset($enrol['userinfo'])) {
            $userinfo = $enrol['userinfo'];

            if (isset($userinfo['userfullname'])) {
                $userfullname = trim($userinfo['userfullname']);
            } else {
                //khong co ten
                $results['error'] = 'Không nhận được Họ và tên';
            }

            if (isset($userinfo['phonenumber'])) {
                $phonenumber = trim($userinfo['phonenumber']);
                if (!is_numeric($phonenumber)) {
                    //so dien thoai khong phai la so
                    $results['error'] = 'Số điện thoại không hợp lệ';
                }
            } else {
                //khong co dien thoai
                $results['error'] = 'Không có số điện thoại';
            }
            if (isset($userinfo['email'])) {
                $email = trim($userinfo['email']);
                if (!validate_email($email)) {
                    // email khong hop le
                    $results['error'] = 'Email không hợp lệ';
                }
            }
        } else {
            // Khong co user
            $results['error'] = 'Không nhận được thông tin Tài khoản';
        }

        if (isset($enrol['courses'])) {
            $courses = $enrol['courses'];
            if (!is_array($courses) or !$courses) {
                $results['error'] = 'Không nhận được Khóa học';
            }
        } else {
            // khong co courses
            $results['error'] = 'Không nhận được Khóa học';
        }

        if ($results) {
            $local_registeredcourse_api = new stdClass();
            $local_registeredcourse_api->fullname = $userfullname;
            $local_registeredcourse_api->phone = $phonenumber;
            $local_registeredcourse_api->email = $email;
            $local_registeredcourse_api->orderid = $orderid;
            $local_registeredcourse_api->timecreated = time();
            $local_registeredcourse_api->message = $results['error'];
            $DB->insert_record('local_registeredcourse_api', $local_registeredcourse_api);

            self::send_mail($ordercode, $results);

            return $results;
        }

        $userfullname_arr = explode(" ", $userfullname);
        if (count($userfullname_arr) < 2) {
            $firstname = "User";
            $lastname = $userfullname_arr[0];
        } else {
            $firstname = trim(str_replace(end($userfullname_arr), "", $userfullname));
            $lastname = trim(end($userfullname_arr));
        }
        if (!isset($email)) {
            $email = $phonenumber . '@nomail.com';
        }

        if ($user = $DB->get_record('user', array('username' => $phonenumber))) {
            $userid = $user->id;
        } else {
            if ($user = $DB->get_record('user', array('phone2' => $phonenumber))) {
                $userid = $user->id;
            } else {
                if ($user = $DB->get_record('user', array('email' => $email))) {
                    $userid = $user->id;
                } else {
                    // khong co user
                    $user['username'] = $phonenumber;
                    $user['email'] = $email;
                    $user['firstname'] = $firstname;
                    $user['lastname'] = $lastname;
                    $user['firstnamephonetic'] = "";
                    $user['lastnamephonetic'] = "";
                    $user['middlename'] = "";
                    $user['alternatename'] = "";
                    $user['password'] = '';
                    $user['auth'] = 'manual';
                    $user['phone2'] = $phonenumber;
                    $user['confirmed'] = 1;
                    $user['mnethostid'] = 1;

                    $user['id'] = user_create_user($user, false, false);
                    $usernew = $DB->get_record('user', array('id' => $user['id']));
                    setnew_password_and_mail($usernew);
                    unset_user_preference('create_password', $usernew);
                    set_user_preference('auth_forcepasswordchange', 1, $usernew);
                    \core\event\user_created::create_from_userid($user['id'])->trigger();

                    $userid = $user['id'];
                }
            }
        }

        // $shortname = $campaigncode = $campaignname = $courseprice = '';
        foreach ($courses as $key => $c) {
            if (isset($c['courseshortname'])) {
                $shortname = trim($c['courseshortname']);
            } else {
                $shortname = '';
            }
            if (isset($c['campaigncode'])) {
                $campaigncode = trim($c['campaigncode']);
            } else {
                $campaigncode = '';
            }
            if (isset($c['campaignname'])) {
                $campaignname = trim($c['campaignname']);
            } else {
                $campaignname = '';
            }
            if (isset($c['courseprice'])) {
                $courseprice = trim($c['courseprice']);
            } else {
                $courseprice = '';
            }
            // print_object($campaigncode);
            $local_registeredcourse_api = new stdClass();
            $local_registeredcourse_api->fullname = $userfullname;
            $local_registeredcourse_api->phone = $phonenumber;
            $local_registeredcourse_api->email = $email;
            $local_registeredcourse_api->orderid = $orderid;
            $local_registeredcourse_api->userid = $userid;
            $local_registeredcourse_api->timecreated = time();
            if ($course = $DB->get_record('course', array('shortname' => $shortname))) {
                if ($campaignid = $DB->get_field('marketing_campaign', 'id', array('campaigncode' => $campaigncode))) {

                } else {
                    //khong co campaign
                    if ($campaigncode and $campaignname) {
                        $campaign = new stdClass();
                        $campaign->campaigncode = $campaigncode;
                        $campaign->campaignname = $campaignname;
                        $campaign->timecreated = time();
                        $campaignid = $DB->insert_record('marketing_campaign', $campaign);
                    }
                }

                if (!$DB->record_exists('user_campaign_course', array('userid' => $userid, 'courseid' => $course->id, 'campaignid' => $campaignid))) {

                    $user_campaign_course = new stdClass();
                    $user_campaign_course->userid = $userid;
                    $user_campaign_course->courseid = $course->id;
                    $user_campaign_course->campaignid = $campaignid;
                    $user_campaign_course->timecreated = time();
                    $DB->insert_record('user_campaign_course', $user_campaign_course);
                }

                if ($DB->get_record('th_registeredcourses', ['userid' => $userid, 'courseid' => $course->id])) {

                    $DB->set_field('th_registeredcourses', 'timeactivated', 0, ['userid' => $userid, 'courseid' => $course->id]);
                } else {
                    $th_registeredcourses = new stdClass();
                    $th_registeredcourses->userid = $userid;
                    $th_registeredcourses->courseid = $course->id;
                    $th_registeredcourses->timeactivated = 0;
                    $th_registeredcourses->timecreated = time();
                    $DB->insert_record('th_registeredcourses', $th_registeredcourses);
                }

                $results['success'][] = array('shortname' => $shortname, 'fullname' => $course->fullname);
                $local_registeredcourse_api->status = 1;
                $local_registeredcourse_api->message = "Đăng ký thành công";
                $local_registeredcourse_api->courseid = $course->id;
                $local_registeredcourse_api->campaignid = $campaignid;
            } else {
                //khong co course
                $results['errors'][] = array('shortname' => $shortname, 'error' => 'Không có Khóa học');
                $local_registeredcourse_api->status = 0;
                $local_registeredcourse_api->courseid = $shortname;
                $local_registeredcourse_api->message = "Không có Khóa học";
            }

            $local_registeredcourse_api->courseprice = $courseprice;
            $DB->insert_record('local_registeredcourse_api', $local_registeredcourse_api);
        }
        // exit;
        if (isset($results['errors'])) {
            self::send_mail($ordercode, $results);
        }

        return $results;
    }

    public static function send_mail($ordercode, $results) {
        global $CFG, $DB;
        $str_email = get_config('local_th_registeredcourse_api', 'email');
        $email_arr = explode(",", $str_email);
        $subject = "Đơn hàng $ordercode";
        $userfrom = core_user::get_noreply_user();
        $messages = '';
        if (isset($results['error'])) {
            $messages .= $results['error'] . "\n";
        }
        foreach ($email_arr as $key => $e) {
            if (isset($results['errors'])) {
                foreach ($results['errors'] as $key => $result) {
                    $messages .= "Khóa học " . $result['shortname'] . " : " . $result['error'] . "\n";
                }
            }
            if ($user = $DB->get_record('user', array('email' => trim($e)))) {
                email_to_user($user, $userfrom, $subject, $messages);
            }
        }
    }
    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function enrolcourse_returns() {
        return new external_single_structure(
            [
                'success' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'shortname' => new external_value(PARAM_TEXT, 'Course shortname', VALUE_OPTIONAL),
                            'fullname' => new external_value(PARAM_TEXT, 'Course fullname', VALUE_OPTIONAL),
                        ]
                    ), 'Enrol success', VALUE_OPTIONAL),

                'errors' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'shortname' => new external_value(PARAM_TEXT, 'Course shortname', VALUE_OPTIONAL),
                            'error' => new external_value(PARAM_TEXT, 'Enrol error', VALUE_OPTIONAL),
                        ]
                    ), 'Enrol error', VALUE_OPTIONAL),

                'error' => new external_value(PARAM_TEXT, 'Error information', VALUE_OPTIONAL),
            ]
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */
    public static function unenrolcourse_parameters() {
        global $CFG;
        $unenrol = [

            'userinfo' => new external_single_structure(
                [
                    'userfullname' => new external_value(PARAM_TEXT, 'User fullname', VALUE_OPTIONAL),
                    'phonenumber' => new external_value(PARAM_TEXT, 'Phone', VALUE_OPTIONAL),
                    'email' => new external_value(PARAM_TEXT, 'User email', VALUE_OPTIONAL),
                ]
                , 'User info', VALUE_OPTIONAL),

            'courses' => new external_multiple_structure(
                new external_single_structure(
                    [
                        'courseshortname' => new external_value(PARAM_TEXT, 'Course short name', VALUE_OPTIONAL),
                        'campaigncode' => new external_value(PARAM_TEXT, 'Campaign code', VALUE_OPTIONAL),
                        'campaignname' => new external_value(PARAM_TEXT, 'Campaign name', VALUE_OPTIONAL),
                        'courseprice' => new external_value(PARAM_TEXT, 'Course price', VALUE_OPTIONAL),
                    ]
                ), 'Unenrol course', VALUE_OPTIONAL),

            'order' => new external_single_structure(
                [
                    'ordercode' => new external_value(PARAM_TEXT, 'Order code'),
                    'totalprice' => new external_value(PARAM_TEXT, 'Total price'),
                    'status' => new external_value(PARAM_TEXT, 'Order status'),
                ], 'Order', VALUE_OPTIONAL),
        ];
        return new external_function_parameters(
            [
                'unenrol' => new external_single_structure($unenrol),
            ]
        );
    }
    /**
     * unenrol of users.
     *
     * Function throw an exception at the first error encountered.
     * @param array $registeredcourses  An array of user registeredcourse
     * @since Moodle 2.2
     */
    public static function unenrolcourse($unenrol) {
        global $DB, $CFG;

        require_once $CFG->libdir . '/enrollib.php';
        require_once $CFG->dirroot . '/user/lib.php';

        $results = array();

        $ordercode = $totalprice = $status = '';
        if (isset($unenrol['order'])) {
            $order = $unenrol['order'];
            if (isset($order['ordercode'])) {
                $ordercode = $order['ordercode'];
            } else {
                $results['error'] = 'Không nhận được Mã đơn hàng';
            }
            if (isset($order['status'])) {
                $status = $order['status'];
            }
        } else {
            $results['error'] = 'Không nhận được Đơn hàng';
        }

        if (!$orderid = $DB->get_field_sql("SELECT id FROM {th_order} WHERE ordercode LIKE '" . $ordercode . "'")) {
            $results['error'] = 'Mã đơn hàng không tồn tại';
        }

        $th_order_status = new stdClass();
        $th_order_status->orderid = $orderid;
        $th_order_status->status = $status;
        $th_order_status->timecreated = time();
        $DB->insert_record('th_order_status', $th_order_status);

        $userfullname = $phonenumber = $email = '';
        if (isset($unenrol['userinfo'])) {
            $userinfo = $unenrol['userinfo'];

            if (isset($userinfo['userfullname'])) {
                $userfullname = trim($userinfo['userfullname']);
            } else {
                //khong co ten
                $results['error'] = 'Không nhận được Họ và tên';
            }
            if (isset($userinfo['phonenumber'])) {
                $phonenumber = trim($userinfo['phonenumber']);
                if (!is_numeric($phonenumber)) {
                    //so dien thoai khong phai la so
                    $results['error'] = 'Số điện thoại không hợp lệ';
                }
            } else {
                //khong co dien thoai
                $results['error'] = 'Không nhận được số điện thoại';
            }
            if (isset($userinfo['email'])) {
                $email = trim($userinfo['email']);
                if (!validate_email($email)) {
                    // email khong hop le
                    $results['error'] = 'Email không hợp lệ';
                }
            }
        } else {
            // Khong co user
            $results['error'] = 'Không nhận được thông tin Tài khoản';
        }

        if (isset($unenrol['courses'])) {
            $courses = $unenrol['courses'];
            if (!is_array($courses) or !$courses) {
                $results['error'] = 'Không nhận được Khóa học';
            }
        } else {
            // khong co courses
            $results['error'] = 'Không nhận được Khóa học';
        }

        if ($results) {
            $local_registeredcourse_api = new stdClass();
            $local_registeredcourse_api->fullname = $userfullname;
            $local_registeredcourse_api->phone = $phonenumber;
            $local_registeredcourse_api->email = $email;
            $local_registeredcourse_api->orderid = $orderid;
            $local_registeredcourse_api->timecreated = time();
            $local_registeredcourse_api->message = $results['error'];
            $DB->insert_record('local_registeredcourse_api', $local_registeredcourse_api);
            self::send_mail($ordercode, $results);
            return $results;
        }

        if (!isset($email)) {
            $email = $phonenumber . '@nomail.com';
        }

        $userfullname_arr = explode(" ", $userfullname);
        if (count($userfullname_arr) < 2) {
            $firstname = "User";
            $lastname = $userfullname_arr[0];
        } else {
            $firstname = trim(str_replace(end($userfullname_arr), "", $userfullname));
            $lastname = trim(end($userfullname_arr));
        }
        if ($user = $DB->get_record('user', array('username' => $phonenumber))) {
            $userid = $user->id;
        } else {
            if ($user = $DB->get_record('user', array('phone2' => $phonenumber))) {
                $userid = $user->id;
            } else {
                if ($user = $DB->get_record('user', array('email' => $email))) {
                    $userid = $user->id;
                } else {
                    // khong co user
                    $results['error'] = 'Không có tài khoản';
                    $local_registeredcourse_api->message = $results['error'];
                    $DB->insert_record('local_registeredcourse_api', $local_registeredcourse_api);
                    self::send_mail($ordercode, $results);
                    return $results;
                }
            }
        }

        $shortname = $campaigncode = $campaignname = $courseprice = '';
        foreach ($courses as $key => $c) {
            if (isset($c['courseshortname'])) {
                $shortname = trim($c['courseshortname']);
            }
            if (isset($c['campaigncode'])) {
                $campaigncode = trim($c['campaigncode']);
            }
            if (isset($c['campaignname'])) {
                $campaignname = trim($c['campaignname']);
            }
            if (isset($c['courseprice'])) {
                $courseprice = trim($c['courseprice']);
            }
            $local_registeredcourse_api = new stdClass();
            $local_registeredcourse_api->fullname = $userfullname;
            $local_registeredcourse_api->phone = $phonenumber;
            $local_registeredcourse_api->email = $email;
            $local_registeredcourse_api->orderid = $orderid;
            $local_registeredcourse_api->timecreated = time();
            $local_registeredcourse_api->userid = $userid;
            if ($course = $DB->get_record('course', array('shortname' => $shortname))) {
                $local_registeredcourse_api->courseid = $course->id;
                $sql = "SELECT * FROM {local_registeredcourse_api} WHERE orderid = $orderid AND userid LIKE '" . $userid . "'" . "AND courseid LIKE '" . $course->id . "'" . "AND status = 1";

                if ($DB->record_exists_sql($sql)) {

                    if ($campaignid = $DB->get_field('marketing_campaign', 'id', array('campaigncode' => $campaigncode))) {
                        $params = array('userid' => $userid, 'courseid' => $course->id, 'campaignid' => $campaignid);
                        $DB->delete_records('user_campaign_course', $params);
                    }

                    // Huy dang ky
                    if ($DB->record_exists('th_registeredcourses', array('userid' => $userid, 'courseid' => $course->id, 'timeactivated' => 0))) {

                        $DB->set_field('th_registeredcourses', 'timeactivated', 1, ['userid' => $userid, 'courseid' => $course->id]);
                        $local_registeredcourse_api->status = 2;
                        $local_registeredcourse_api->message = "Hủy Đăng ký thành công";
                        $results['success'][] = array('shortname' => $shortname, 'fullname' => $course->fullname, 'method' => 'Hủy Đăng ký thành công');

                    } else {

                        //kiem tra co dang hoc hay khong
                        $context = context_course::instance($course->id);
                        if (is_enrolled($context, $userid)) {
                            $instance = $DB->get_record('enrol', array('courseid' => $course->id, 'status' => ENROL_INSTANCE_ENABLED, 'enrol' => 'manual'));
                            // $enrolinstances = enrol_get_instances($course->id, false);
                            // foreach ($enrolinstances as $instance) {

                            if ($instance->status == ENROL_INSTANCE_ENABLED) {

                                $plugin = enrol_get_plugin($instance->enrol);
                                $plugin->unenrol_user($instance, $userid);
                                // $plugin->update_user_enrol($instance, $userid, ENROL_USER_SUSPENDED);
                                $local_registeredcourse_api->status = 2;
                                $local_registeredcourse_api->message = "Hủy ghi danh thành công";
                                $results['success'][] = array('shortname' => $shortname, 'fullname' => $course->fullname, 'method' => 'Hủy ghi danh thành công');
                            } else {
                                $local_registeredcourse_api->message = "Không Hủy ghi danh được";
                                $results['errors'][] = array('shortname' => $shortname, 'fullname' => $course->fullname, 'error' => 'Không Hủy ghi danh được');
                            }
                            // }
                        } else {
                            //khong duoc ghi danh
                            $local_registeredcourse_api->status = 2;
                            $local_registeredcourse_api->message = "Hủy ghi danh thành công";
                            $results['success'][] = array('shortname' => $shortname, 'fullname' => $course->fullname, 'method' => 'Hủy ghi danh thành công');
                        }
                    }
                    $local_registeredcourse_api->campaignid = $campaignid;
                } else {
                    $local_registeredcourse_api->message = "Khóa học đăng ký và khóa học hủy không giống nhau";
                    $results['errors'][] = array('shortname' => $shortname, 'fullname' => $course->fullname, 'error' => 'Khóa học đăng ký và khóa học hủy không giống nhau');
                }

            } else {
                //khong co course
                $results['errors'][] = array('shortname' => $shortname, 'error' => 'No course');
                $local_registeredcourse_api->courseid = $shortname;
                $local_registeredcourse_api->message = "Không có khóa học";
            }

            $local_registeredcourse_api->courseprice = $courseprice;
            $DB->insert_record('local_registeredcourse_api', $local_registeredcourse_api);
        }
        if (isset($results['errors']) or isset($results['error'])) {
            self::send_mail($ordercode, $results);
        }

        return $results;
    }
    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function unenrolcourse_returns() {
        return new external_single_structure(
            [
                'success' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'shortname' => new external_value(PARAM_TEXT, 'Course shortname', VALUE_OPTIONAL),
                            'fullname' => new external_value(PARAM_TEXT, 'Course fullname', VALUE_OPTIONAL),
                            'method' => new external_value(PARAM_TEXT, 'Unenrol method', VALUE_OPTIONAL),
                        ]
                    ), 'Unenrol success', VALUE_OPTIONAL),

                'errors' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'shortname' => new external_value(PARAM_TEXT, 'Course shortname', VALUE_OPTIONAL),
                            'fullname' => new external_value(PARAM_TEXT, 'Course fullname', VALUE_OPTIONAL),
                            'error' => new external_value(PARAM_TEXT, 'Unenrol error', VALUE_OPTIONAL),
                        ]
                    ), 'Unenrol error', VALUE_OPTIONAL),

                'error' => new external_value(PARAM_TEXT, 'Error information', VALUE_OPTIONAL),
            ]
        );
    }
}
