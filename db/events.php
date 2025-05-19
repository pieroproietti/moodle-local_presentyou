<?php
defined('MOODLE_INTERNAL') || die();

$observers = array(
    array(
        'eventname' => '\core\event\user_loggedin',
        'callback' => 'local_presentyou\observer::user_loggedin',
        'internal' => false,
        'priority' => 1000,
    ),
    array(
        'eventname' => '\core\event\before_http_headers',
        'callback' => 'local_presentyou\observer::before_http_headers',
        'internal' => false,
    ),
);
