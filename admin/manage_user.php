<?php
ini_set('display_errors', 'On');

require_once(__DIR__ . "/../config/admin/config.php");
require_once(__DIR__ . "/../db/src/User.php");
require_once(__DIR__ . "/../db/UserManager.php");

// Set up doctrine objects
$emf = new EntityManagerFactory();
$em = $emf->getEntityManager();
$um = new UserManager($em);

// Setup the template engine
require($_SERVER['DOCUMENT_ROOT'] . '/config/admin/mustache.php');
$tpl = $mustache->loadTemplate('manage_user');

// If the user is logged in, proceed.  Otherwise, show the login screen.
if( array_key_exists('logged_in',$_SESSION) && $_SESSION['logged_in'] == "true") {
  $data = handleFormInput($um);
  $data['users'] = loadUsers($um);
  $data['user_info'] = true;
  $data['page_title'] = 'Manage Users';
} else {
  $data['page_title'] = 'Log In';
}
$data['title'] = 'Project Phoenix - Admin';

// Pass the data to the template engine and render it
echo $tpl->render($data);


// Build a list of users for display
function loadUsers($um) {

  // Load all the administrators from the Database
  // TODO: Modify this to have a windowed view of the dataset
  $loadedUsers = $um->loadAll();

  // Simplify the data structure for the templating engine
  $i = 0;
  foreach($loadedUsers as $displayUser) {
    $users[$i] = array(
      'id' => $displayUser->getId(),
      'firstName' => $displayUser->getFirstName(),
      'lastName' => $displayUser->getLastName(),
      'email' => $displayUser->getEmail(),
      'created' => $displayUser->getCreated()
    );
    $i = $i + 1;
  }

  if (isset($users)) {
    return $users;
  }
  return null;
}

// Handle any actions in the user workflow
function handleFormInput($um) {

  // Process any form input
  if(isset($_POST['action'])) {
    switch($_POST['action']) {
      case "add":
        $user = new User();
        $user->setEmail($_POST['email']);
        $user->setPassword($_POST['password']);
        $user->setFirstName($_POST['firstName']);
        $user->setLastName($_POST['lastName']);
        try {
          // Store the user object in the database
          $um->store($user);
          // If the ORM throws an exception, handle it and show an error
        } catch (Exception $e) {
          $data['error'] = "An error has occurred.  The operation could not be completed";
          break;
        }
        $data['user']['firstName'] = $user->getFirstName();
        $data['user']['lastName'] = $user->getLastName();
        $data['user']['id'] = $user->getId();
        $data['user']['email'] = $user->getEmail();
        $data['user']['created'] = $user->getCreated()->format('m-d-Y H:i:s');
        $data['added'] = true;
      break;
      case "update":
        try {
          // Load the provided user from the database
          $user = $um->load($_POST['id']);
        } catch (Exception $e) {
          $data['error'] = "An error has occurred.  The object could not be retrieved.";
          break;
        }
        // Populate the data element for the template engine
        $data['user']['firstName'] = $user->getFirstName();
        $data['user']['lastName'] = $user->getLastName();
        $data['user']['id'] = $user->getId();
        $data['user']['email'] = $user->getEmail();
        $data['user']['created'] = $user->getCreated()->format('m-d-Y H:i:s');
      break;
      case "doUpdate":
        $user = $um->load($_POST['id']);
        $user->setFirstName($_POST['firstName']);
        $user->setLastName($_POST['lastName']);
        $user->setEmail($_POST['email']);
        echo("Setting Password: " . $_POST['password']);
        $user->setPassword($_POST['password']);
        $um->store($user);

        $data['user']['firstName'] = $user->getFirstName();
        $data['user']['lastName'] = $user->getLastName();
        $data['user']['id'] = $user->getId();
        $data['user']['email'] = $user->getEmail();
        $data['user']['created'] = $user->getCreated()->format('m-d-Y H:i:s');

        $data['updated'] = true;
      break;
      case "delete":
        try {
          $user = $um->load($_POST['id']);
        } catch (Exception $e) {
          $data['error'] = "An error has occurred.  The object could not be retrieved.";
          break;
        }
        try {
          $um->delete($user);
        } catch (Exception $e) {
          $data['error'] = "Operation failed. The specified user could not be deleted.";
          break;
        }
        $data['deleted'] = true;
    }
    if(isset($data)) {
      return $data;
    }
  }
  return null;
}
?>
