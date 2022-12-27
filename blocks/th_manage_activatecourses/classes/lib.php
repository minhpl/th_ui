<?php

define('BLOCKth_bulkactivatecourse_HINT', 'hint');
define('BLOCKth_bulkactivatecourse_ENROLUSERS', 'enrolusers');

class th_manage_activatecourses {

    public static function get_fullname_course($courseid) {
        global $DB;
        $sql = "SELECT fullname FROM {course} WHERE id = $courseid";
        $fullname = $DB->get_field_sql($sql);
        return $fullname;
    }

    public static function get_shortname_course($courseid) {
        global $DB;
        $sql = "SELECT shortname FROM {course} WHERE id = $courseid";
        $shortname = $DB->get_field_sql($sql);
        return $shortname;
    }

    public static function get_courseid_by_shortname($shortname) {
        global $DB;

        $check = $DB->record_exists('course', array('shortname' => $shortname));

        if ($check) {
            return $DB->get_record('course', array('shortname' => $shortname), 'id')->id;
        }
        return;
    }

    public static function get_userid_fullname($userid) {
        global $DB;
        $userfields = 'id, ' . get_all_user_name_fields(true);
        $user = $DB->get_record('user', array('id' => $userid), $userfields);
        return fullname($user);
    }

    public function get_all_campaign() {

        global $DB;
        $allCampaign = array();
        $records = $DB->get_records('marketing_campaign');

        if (!empty($records)) {
            foreach ($records as $key => $record) {
                $allCampaign[$key] = $record->campaignname;
            }
        }

        return $allCampaign;
    }

    public static function get_name_campaign($campaignid) {

        global $DB;

        return $DB->get_record('marketing_campaign', array('id' => $campaignid), 'campaignname')->campaignname;
    }

    public static function check_user_campaign_course($dataobject) {

        global $DB;

        $userid = $dataobject->userid;
        $courseid = $dataobject->courseid;
        $campaignid = $dataobject->campaignid;

        $check_campaign = $DB->record_exists('marketing_campaign', array('id' => $campaignid));
        if (!$check_campaign) {
            return true;
        }

        $check_course = $DB->record_exists('course', array('id' => $courseid));
        if (!$check_course) {
            return true;
        }

        $check_user = $DB->record_exists('user', array('id' => $userid));
        if (!$check_user) {
            return true;
        }

        $data = [
            'userid' => $userid,
            'courseid' => $courseid,
            'campaignid' => $campaignid,
        ];

        $check_user_campaign_course = $DB->record_exists('user_campaign_course', $data);
        if ($check_user_campaign_course) {
            return true;
        }
        return false;
    }

    public static function update_user_register_courses($dataobject) {

        global $DB;

        return $DB->update_record('th_registeredcourses', $dataobject);
    }

    public static function get_user_register_courses() {
        global $DB;

        $sql = "SELECT DISTINCT u.id,email,username,firstname,lastname
			FROM {th_registeredcourses} thr JOIN {user} u ON thr.userid=u.id
			WHERE thr.timeactivated = 0 AND u.deleted=0 AND u.suspended=0";
        return $DB->get_records_sql($sql);
    }

    public static function check_register_courses($dataobject) {

        global $DB;

        $userid = $dataobject->userid;
        $courseid = $dataobject->courseid;

        $check_course = $DB->record_exists('course', array('id' => $courseid));
        // print_object($check_course);
        // exit();
        if (!$check_course) {
            return false;
        }

        $check_user = $DB->record_exists('user', array('id' => $userid));
        // print_object($check_user);
        if (!$check_user) {
            return false;
        }

        $data = array(
            'userid' => $userid,
            'courseid' => $courseid,
            'timeactivated' => 0,
        );

        $check_register_courses = $DB->record_exists('th_registeredcourses', $data);
        // print_object($check_register_courses);
        if (!$check_register_courses) {
            return true;
        }

        // $context = context_course::instance($courseid);
        // $check = is_enrolled($context, $userid);

        // if ($check) {
        //     return true;
        // }
        return false;
    }

    public static function unregister_courses($id) {
        global $DB;

        $sql = "DELETE FROM {th_registeredcourses}
				WHERE id= :id";
        $params = array("id" => $id);

        return $DB->execute($sql, $params);
    }

    public static function get_all_user_register_courses() {
        global $DB;

        $sql = "SELECT thr.*
			FROM {th_registeredcourses} thr
			JOIN {user} u ON thr.userid=u.id
			JOIN {course} c ON thr.courseid=c.id
			WHERE thr.timeactivated = 0 AND u.deleted=0 AND u.suspended=0";

        return $DB->get_records_sql($sql);
    }
    public static function active_course_edit_controls(context $context, moodle_url $currenturl) {
        $tabs = array();
        $currenttab = 'view';
        $viewurl = new moodle_url('/blocks/th_manage_activatecourses/view.php', array('contextid' => $context->id));

        if ($context->contextlevel == CONTEXT_SYSTEM) {
            $tabs[] = new tabobject('view', new moodle_url($viewurl), get_string('all', 'block_th_manage_activatecourses'));
            if ($currenturl->get_param('contextid')) {
                $currenttab = 'view';
            }
        }
        if (has_capability('block/th_manage_activatecourses:view', $context)) {
            $addurl = new moodle_url('/blocks/th_manage_activatecourses/edit.php', array('contextid' => $context->id));
            $tabs[] = new tabobject('edit', $addurl, get_string('add', 'block_th_manage_activatecourses'));
            if ($currenturl->get_path() === $addurl->get_path() && !$currenturl->param('id')) {
                $currenttab = 'edit';
            }
        }
        if (count($tabs) > 1) {
            return new tabtree($tabs, $currenttab);
        }
        return null;
    }
}

function add_activatecourses_display_table($successs, $errors) {
    global $CFG;

    foreach ($successs as $key => $success) {
        $row = array();

        $cell = new html_table_cell();
        $course_fullname = html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $success["courseid"], th_manage_activatecourses::get_fullname_course($success["courseid"]));
        $cell->text = $course_fullname;
        $row[] = $cell;

        $cell = new html_table_cell();
        $cell->text = th_manage_activatecourses::get_shortname_course($success["courseid"]);
        $row[] = $cell;

        $check = array_key_exists("campaignid", $success);
        $cell = new html_table_cell();
        if ($check) {
            $campaign_name = html_writer::link($CFG->wwwroot . '/blocks/th_vmc_campaign/user.php?id=' . $success["campaignid"], th_manage_activatecourses::get_name_campaign($success["campaignid"]));
            $cell->text = $campaign_name;
        } else {
            $cell->text = "";
        }
        $row[] = $cell;

        $cell = new html_table_cell();
        $cell->text = html_writer::tag('span',
            get_string('success', 'block_th_manage_activatecourses'),
            array('class' => 'badge badge-success'));
        $row[] = $cell;

        $data[] = $row;
    }

    foreach ($errors as $key => $error) {
        $row = array();

        $cell = new html_table_cell();
        $course_fullname = html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $error["courseid"], th_manage_activatecourses::get_fullname_course($error["courseid"]));
        $cell->text = $course_fullname;
        $row[] = $cell;

        $cell = new html_table_cell();
        $cell->text = th_manage_activatecourses::get_shortname_course($error["courseid"]);
        $row[] = $cell;

        $check = array_key_exists("campaignid", $error);
        $cell = new html_table_cell();
        if ($check) {
            $campaign_name = html_writer::link($CFG->wwwroot . '/blocks/th_vmc_campaign/user.php?id=' . $error["campaignid"], th_manage_activatecourses::get_name_campaign($error["campaignid"]));
            $cell->text = $campaign_name;
            $row[] = $cell;
            $cell = new html_table_cell();
            $cell->text = html_writer::tag('span',
                get_string('add_failed_user_campaign', 'block_th_manage_activatecourses'),
                array('class' => 'badge badge-secondary'));

        } else {
            $cell->text = "";
            $row[] = $cell;
            $cell = new html_table_cell();
            if ($error["error"] == 1) {
                $cell->text = html_writer::tag('span',
                    get_string('add_failed_user_enrolled', 'block_th_manage_activatecourses'),
                    array('class' => 'badge badge-secondary'));
            } else {
                $cell->text = html_writer::tag('span',
                    get_string('add_failed_user_registered', 'block_th_manage_activatecourses'),
                    array('class' => 'badge badge-secondary'));
            }
        }
        $row[] = $cell;
        $data[] = $row;
    }

    $table = new html_table();
    $table->id = "BLOCKth_bulkactivatecourse_ENROLUSERS";
    $table->attributes['class'] = 'generaltable';
    $table->summary = get_string('users_to_enrol_in_course', 'block_th_manage_activatecourses');
    $table->size = array('40%', '40%', '15%', '5%');
    $table->head = array();
    $table->head[] = get_string('course');
    $table->head[] = get_string('shortname');
    $table->head[] = get_string('campaign_name', 'block_th_manage_activatecourses');
    $table->head[] = get_string('status');
    $table->data = $data;

    return html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
}

function block_th_manage_activatecourses_controls(context $context, moodle_url $currenturl) {
    $tabs = array();
    $currenttab = 'view';
    $view = new moodle_url('/blocks/th_manage_activatecourses/view.php');

    if (has_capability('block/th_manage_activatecourses:view', $context)) {
        $addurl = new moodle_url('/blocks/th_manage_activatecourses/view.php');
        $tabs[] = new tabobject('view', $addurl, get_string('all', 'block_th_manage_activatecourses'));
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'view';
        }
    }
    if (has_capability('block/th_manage_activatecourses:view', $context)) {
        $addurl = new moodle_url('/blocks/th_manage_activatecourses/edit.php');
        $tabs[] = new tabobject('edit', $addurl, get_string('add', 'block_th_manage_activatecourses'));
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'edit';
        }
    }
    if (has_capability('block/th_manage_activatecourses:view', $context)) {
        $addurl = new moodle_url('/blocks/th_manage_activatecourses/user.php', array('option' => 0));
        $tabs[] = new tabobject('adds', $addurl, get_string('add_users', 'block_th_manage_activatecourses'));
        if ($currenturl->get_path() === $addurl->get_path() && $currenturl->param('option') == 0) {
            $currenttab = 'adds';
        }
    }
    if (has_capability('block/th_manage_activatecourses:view', $context)) {
        $addurl = new moodle_url('/blocks/th_manage_activatecourses/user.php', array('option' => 1));
        $tabs[] = new tabobject('delete', $addurl, get_string('del_users', 'block_th_manage_activatecourses'));
        if ($currenturl->get_path() === $addurl->get_path() && $currenturl->param('option') == 1) {
            $currenttab = 'delete';
        }
    }
    if (count($tabs) > 1) {
        return new tabtree($tabs, $currenttab);
    }
    return null;
}

function block_th_manage_activatecourses_check_user_mails($emailstextfield, $option) {

    $checkedemails = new stdClass();
    $checkedemails->emails_to_ignore = array();
    $checkedemails->error_messages = array();
    $checkedemails->moodleusers_for_email = array();
    $checkedemails->user_enroled = array();
    $checkedemails->validemailfound = 0;

    $emaildelimiters = array(', ', ' ', ',');

    if (!empty($emailstextfield)) {

        $emailslines = block_th_manage_activatecourses_parse_emails($emailstextfield);

        $linecnt = 0;

        // Process emails from textfield.
        foreach ($emailslines as $emailline) {
            $linecnt++;

            $error = '';

            $emailline = trim($emailline);
            $shortnameinlinecnt = substr_count($emailline, ',');
            // No email in row/line.
            if ($shortnameinlinecnt == 0) {

                $a = new stdClass();
                $a->line = $linecnt;
                $a->content = $emailline;
                $error = get_string('error_no_course', 'block_th_manage_activatecourses', $a);
                $checkedemails->error_messages[$linecnt] = $error;
                continue;
                // One email in row/line.
            }
            if (!empty($emailline)) {
                $array = explode(',', $emailline);
                $emailline = $array[0];
                $shortname = $array[1];
                $courseid = th_manage_activatecourses::get_courseid_by_shortname($shortname);
                if (empty($courseid)) {
                    $a = new stdClass();
                    $a->line = $linecnt;
                    $a->content = $shortname;
                    $error = get_string('error_no_course', 'block_th_manage_activatecourses', $a);
                    $checkedemails->error_messages[$linecnt] = $error;
                    continue;
                }
            }

            // Check number of emails in current row/line.
            $emailsinlinecnt = substr_count($emailline, '@');

            // No email in row/line.
            if ($emailsinlinecnt == 0) {

                $a = new stdClass();
                $a->line = $linecnt;
                $a->content = $emailline;
                $error = get_string('error_no_email', 'block_th_manage_activatecourses', $a);
                $checkedemails->error_messages[$linecnt] = $error;

                // One email in row/line.
            } else if ($emailsinlinecnt == 1) {
                $email = $emailline;
                block_th_manage_activatecourses_email($email, $linecnt, $courseid, $option, $checkedemails);
            }
            // More than one email in row/line.
            if ($emailsinlinecnt > 1) {
                $delimiter = '';

                // Check delimiters.
                foreach ($emaildelimiters as $emaildelimiter) {
                    $pos = strpos($emailline, $emaildelimiter);
                    if ($pos) {
                        $delimiter = $emaildelimiter;
                        break;
                    }
                }
                if (!empty($delimiter)) {
                    $emailsinline = explode($delimiter, $emailline);

                    // Iterate emails in row/line.
                    foreach ($emailsinline as $emailinline) {

                        $email = trim($emailinline);
                        block_th_manage_activatecourses_email($email, $linecnt, $courseid, $option, $checkedemails);
                    }
                }
            }
        }
    }
    // print_object($checkedemails);
    // exit();
    return $checkedemails;
}

function block_th_manage_activatecourses_parse_emails($emails) {
    if (empty($emails)) {
        return array();
    } else {
        $rawlines = explode(PHP_EOL, $emails);
        $result = array();
        foreach ($rawlines as $rawline) {
            $result[] = trim($rawline);
        }
        return $result;
    }
}

function block_th_manage_activatecourses_email($email, $linecnt, $courseid, $option, &$checkedemails) {
    // Check for valid email.
    $emailisvalid = validate_email($email);
    // Email is not valid.
    if (!$emailisvalid) {
        $checkedemails->emails_to_ignore[] = $email;
        $a = new stdClass();
        $a->row = $linecnt;
        $a->email = $email;
        $error = get_string('error_invalid_email', 'block_th_manage_activatecourses', $a);
        if (array_key_exists($linecnt, $checkedemails->error_messages)) {
            $errors = $checkedemails->error_messages[$linecnt];
            $errors .= "<br>" . $error;
            $checkedemails->error_messages[$linecnt] = $errors;
        } else {
            $checkedemails->error_messages[$linecnt] = $error;
        }

        // Email is valid.
    } else {
        // Check for moodle user with email.
        list($error, $userrecord) = block_th_manage_activatecourses_get_user($email);
        if (!empty($error)) {
            $checkedemails->emails_to_ignore[] = $email;
            if (array_key_exists($linecnt, $checkedemails->error_messages)) {
                $errors = $checkedemails->error_messages[$linecnt];
                $errors .= "<br>" . $error;
                $checkedemails->error_messages[$linecnt] = $errors;
            } else {
                $checkedemails->error_messages[$linecnt] = $error;
            }
        } else if (!empty($userrecord) && !empty($userrecord->id)) {
            $checkedemails->validemailfound += 1;

            $useralreadyenroled = false;

            if (!empty($userrecord)) {
                $useralreadyenroled = block_th_manage_activatecourses_check_user($userrecord->id, $courseid);
            }
            $checkedemails->moodleusers_for_email[$email . ',' . $courseid] = $userrecord;
            if (empty($useralreadyenroled) && $option === 0) {
                $checkedemails->user_enroled[$email . ',' . $courseid] = $userrecord;
            }
            if (!empty($useralreadyenroled) && $option === 1) {
                $checkedemails->user_enroled[$email . ',' . $courseid] = $userrecord;
            }
        }
    }
}

function block_th_manage_activatecourses_check_user($userid, $courseid) {
    global $DB;

    $check = $DB->record_exists('th_registeredcourses', array('userid' => $userid, 'courseid' => $courseid, 'timeactivated' => 0));
    if ($check) {
        return true;
    }

    $context = context_course::instance($courseid);
    $check = is_enrolled($context, $userid);
    if ($check) {
        return true;
    }

    return false;
}

function block_th_manage_activatecourses_get_user($email) {
    global $DB;

    $error = null;
    $userrecord = null;

    if (empty($email)) {
        return array($error, $userrecord);
    } else {
        // Get user records for email.
        try {
            $userrecords = $DB->get_records('user', array('email' => $email));
            $count = count($userrecords);
            if (!empty($count)) {
                // More than one user with email -> ignore email and don't enrol users later!
                if ($count > 1) {
                    $error = get_string('error_more_than_one_record_for_email', 'block_th_manage_activatecourses', $email);
                } else {
                    $userrecord = current($userrecords);
                }
            } else {
                $error = get_string('error_no_record_found_for_email', 'block_th_manage_activatecourses', $email);
            }
        } catch (Exception $e) {
            $error = get_string('error_getting_user_for_email', 'block_th_manage_activatecourses', $email) . block_th_manage_activatecourses_get_exception_info($e);
        }

        return array($error, $userrecord);
    }
}

function block_th_manage_activatecourses_get_exception_info($e) {
    if (empty($e) || !($e instanceof Exception)) {
        return '';
    }

    return " " . get_string('error_exception_info', 'block_th_manage_activatecourses') . ": " . $e->getMessage() . " -> " . $e->getTraceAsString();
}

function block_th_bulk_add_activatecourses_display_table($localbulkenroldata, $key) {
    global $OUTPUT;

    if (!empty($localbulkenroldata) && !empty($key)) {

        switch ($key) {
        case BLOCKth_bulkactivatecourse_HINT:

            $data = array();

            if (!empty($localbulkenroldata->error_messages)) {
                foreach ($localbulkenroldata->error_messages as $line => $errormessages) {
                    $row = array();

                    $cell = new html_table_cell();
                    $cell->text = $line;
                    $row[] = $cell;

                    $cell = new html_table_cell();
                    $cell->text = $errormessages;
                    $row[] = $cell;

                    $data[] = $row;
                }
            }

            $table = new html_table();
            $table->id = "BLOCKth_bulkactivatecourse_HINTs";
            $table->attributes['class'] = 'generaltable';
            $table->summary = get_string('hints', 'block_th_manage_activatecourses');
            $table->size = array('10%', '90%');
            $table->head = array();
            $table->head[] = get_string('row', 'block_th_manage_activatecourses');
            $table->head[] = get_string('hints', 'block_th_manage_activatecourses');
            $table->data = $data;

            if (!empty($data)) {
                echo $OUTPUT->heading(get_string('hints', 'block_th_manage_activatecourses'), 3);
                echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
            }

            break;

        case BLOCKth_bulkactivatecourse_ENROLUSERS:
            $data = array();

            if (!empty($localbulkenroldata->moodleusers_for_email)) {
                foreach ($localbulkenroldata->moodleusers_for_email as $email => $user) {
                    $row = array();

                    $cell = new html_table_cell();
                    $cell->text = $user->email;
                    $row[] = $cell;

                    $cell = new html_table_cell();
                    $cell->text = $user->firstname;
                    $row[] = $cell;

                    $cell = new html_table_cell();
                    $cell->text = $user->lastname;
                    $row[] = $cell;

                    $cell = new html_table_cell();
                    $cell->text = '';

                    $array = explode(',', $email);
                    $courseid = $array[1];
                    $cell->text = th_manage_activatecourses::get_fullname_course($courseid);

                    $row[] = $cell;

                    $cell = new html_table_cell();
                    $cell->text = '';
                    if (!empty($localbulkenroldata->user_enroled[$email])) {
                        $cell->text = html_writer::tag('span',
                            get_string('user_enroled_yes', 'block_th_manage_activatecourses'),
                            array('class' => 'badge badge-secondary'));
                    } else {
                        $cell->text = html_writer::tag('span',
                            get_string('user_enroled_already', 'block_th_manage_activatecourses'),
                            array('class' => 'badge badge-secondary'));
                    }
                    $row[] = $cell;

                    $data[] = $row;
                }
            }

            $table = new html_table();
            $table->id = "BLOCKth_bulkactivatecourse_ENROLUSERS";
            $table->attributes['class'] = 'generaltable';
            $table->summary = get_string('users_to_enrol_in_course', 'block_th_manage_activatecourses');
            $table->size = array('20%', '17%', '17%', '20%', '26%');
            $table->head = array();
            $table->head[] = get_string('email');
            $table->head[] = get_string('firstname');
            $table->head[] = get_string('lastname');
            $table->head[] = get_string('course');
            $table->head[] = get_string('status');
            $table->data = $data;

            if (!empty($data)) {
                echo $OUTPUT->heading(get_string('users_to_enrol_in_course', 'block_th_manage_activatecourses'), 3);
                echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
            }
            break;

        default:
            break;
        }
    }
}

function block_th_bulk_add_activatecourses_users($localth_bulkenrolkey) {
    global $DB, $CFG, $SESSION;

    $time = time();

    $error = '';
    $exceptionsmsg = array();

    if (!empty($localth_bulkenrolkey)) {

        if (!empty($localth_bulkenrolkey) && !empty($SESSION->block_th_bulkenrol) &&
            array_key_exists($localth_bulkenrolkey, $SESSION->block_th_bulkenrol)) {
            $blockth_bulkenroldata = $SESSION->block_th_bulkenrol[$localth_bulkenrolkey];

            // $campaignid = $SESSION->block_th_bulkenrol_campaign[$blockth_bulkenrolkey];

            if (!empty($blockth_bulkenroldata)) {
                $error = '';

                $campaignid = 0;

                $tmpdata = explode('_', $localth_bulkenrolkey);

                if (!empty($tmpdata)) {
                    $campaignid = $tmpdata[0];
                }

                $userstoenrol = $blockth_bulkenroldata->moodleusers_for_email;

                if (!empty($userstoenrol)) {
                    try {
                        foreach ($userstoenrol as $key => $user) {
                            try {
                                $array = explode(",", $key);
                                $courseid = $array[1];
                                // Check if user is already enrolled with another enrolment method.

                                $userisenrolled = block_th_manage_activatecourses_check_user($user->id, $courseid);

                                // If the user is already enrolled, continue to avoid a second enrolment for the user.
                                if ($userisenrolled) {
                                    continue;

                                    // Otherwise.
                                } else {

                                    $dataobject = new stdClass();
                                    $dataobject->userid = $user->id;
                                    $dataobject->courseid = $courseid;
                                    $dataobject->timecreated = $time;
                                    $dataobject->timeactivated = 0;
                                    $DB->insert_record('th_registeredcourses', $dataobject);

                                    $coursefullname = $DB->get_record('course', array('id' => $courseid), 'fullname')->fullname;
                                    $userto = $DB->get_record('user', array('id' => $user->id));
                                    $userfullname = fullname($userto);
                                    $linkactive = html_writer::link($CFG->wwwroot . '/blocks/th_activatecourses/activate.php?id=' . $courseid, 'ĐÂY');

                                    $userfrom = \core_user::get_support_user();
                                    $title = get_string('title', 'block_th_manage_activatecourses');
                                    $content = get_string('body', 'block_th_manage_activatecourses', array('userfullname' => $userfullname, 'coursefullname' => $coursefullname, 'linkactive' => $linkactive));
                                    email_to_user($userto, $userfrom, $title, $content);

                                    if (!empty($campaignid)) {

                                        $dataobject = new stdClass();
                                        $dataobject->userid = $user->id;
                                        $dataobject->courseid = $courseid;
                                        $dataobject->campaignid = $campaignid;
                                        $dataobject->timecreated = $time;
                                        $dataobject->timemodified = $time;

                                        $DB->insert_record('user_campaign_course', $dataobject);
                                    }

                                }
                            } catch (Exception $e) {
                                $a = new stdClass();
                                $a->email = $user->email;

                                $msg = get_string('error_enrol_user', 'block_th_manage_activatecourses', $a) .
                                block_th_manage_activatecourses_get_exception_info($e);
                                $exceptionsmsg[] = $msg;
                            }
                        }
                    } catch (Exception $e) {
                        $msg = get_string('error_enrol_users', 'block_th_manage_activatecourses') . block_th_manage_activatecourses_get_exception_info($e);
                        $exceptionsmsg[] = $msg;
                    }
                }
            }
        }
    }

    $retval = new stdClass();
    $retval->status = '';
    $retval->text = '';

    if (!empty($error) || !empty($exceptionsmsg)) {
        $retval->status = 'error';

        if (!empty($error)) {
            $msg = get_string($error, 'block_th_manage_activatecourses');
            $retval->text = $msg;
        }

        if (!empty($exceptionsmsg)) {
            if (!empty($error)) {
                $retval->text .= '<br>';
            }
            $retval->text .= implode('<br>', $exceptionsmsg);
        }
    } else {
        $retval->status = 'success';
        $msg = get_string('enrol_users_successful', 'block_th_manage_activatecourses');
        $retval->text = $msg;
    }

    return $retval;
}

function block_th_bulk_delete_activatecourses_users($localth_bulkenrolkey) {
    global $DB, $SESSION;

    $error = '';
    $exceptionsmsg = array();

    if (!empty($localth_bulkenrolkey)) {
        if (!empty($localth_bulkenrolkey) && !empty($SESSION->block_th_bulkenrol) &&
            array_key_exists($localth_bulkenrolkey, $SESSION->block_th_bulkenrol)) {
            $blockth_bulkenroldata = $SESSION->block_th_bulkenrol[$localth_bulkenrolkey];
            // print_object($blockth_bulkenroldata);
            // exit();
            if (!empty($blockth_bulkenroldata)) {
                $error = '';

                // $campaignid = 0;

                // $tmpdata = explode('_', $localth_bulkenrolkey);
                // if (!empty($tmpdata)) {
                //     $campaignid = $tmpdata[0];
                // }

                $userstoenrol = $blockth_bulkenroldata->moodleusers_for_email;

                if (!empty($userstoenrol)) {
                    try {
                        foreach ($userstoenrol as $key => $user) {
                            try {
                                $array = explode(",", $key);
                                $courseid = $array[1];
                                // Check if user is already enrolled with another enrolment method.

                                $userisenrolled = block_th_manage_activatecourses_check_user($user->id, $courseid);

                                // If the user is already enrolled, continue to avoid a second enrolment for the user.
                                if (!$userisenrolled) {
                                    continue;

                                    // Otherwise.
                                } else {

                                    $record = array('userid' => $user->id, 'courseid' => $courseid, 'timeactivated' => 0);
                                    $DB->delete_records('th_registeredcourses', $record);
                                }
                            } catch (Exception $e) {
                                $a = new stdClass();
                                $a->email = $user->email;

                                $msg = get_string('error_enrol_user', 'block_th_manage_activatecourses', $a) .
                                block_th_manage_activatecourses_get_exception_info($e);
                                $exceptionsmsg[] = $msg;
                            }
                        }
                    } catch (Exception $e) {
                        $msg = get_string('error_enrol_users', 'block_th_manage_activatecourses') . block_th_manage_activatecourses_get_exception_info($e);
                        $exceptionsmsg[] = $msg;
                    }
                }
            }
        }
    }

    $retval = new stdClass();
    $retval->status = '';
    $retval->text = '';

    if (!empty($error) || !empty($exceptionsmsg)) {
        $retval->status = 'error';

        if (!empty($error)) {
            $msg = get_string($error, 'block_th_manage_activatecourses');
            $retval->text = $msg;
        }

        if (!empty($exceptionsmsg)) {
            if (!empty($error)) {
                $retval->text .= '<br>';
            }
            $retval->text .= implode('<br>', $exceptionsmsg);
        }
    } else {
        $retval->status = 'success';
        $msg = get_string('unenrol_users_successful', 'block_th_manage_activatecourses');
        $retval->text = $msg;
    }

    return $retval;
}

function block_th_bulk_delete_activatecourses_display_table($localbulkenroldata, $key) {
    global $OUTPUT;

    if (!empty($localbulkenroldata) && !empty($key)) {

        switch ($key) {
        case BLOCKth_bulkactivatecourse_HINT:

            $data = array();

            if (!empty($localbulkenroldata->error_messages)) {
                foreach ($localbulkenroldata->error_messages as $line => $errormessages) {
                    $row = array();

                    $cell = new html_table_cell();
                    $cell->text = $line;
                    $row[] = $cell;

                    $cell = new html_table_cell();
                    $cell->text = $errormessages;
                    $row[] = $cell;

                    $data[] = $row;
                }
            }

            $table = new html_table();
            $table->id = "BLOCKth_bulkactivatecourse_HINTs";
            $table->attributes['class'] = 'generaltable';
            $table->summary = get_string('hints', 'block_th_manage_activatecourses');
            $table->size = array('10%', '90%');
            $table->head = array();
            $table->head[] = get_string('row', 'block_th_manage_activatecourses');
            $table->head[] = get_string('hints', 'block_th_manage_activatecourses');
            $table->data = $data;

            if (!empty($data)) {
                echo $OUTPUT->heading(get_string('hints', 'block_th_manage_activatecourses'), 3);
                echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
            }

            break;

        case BLOCKth_bulkactivatecourse_ENROLUSERS:
            $data = array();

            if (!empty($localbulkenroldata->moodleusers_for_email)) {
                foreach ($localbulkenroldata->moodleusers_for_email as $email => $user) {
                    $row = array();

                    $cell = new html_table_cell();
                    $cell->text = $user->email;
                    $row[] = $cell;

                    $cell = new html_table_cell();
                    $cell->text = $user->firstname;
                    $row[] = $cell;

                    $cell = new html_table_cell();
                    $cell->text = $user->lastname;
                    $row[] = $cell;

                    $cell = new html_table_cell();
                    $cell->text = '';

                    $array = explode(',', $email);
                    $courseid = $array[1];

                    $cell->text = th_manage_activatecourses::get_fullname_course($courseid);
                    $row[] = $cell;

                    $cell = new html_table_cell();
                    $cell->text = '';
                    if (!empty($localbulkenroldata->user_enroled[$email])) {
                        $cell->text = html_writer::tag('span',
                            get_string('user_will_be_unenrolled', 'block_th_manage_activatecourses'),
                            array('class' => 'badge badge-secondary'));
                    } else {
                        $cell->text = html_writer::tag('span',
                            get_string('user_unenrolled_no', 'block_th_manage_activatecourses'),
                            array('class' => 'badge badge-secondary'));
                    }
                    $row[] = $cell;

                    $data[] = $row;
                }
            }

            $table = new html_table();
            $table->id = "BLOCKth_bulkactivatecourse_ENROLUSERS";
            $table->attributes['class'] = 'generaltable';
            $table->summary = get_string('users_to_unenrol_in_course', 'block_th_manage_activatecourses');
            $table->size = array('20%', '17%', '17%', '20%', '26%');
            $table->head = array();
            $table->head[] = get_string('email');
            $table->head[] = get_string('firstname');
            $table->head[] = get_string('lastname');
            $table->head[] = get_string('course');
            $table->head[] = get_string('status');
            $table->data = $data;

            if (!empty($data)) {
                echo $OUTPUT->heading(get_string('users_to_unenrol_in_course', 'block_th_manage_activatecourses'), 3);
                echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
            }
            break;

        default:
            break;
        }
    }
}

function email_to_user_register($user, $from, $subject, $messagetext, $messagehtml = '', $attachment = '', $attachname = '', $usetrueaddress = true, $replyto = '', $replytoname = '', $wordwrapwidth = 79) {

    global $CFG, $PAGE, $SITE;

    if (empty($user) or empty($user->id)) {
        debugging('Can not send email to null user', DEBUG_DEVELOPER);
        return false;
    }

    if (empty($user->email)) {
        debugging('Can not send email to user without email: ' . $user->id, DEBUG_DEVELOPER);
        return false;
    }

    if (!empty($user->deleted)) {
        debugging('Can not send email to deleted user: ' . $user->id, DEBUG_DEVELOPER);
        return false;
    }

    if (defined('BEHAT_SITE_RUNNING')) {
        // Fake email sending in behat.
        return true;
    }

    if (!empty($CFG->noemailever)) {
        // Hidden setting for development sites, set in config.php if needed.
        debugging('Not sending email due to $CFG->noemailever config setting', DEBUG_NORMAL);
        return true;
    }

    if (email_should_be_diverted($user->email)) {
        $subject = "[DIVERTED {$user->email}] $subject";
        $user = clone ($user);
        $user->email = $CFG->divertallemailsto;
    }

    // Skip mail to suspended users.
    if ((isset($user->auth) && $user->auth == 'nologin') or (isset($user->suspended) && $user->suspended)) {
        return true;
    }

    if (!validate_email($user->email)) {
        // We can not send emails to invalid addresses - it might create security issue or confuse the mailer.
        debugging("email_to_user: User $user->id (" . fullname($user) . ") email ($user->email) is invalid! Not sending.");
        return false;
    }

    if (over_bounce_threshold($user)) {
        debugging("email_to_user: User $user->id (" . fullname($user) . ") is over bounce threshold! Not sending.");
        return false;
    }

    // TLD .invalid  is specifically reserved for invalid domain names.
    // For More information, see {@link http://tools.ietf.org/html/rfc2606#section-2}.
    if (substr($user->email, -8) == '.invalid') {
        debugging("email_to_user: User $user->id (" . fullname($user) . ") email domain ($user->email) is invalid! Not sending.");
        return true; // This is not an error.
    }

    // If the user is a remote mnet user, parse the email text for URL to the
    // wwwroot and modify the url to direct the user's browser to login at their
    // home site (identity provider - idp) before hitting the link itself.
    if (is_mnet_remote_user($user)) {
        require_once $CFG->dirroot . '/mnet/lib.php';

        $jumpurl = mnet_get_idp_jump_url($user);
        $callback = partial('mnet_sso_apply_indirection', $jumpurl);

        $messagetext = preg_replace_callback("%($CFG->wwwroot[^[:space:]]*)%",
            $callback,
            $messagetext);
        $messagehtml = preg_replace_callback("%href=[\"'`]($CFG->wwwroot[\w_:\?=#&@/;.~-]*)[\"'`]%",
            $callback,
            $messagehtml);
    }
    $mail = get_mailer();

    if (!empty($mail->SMTPDebug)) {
        echo '<pre>' . "\n";
    }

    $temprecipients = array();
    $tempreplyto = array();

    // Make sure that we fall back onto some reasonable no-reply address.
    $noreplyaddressdefault = 'noreply@' . get_host_from_url($CFG->wwwroot);
    $noreplyaddress = empty($CFG->noreplyaddress) ? $noreplyaddressdefault : $CFG->noreplyaddress;

    if (!validate_email($noreplyaddress)) {
        debugging('email_to_user: Invalid noreply-email ' . s($noreplyaddress));
        $noreplyaddress = $noreplyaddressdefault;
    }

    // Make up an email address for handling bounces.
    if (!empty($CFG->handlebounces)) {
        $modargs = 'B' . base64_encode(pack('V', $user->id)) . substr(md5($user->email), 0, 16);
        $mail->Sender = generate_email_processing_address(0, $modargs);
    } else {
        $mail->Sender = $noreplyaddress;
    }

    // Make sure that the explicit replyto is valid, fall back to the implicit one.
    if (!empty($replyto) && !validate_email($replyto)) {
        debugging('email_to_user: Invalid replyto-email ' . s($replyto));
        $replyto = $noreplyaddress;
    }

    if (is_string($from)) {
        // So we can pass whatever we want if there is need.
        $mail->From = $noreplyaddress;
        $mail->FromName = $from;
        // Check if using the true address is true, and the email is in the list of allowed domains for sending email,
        // and that the senders email setting is either displayed to everyone, or display to only other users that are enrolled
        // in a course with the sender.
    } else if ($usetrueaddress && can_send_from_real_email_address($from, $user)) {
        if (!validate_email($from->email)) {
            debugging('email_to_user: Invalid from-email ' . s($from->email) . ' - not sending');
            // Better not to use $noreplyaddress in this case.
            return false;
        }
        $mail->From = $from->email;
        $fromdetails = new stdClass();
        $fromdetails->name = fullname($from);
        $fromdetails->url = preg_replace('#^https?://#', '', $CFG->wwwroot);
        $fromdetails->siteshortname = format_string($SITE->shortname);
        $fromstring = $fromdetails->name;
        if ($CFG->emailfromvia == EMAIL_VIA_ALWAYS) {
            $fromstring = get_string('emailvia', 'core', $fromdetails);
        }
        $mail->FromName = $fromstring;
        if (empty($replyto)) {
            $tempreplyto[] = array($from->email, fullname($from));
        }
    } else {
        $mail->From = $noreplyaddress;
        $fromdetails = new stdClass();
        $fromdetails->name = fullname($from);
        $fromdetails->url = preg_replace('#^https?://#', '', $CFG->wwwroot);
        $fromdetails->siteshortname = format_string($SITE->shortname);
        $fromstring = $fromdetails->name;
        if ($CFG->emailfromvia != EMAIL_VIA_NEVER) {
            $fromstring = get_string('emailvia', 'core', $fromdetails);
        }
        $mail->FromName = $fromstring;
        if (empty($replyto)) {
            $tempreplyto[] = array($noreplyaddress, get_string('noreplyname'));
        }
    }

    if (!empty($replyto)) {
        $tempreplyto[] = array($replyto, $replytoname);
    }

    $temprecipients[] = array($user->email, fullname($user));

    // Set word wrap.
    $mail->WordWrap = $wordwrapwidth;

    if (!empty($from->customheaders)) {
        // Add custom headers.
        if (is_array($from->customheaders)) {
            foreach ($from->customheaders as $customheader) {
                $mail->addCustomHeader($customheader);
            }
        } else {
            $mail->addCustomHeader($from->customheaders);
        }
    }

    // If the X-PHP-Originating-Script email header is on then also add an additional
    // header with details of where exactly in moodle the email was triggered from,
    // either a call to message_send() or to email_to_user().
    if (ini_get('mail.add_x_header')) {

        $stack = debug_backtrace(false);
        $origin = $stack[0];

        foreach ($stack as $depth => $call) {
            if ($call['function'] == 'message_send') {
                $origin = $call;
            }
        }

        $originheader = $CFG->wwwroot . ' => ' . gethostname() . ':'
        . str_replace($CFG->dirroot . '/', '', $origin['file']) . ':' . $origin['line'];
        $mail->addCustomHeader('X-Moodle-Originating-Script: ' . $originheader);
    }

    if (!empty($CFG->emailheaders)) {
        $headers = array_map('trim', explode("\n", $CFG->emailheaders));
        foreach ($headers as $header) {
            if (!empty($header)) {
                $mail->addCustomHeader($header);
            }
        }
    }

    if (!empty($from->priority)) {
        $mail->Priority = $from->priority;
    }

    $renderer = $PAGE->get_renderer('core');
    $context = array(
        'sitefullname' => $SITE->fullname,
        'siteshortname' => $SITE->shortname,
        'sitewwwroot' => $CFG->wwwroot,
        'subject' => $subject,
        'prefix' => $CFG->emailsubjectprefix,
        'to' => $user->email,
        'toname' => fullname($user),
        'from' => $mail->From,
        'fromname' => $mail->FromName,
    );
    if (!empty($tempreplyto[0])) {
        $context['replyto'] = $tempreplyto[0][0];
        $context['replytoname'] = $tempreplyto[0][1];
    }
    if ($user->id > 0) {
        $context['touserid'] = $user->id;
        $context['tousername'] = $user->username;
    }

    if (!empty($user->mailformat) && $user->mailformat == 1) {
        // Only process html templates if the user preferences allow html email.

        if (!$messagehtml) {
            // If no html has been given, BUT there is an html wrapping template then
            // auto convert the text to html and then wrap it.
            $messagehtml = trim(text_to_html($messagetext));
        }
        $context['body'] = $messagehtml;
        $messagehtml = $renderer->render_from_template('core/email_html', $context);
    }

    $context['body'] = html_to_text(nl2br($messagetext));
    $mail->Subject = $renderer->render_from_template('core/email_subject', $context);
    $mail->FromName = $renderer->render_from_template('core/email_fromname', $context);
    $messagetext = $renderer->render_from_template('core/email_text', $context);

    // Autogenerate a MessageID if it's missing.
    if (empty($mail->MessageID)) {
        $mail->MessageID = generate_email_messageid();
    }

    if ($messagehtml && !empty($user->mailformat) && $user->mailformat == 1) {
        // Don't ever send HTML to users who don't want it.
        $mail->isHTML(true);
        $mail->Encoding = 'quoted-printable';
        $mail->Body = $messagehtml;
        $mail->AltBody = "\n$messagetext\n";
    } else {
        $mail->IsHTML(false);
        $mail->Body = "\n$messagetext\n";
    }

    if ($attachment && $attachname) {
        if (preg_match("~\\.\\.~", $attachment)) {
            // Security check for ".." in dir path.
            $supportuser = core_user::get_support_user();
            $temprecipients[] = array($supportuser->email, fullname($supportuser, true));
            $mail->addStringAttachment('Error in attachment.  User attempted to attach a filename with a unsafe name.', 'error.txt', '8bit', 'text/plain');
        } else {
            require_once $CFG->libdir . '/filelib.php';
            $mimetype = mimeinfo('type', $attachname);

            // Before doing the comparison, make sure that the paths are correct (Windows uses slashes in the other direction).
            // The absolute (real) path is also fetched to ensure that comparisons to allowed paths are compared equally.
            $attachpath = str_replace('\\', '/', realpath($attachment));

            // Build an array of all filepaths from which attachments can be added (normalised slashes, absolute/real path).
            $allowedpaths = array_map(function (string $path): string {
                return str_replace('\\', '/', realpath($path));
            }, [
                $CFG->cachedir,
                $CFG->dataroot,
                $CFG->dirroot,
                $CFG->localcachedir,
                $CFG->tempdir,
                $CFG->localrequestdir,
            ]);

            // Set addpath to true.
            $addpath = true;

            // Check if attachment includes one of the allowed paths.
            foreach (array_filter($allowedpaths) as $allowedpath) {
                // Set addpath to false if the attachment includes one of the allowed paths.
                if (strpos($attachpath, $allowedpath) === 0) {
                    $addpath = false;
                    break;
                }
            }

            // If the attachment is a full path to a file in the multiple allowed paths, use it as is,
            // otherwise assume it is a relative path from the dataroot (for backwards compatibility reasons).
            if ($addpath == true) {
                $attachment = $CFG->dataroot . '/' . $attachment;
            }

            $mail->addAttachment($attachment, $attachname, 'base64', $mimetype);
        }
    }

    // Check if the email should be sent in an other charset then the default UTF-8.
    if ((!empty($CFG->sitemailcharset) || !empty($CFG->allowusermailcharset))) {

        // Use the defined site mail charset or eventually the one preferred by the recipient.
        $charset = $CFG->sitemailcharset;
        if (!empty($CFG->allowusermailcharset)) {
            if ($useremailcharset = get_user_preferences('mailcharset', '0', $user->id)) {
                $charset = $useremailcharset;
            }
        }

        // Convert all the necessary strings if the charset is supported.
        $charsets = get_list_of_charsets();
        unset($charsets['UTF-8']);
        if (in_array($charset, $charsets)) {
            $mail->CharSet = $charset;
            $mail->FromName = core_text::convert($mail->FromName, 'utf-8', strtolower($charset));
            $mail->Subject = core_text::convert($mail->Subject, 'utf-8', strtolower($charset));
            $mail->Body = core_text::convert($mail->Body, 'utf-8', strtolower($charset));
            $mail->AltBody = core_text::convert($mail->AltBody, 'utf-8', strtolower($charset));

            foreach ($temprecipients as $key => $values) {
                $temprecipients[$key][1] = core_text::convert($values[1], 'utf-8', strtolower($charset));
            }
            foreach ($tempreplyto as $key => $values) {
                $tempreplyto[$key][1] = core_text::convert($values[1], 'utf-8', strtolower($charset));
            }
        }
    }

    foreach ($temprecipients as $values) {
        $mail->addAddress($values[0], $values[1]);
    }
    foreach ($tempreplyto as $values) {
        $mail->addReplyTo($values[0], $values[1]);
    }

    if (!empty($CFG->emaildkimselector)) {
        $domain = substr(strrchr($mail->From, "@"), 1);
        $pempath = "{$CFG->dataroot}/dkim/{$domain}/{$CFG->emaildkimselector}.private";
        if (file_exists($pempath)) {
            $mail->DKIM_domain = $domain;
            $mail->DKIM_private = $pempath;
            $mail->DKIM_selector = $CFG->emaildkimselector;
            $mail->DKIM_identity = $mail->From;
        } else {
            debugging("Email DKIM selector chosen due to {$mail->From} but no certificate found at $pempath", DEBUG_DEVELOPER);
        }
    }

    if ($mail->send()) {
        set_send_count($user);
        if (!empty($mail->SMTPDebug)) {
            echo '</pre>';
        }
        return true;
    } else {
        // Trigger event for failing to send email.
        $event = \core\event\email_failed::create(array(
            'context' => context_system::instance(),
            'userid' => $from->id,
            'relateduserid' => $user->id,
            'other' => array(
                'subject' => $subject,
                'message' => $messagetext,
                'errorinfo' => $mail->ErrorInfo,
            ),
        ));
        $event->trigger();
        if (CLI_SCRIPT) {
            mtrace('Error: lib/moodlelib.php email_to_user(): ' . $mail->ErrorInfo);
        }
        if (!empty($mail->SMTPDebug)) {
            echo '</pre>';
        }
        return false;
    }
}