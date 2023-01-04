<?php

defined('MOODLE_INTERNAL') || die;

function xmldb_local_th_dashboard_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();
    if (!$dbman->table_exists('local_th_dashboard')) {

        $table = new xmldb_table('local_th_dashboard');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('content', XMLDB_TYPE_TEXT);
        $table->add_field('userid', XMLDB_TYPE_INTEGER,  '10', null, null, null, 0);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);
    }

    return true;
}