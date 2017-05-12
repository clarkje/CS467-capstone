<?php
ini_set('display_errors', 'On');

require_once(__DIR__ . "/config/client/config.php");
require_once(__DIR__ . "/db/src/User.php");
require_once(__DIR__ . "/db/UserManager.php");


// Set up doctrine objects
$emf = new EntityManagerFactory();
$em = $emf->getEntityManager();
$um = new UserManager($em);

// Setup the template engine
require($_SERVER['DOCUMENT_ROOT'] . '/config/client/mustache.php');
$tpl = $mustache->loadTemplate('client_profile');

// If the user is logged in, proceed.  Otherwise, show the login screen.
if( array_key_exists('logged_in',$_SESSION) && $_SESSION['logged_in'] == "true") {
  $data['user_info'] = true;
  $data['page_title'] = 'Update Profile';
} else {
  $data['page_title'] = 'Log In';
}
$data['title'] = 'Project Phoenix - Employee Recognition System';
$data['id'] = $_SESSION['id'];
$data['email'] = $_SESSION['email'];

// Process any form input
if(isset($_POST['action'])) {
  switch($_POST['action']) {
    case "update":
      try {
        // Load the provided user from the database
        $user = $um->load($_SESSION['id']);
      } catch (Exception $e) {
        $data['error'] = "An error has occurred.  The object could not be retrieved.";
        break;
      }

      $user->setFirstName($_POST['firstName']);
      $user->setLastName($_POST['lastName']);
      $user->setEmail($_POST['email']);

      // Show the successful update message in the UI
      $data['updated'] = true;
    break;
    default:
      try {
        // Load the provided user from the database
        $user = $um->load($_SESSION['id']);
      } catch (Exception $e) {
        $data['error'] = "An error has occurred.  The object could not be retrieved.";
        break;
      }
      $data['firstName'] = $user->getFirstName();
      $data['lastName'] = $user->getLastName();
      $data['email'] = $user->getEmail();
      break;
    }
}

// Pass the resulting data into the template
echo $tpl->render($data);
?>
