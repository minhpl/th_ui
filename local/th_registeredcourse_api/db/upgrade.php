<?php

defined('MOODLE_INTERNAL') || die;

function xmldb_local_th_registeredcourse_api_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();
    $table = new xmldb_table('local_registeredcourse_api');

    // $field = new xmldb_field('code', XMLDB_TYPE_CHAR, '20');
    //if (!$dbman->field_exists($table, $field)) {
    //    $dbman->add_field($table, $field);
    //}
    $field = new xmldb_field('code', XMLDB_TYPE_CHAR, '20');
    if ($dbman->field_exists($table, $field)) {
        $table->deleteField('code');
    }
    $field = new xmldb_field('campaignid', XMLDB_TYPE_INTEGER, '10');
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
    $field = new xmldb_field('courseprice', XMLDB_TYPE_TEXT);
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
    $field = new xmldb_field('orderid', XMLDB_TYPE_INTEGER, '10');
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }

    $field = new xmldb_field('status', XMLDB_TYPE_INTEGER, '1', null, null, null, 0, null);
    if ($dbman->field_exists($table, $field)) {
        $dbman->change_field_default($table, $field);
        $dbman->change_field_type($table, $field);
    }

    $field = new xmldb_field('userid', XMLDB_TYPE_TEXT);
    if ($dbman->field_exists($table, $field)) {
        $dbman->change_field_type($table, $field);
    }
    $field = new xmldb_field('fullname', XMLDB_TYPE_TEXT);
    if ($dbman->field_exists($table, $field)) {
        $dbman->change_field_type($table, $field);
    }
    $field = new xmldb_field('phone', XMLDB_TYPE_TEXT);
    if ($dbman->field_exists($table, $field)) {
        $dbman->change_field_type($table, $field);
    }
    $field = new xmldb_field('email', XMLDB_TYPE_TEXT);
    if ($dbman->field_exists($table, $field)) {
        $dbman->change_field_type($table, $field);
    }
    $field = new xmldb_field('courseid', XMLDB_TYPE_TEXT);
    if ($dbman->field_exists($table, $field)) {
        $dbman->change_field_type($table, $field);
    }

    if (!$dbman->table_exists('th_order')) {

        $table = new xmldb_table('th_order');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('ordercode', XMLDB_TYPE_TEXT);
        $table->add_field('ordername', XMLDB_TYPE_TEXT);
        $table->add_field('description', XMLDB_TYPE_TEXT);
        $table->add_field('totalprice', XMLDB_TYPE_TEXT);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);
    }
    if (!$dbman->table_exists('th_order_status')) {

        $table = new xmldb_table('th_order_status');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('orderid', XMLDB_TYPE_INTEGER, '10');
        $table->add_field('status', XMLDB_TYPE_TEXT);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);
    }

    return true;
}
