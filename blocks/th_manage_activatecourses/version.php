<?php
$plugin->component = 'block_th_manage_activatecourses'; // Recommended since 2.0.2 (MDL-26035). Required since 3.0 (MDL-48494)
$plugin->version = 2022091301; // YYYYMMDDHH (year, month, day, 24-hr time)
$plugin->requires = 2010031008; // YYYYMMDDHH (This is the release version for Moodle 2.0)
$plugin->dependencies = array(
	'local_thlib' => '2021100000',
	'block_th_vmc_campaign' => '2021100000',
);
