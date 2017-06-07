<?php

// The base directory of the application on the server
// I try to keep it all relative with __DIR__ . <relative_path>, but it's not always doable/readable

// Server timezone
date_default_timezone_set('America/Los_Angeles');

// Path to the document root
$GLOBALS['DOCUMENT_ROOT'] = "/Users/jeclark/OSU/phoenix";

// The hostname of our application
$GLOBALS['HOST_NAME'] = "localhost:8888";

// Hostname for the user content, like signatures and certificates
// Keeping it on a separate host is one defense against code injection
$GLOBALS['STATIC_HOST'] = "localhost:8888";

// Path to the static content root
$GLOBALS['STATIC_ROOT'] = "/Users/jeclark/OSU/phoenix";

// A password reset has is only valid for one hour
$GLOBALS['PASSWORD_RESET_TIMEOUT'] = 3600;

// Path to where certificates get stored
$GLOBALS['CERT_PATH'] = "/static/cert/";

// Path to where signatures get stored
$GLOBALS['SIG_PATH'] = "/static/sig/";

// Path to PDFLatex
$GLOBALS['PDFLATEX_PATH'] = "/home/phoenixssh/texlive/bin/x86_64-linux/pdflatex";

?>
