<?php
$services = array(
	'block_th_course_unenrollment_report' => array( // the name of the web service
		'functions' => array('block_th_course_unenrollment_report_load_users'), // web service functions of this service
		'requiredcapability' => '', // if set, the web service user need this capability to access
		// any function of this service. For example: 'some/capability:specified'
		'restrictedusers' => 0, // if enabled, the Moodle administrator must link some user to this service
		// into the administration
		'enabled' => 1, // if enabled, the service can be reachable on a default installation
		'shortname' => 'block_th_course_unenrollment_report', // optional – but needed if restrictedusers is set so as to allow logins.
	),
);
$functions = array(
	'block_th_course_unenrollment_report_load_users' => array( //web service function name
		'classname' => 'block_th_course_unenrollment_report_external', //class containing the external function OR namespaced class in classes/external/XXXX.php
		'methodname' => 'load_users', //external function name
		'classpath' => 'blocks/th_course_unenrollment_report/classes/external.php', //file containing the class/external function - not required if using namespaced auto-loading classes.
		// defaults to the service's externalib.php
		'description' => 'fetch user id.', //human readable description of the web service function
		'type' => 'write', //database rights of the web service function (read, write)
		'ajax' => true, // is the service available to 'internal' ajax calls.
		'loginrequired' => true,
		'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE), // Optional, only available for Moodle 3.1 onwards. List of built-in services (by shortname) where the function will be included.  Services created manually via the Moodle interface are not supported.
		'capabilities' => '', // comma separated list of capabilities used by the function.
	),
);
?>
