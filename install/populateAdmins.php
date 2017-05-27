<?php

// Populates a list of Award Types
//
// NOTE: This is a destructive operation.
// It will screw up any associated indices.
// Only run this if you're okay blowing away the entire existing database.

ini_set('display_errors', 'On');

require_once(__DIR__ . "/../config/admin/config.php");
require_once(__DIR__ . "/../config/admin/doctrine.php");
require_once(__DIR__ . "/../db/src/Admin.php");
require_once(__DIR__ . "/../db/AdminManager.php");

// Set up doctrine objects
$emf = new EntityManagerFactory();
$em = $emf->getEntityManager();
$am = new AdminManager($em);

// Default Admin Credentials
$default_username = "admin@jeromie.com";
$default_password = "password";

$admin = new Admin();
$admin->setEmail($default_username);
$admin->setPassword($default_password);
$am->store($admin);

?>
