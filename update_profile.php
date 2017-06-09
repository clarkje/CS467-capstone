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
  $data = handleFormInput($um);
  $data['user_info'] = true;
  $data['page_title'] = 'Update Profile';
} else {
  $data['page_title'] = 'Log In';
}
$data['title'] = 'Project Phoenix - Employee Recognition System';



// Pass the resulting data into the template
echo $tpl->render($data);

function handleFormInput($um) {

  $data = array();

  // Load user data from the session
  try {
    $user = $um->load($_SESSION['id']);
  } catch (Exception $e) {
    $data['error'] = "An error has occurred.  The object could not be retrieved.";
  }

  $data['id'] = $_SESSION['id'];
  $data['email'] = $_SESSION['email'];
  $data['firstName'] = $user->getFirstName();
  $data['lastName'] = $user->getLastName();

  if($user->getSignatureURL()) {
    $data['signatureURL'] = $user->getSignatureURL();
  }

  // Process any form input
  if(isset($_POST['action'])) {
    switch($_POST['action']) {
      case "update":

        // If there's a file present, handle it
        if (!empty($_FILES) && $_FILES['signatureFile']['size'] > 128) {
          if(!$user->setSignature($_FILES['signatureFile']['tmp_name'])) {
            $data['error'] = "An error has occurred.  The signature file could not be saved.";
          }
        }
        $user->setFirstName($_POST['firstName']);
        $user->setLastName($_POST['lastName']);
        $user->setEmail($_POST['email']);

        if(isset($_POST['password']) && $_POST['password'] != null) {
          $admin->setPassword($_POST['password']);
        }

        $um->store($user);
        if($user->getSignatureURL()) {
          $data['signatureURL'] = $user->getSignatureURL();
        }
        // Show the successful update message in the UI
        $data['updated'] = true;
      break;
    }
  }

  return $data;
}


?>
