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
$tpl = $mustache->loadTemplate('manage_admin');

// If the user is logged in, proceed.  Otherwise, show the login screen.
if( array_key_exists('logged_in',$_SESSION) && $_SESSION['logged_in'] == "true") {
  $data = handleFormInput($am);
  $data['admins'] = loadAdmins($am);
  $data['user_info'] = true;
  $data['page_title'] = 'Manage Administrators';
} else {
  $data['page_title'] = 'Log In';
}
$data['title'] = 'Project Phoenix - Admin';

// Pass the resulting data into the template
echo $tpl->render($data);


/**
* @return Array data for display
*
* Handle any actions from the admin workflow
*/
function handleFormInput($am) {
  if(isset($_POST['action'])) {
    switch($_POST['action']) {
      case "add":
        $admin = new Admin();
        $admin->setEmail($_POST['email']);
        $admin->setPassword($_POST['password']);
        try {
          // Store the admin object in the database
          $am->store($admin);
          // If the ORM throws an exception, handle it and show an error
        } catch (Exception $e) {
          $data['error'] = "An error has occurred.  The operation could not be completed";
          break;
        }
        $data['admin']['id'] = $admin->getId();
        $data['admin']['email'] = $admin->getEmail();
        $data['admin']['created'] = $admin->getCreated()->format('m-d-Y H:i:s');
      break;
      case "update":

      //TODO: Update doesn't actually save anything
        try {
          // Load the provided admin from the database
          $admin = $am->load($_POST['id']);
        } catch (Exception $e) {
          $data['error'] = "An error has occurred.  The object could not be retrieved.";
          break;
        }
        // Populate the data element for the template engine
        $data['admin']['id'] = $admin->getId();
        $data['admin']['email'] = $admin->getEmail();
        $data['admin']['created'] = $admin->getCreated()->format('m-d-Y h:i:s A');
      break;
    }
    if(isset($data)) {
      return $data;
    }
  }
  return null;
}

/**
* @param AdminManager $am
* @return Array of data for display
*
* Load all administrators for display in the UI
*/
function loadAdmins($am) {

  // Load all the administrators from the Database
  // TODO: Modify this to have a windowed view of the dataset
  $loadedAdmins = $am->loadAll();

  // Simplify the data structure for the templating engine
  $i = 0;
  foreach($loadedAdmins as $displayAdmin) {
    $admins[$i] = array(
      'id' => $displayAdmin->getId(),
      'email' => $displayAdmin->getEmail(),
      'created' => $displayAdmin->getCreated()
    );
    $i = $i + 1;
  }

  if(isset($admins)) {
    return $admins;
  }
  return null;
}
?>
