<?php

// The base directory of the application on the server
// I try to keep it all relative with __DIR__ . <relative_path>, but it's not always doable/readable

// Server timezone
date_default_timezone_set('America/Los_Angeles');

// Path to the document root
$GLOBALS['DOCUMENT_ROOT'] = "/Users/jeclark/OSU/phoenix";

// A password reset has is only valid for one hour
$GLOBALS['password_reset_timeout'] = 3600;

// The hostname of our application
$GLOBALS['hostname'] = "localhost:8888";

// Path to where certificates get stored
$GLOBALS['CERT_PATH'] = "/static/cert/";

// Path to where signatures get stored
$GLOBALS['SIG_PATH'] = "/static/sig/";
?>
