<?php
$capabilities = array(

    'local/th_notification_forum:seeallthings' => array(
        'riskbitmask'  => RISK_SPAM | RISK_XSS,
        'captype'      => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes'   => array(
            'manager' => CAP_ALLOW,
        ),
    ),
);