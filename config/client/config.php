<?php

// Include common settings
require_once(__DIR__ . "/../config.php");

// Just bundle up all the common required libraries to bootstrap the admin UI pages
// so that I'm not adding a bunch of requires in each of the individual pages

// Composer dependency auto-loader
require_once($GLOBALS['DOCUMENT_ROOT'] . "/vendor/autoload.php");

// Setup the database abstraction library (ORM)
require_once("doctrine.php");

// If we're running PHPUnit, including these is problematic
if ($_SERVER['DOCUMENT_ROOT'] != NULL) {
  // Setup the templating engine
  require_once("mustache.php");

  // Setup session management
  require_once("sessions.php");
}
?>
