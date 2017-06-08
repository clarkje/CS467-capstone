<?php
ini_set('display_errors', 'On');

require_once(__DIR__ . "/../config/admin/config.php");
require_once(__DIR__ . "/../db/src/Admin.php");
require_once(__DIR__ . "/../db/AdminManager.php");


// Set up doctrine objects
$emf = new EntityManagerFactory();
$em = $emf->getEntityManager();
$am = new AdminManager($em);

// Setup the template engine
require($_SERVER['DOCUMENT_ROOT'] . '/config/admin/mustache.php');
$tpl = $mustache->loadTemplate('admin_profile');

// If the user is logged in, proceed.  Otherwise, show the login screen.
if( array_key_exists('logged_in',$_SESSION) && $_SESSION['logged_in'] == "true") {
  $data = handleFormInput($am);
  $data['user_info'] = true;
  $data['page_title'] = 'Update Profile';
} else {
  $data['page_title'] = 'Log In';
}
$data['title'] = 'Project Phoenix - Admin';

if(isset($_SESSION['id'])) {
  $data['admin']['id'] = $_SESSION['id'];
}

if(isset($_SESSION['email'])) {
  $data['admin']['email'] = $_SESSION['email'];
}

// Pass the resulting data into the template
echo $tpl->render($data);

function handleFormInput($am) {

  $data = array();

  // Process any form input
  if(isset($_POST['action'])) {
    switch($_POST['action']) {
      case "update":
        try {
          // Load the provided admin from the database
          $admin = $am->load($_POST['id']);
        } catch (Exception $e) {
          $data['error'] = "An error has occurred.  The object could not be retrieved.";
          break;
        }

        if(isset($_POST['email']) && $_POST['email'] != null) {
          $admin->setEmail($_POST['email']);
        }

        if(isset($_POST['password']) && $_POST['password'] != null) {
          $admin->setPassword($_POST['password']);
        }

        $am->store($admin);

        // Populate the data element for the template engine
        $data['admin']['id'] = $admin->getId();
        $data['admin']['email'] = $admin->getEmail();
        // Show the successful update message in the UI
        $data['updated'] = true;
      break;
      case "delete":
        $admin = $am->load($_POST['id']);
        $am->delete($admin);
      break;
    }
  }
  return $data;
}

?>
