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
if($user->getSignaturePath()) {
  $data['signaturePath'] = $user->getSignaturePath();
}

// Process any form input
if(isset($_POST['action'])) {
  switch($_POST['action']) {
    case "update":

      // If there's a file present, handle it
      if (!empty($_FILES)) {
        if(!$user->setSignature($_FILES['signatureFile']['tmp_name'])) {
          $data['error'] = "An error has occurred.  The signature file could not be saved.";
        }
      }
      $user->setFirstName($_POST['firstName']);
      $user->setLastName($_POST['lastName']);
      $user->setEmail($_POST['email']);

      $um->store($user);
      if($user->getSignaturePath()) {

        $basePath = "http";
        if(!empty($_SERVER['HTTPS'])) {
          $basePath .= "s";
        }
        $basePath .= "://" . $GLOBALS['STATIC_HOST'];


        $data['signaturePath'] = $basePath . $user->getSignaturePath();
      }
      // Show the successful update message in the UI
      $data['updated'] = true;
    break;
  }
}

// Pass the resulting data into the template
echo $tpl->render($data);
?>
