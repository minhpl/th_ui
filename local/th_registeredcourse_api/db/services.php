<?php

$services = array(
    'th_registeredcourse_api' => array( //the name of the web service
        'functions' => array('local_th_enrolcourse', 'local_th_unenrolcourse'), //web service functions of this service
        'requiredcapability' => 0, //if set, the web service user need this capability to access
        //any function of this service. For example: 'some/capability:specified'
        'restrictedusers' => 0, //if enabled, the Moodle administrator must link some user to this service
        //into the administration
        'enabled' => 1, //if enabled, the service can be reachable on a default installation
        'shortname' => 'th_registeredcourse_api', //the short name used to refer to this service from elsewhere including when fetching a token
    ),
);

$functions = array(
    'local_th_enrolcourse' => array(
        'classname' => 'local_th_registeredcourse_api_external',
        'methodname' => 'enrolcourse',
        'classpath' => 'local/th_registeredcourse_api/externallib.php',
        'description' => 'api enrol course',
        'type' => 'write',
    ),
    'local_th_unenrolcourse' => array(
        'classname' => 'local_th_registeredcourse_api_external',
        'methodname' => 'unenrolcourse',
        'classpath' => 'local/th_registeredcourse_api/externallib.php',
        'description' => 'api unenrol course',
        'type' => 'write',
    ),
);