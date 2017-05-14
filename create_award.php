<?php
ini_set('display_errors', 'On');

require_once(__DIR__ . "/config/client/config.php");
require_once(__DIR__ . "/db/src/Award.php");
require_once(__DIR__ . "/db/AwardManager.php");
require_once(__DIR__ . "/db/src/User.php");
require_once(__DIR__ . "/db/UserManager.php");

// Set up doctrine objects
$emf = new EntityManagerFactory();
$em = $emf->getEntityManager();
$um = new UserManager($em);
$am = new AwardManager($em);

// Setup the template engine
require($_SERVER['DOCUMENT_ROOT'] . '/config/client/mustache.php');
$tpl = $mustache->loadTemplate('create_award');
$data = array();  // Output for display by the template engine

// If the user is logged in, proceed.  Otherwise, show the login screen.
if( array_key_exists('logged_in',$_SESSION) && $_SESSION['logged_in'] == "true") {
  $data['award'] = handleFormInput($am, $um);
  $data['user'] = loadUserData($um);
  $data['awards'] = loadAllAwards($am, $um);
  $data['user_info'] = true;
  $data['page_title'] = 'View Award';
  $data['awardType'] = array(array('id'=>"1", 'label'=>"Generic Award"));
} else {
  $data['page_title'] = 'Log In';
}
$data['title'] = 'Project Phoenix - Employee Recognition System';

// Pass the resulting data into the template
echo $tpl->render($data);

/**
* @param AwardManager $am
* @param UserManager $um
* @return Array data for display
*/
function loadAllAwards($am, $um) {
  $loadedAwards = $am->loadAll();
  if(empty($loadedAwards)) {
    return null;
  }

  foreach ($loadedAwards as $displayAward) {

    if(empty($displayAward->getGrantDate())) {
      $grantDate = null;
    } else {
      $grantDate = $displayAward->getGrantDate()->format('m/d/Y');
    }

    $awards[] = array(
      'id' => $displayAward->getId(),
      'recipientEmail' => $displayAward->getRecipientEmail(),
      'recipientFirst' => $displayAward->getRecipientFirst(),
      'recipientLast' => $displayAward->getRecipientLast(),
      'grantDate' => $grantDate,
      'awardType' => $displayAward->getAwardType(),
      'granter' => $displayAward->getGranter()->getFirstName() . " "
        . $displayAward->getGranter()->getLastName()
    );
  }
  return $awards;
}

/**
* @param UserManager $um
* @return Array data for display
*/
function loadUserData($um) {

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
  return $data;
}


/**
* @param AwardManager $am
* @param UserManager $um
* @return Array data for display
**/
function handleFormInput($am, $um) {

  if(isset($_POST['action'])) {
    switch($_POST['action']) {
      case "add":

        // Doctrine wants the actual user object and not the ID
        $user = $um->load($_POST['granterId']);

        $award = new Award();
        $award->setRecipientFirst($_POST['recipientFirst']);
        $award->setRecipientLast($_POST['recipientLast']);
        $award->setRecipientEmail($_POST['recipientEmail']);
        $award->setGranter($user);
        $award->setGrantDate(new DateTime($_POST['grantDate']));

        try {
          $am->store($award);
        } catch (Exception $e) {
          $data['error'] = "An error has occurred.  The operation could not be completed.";
          break;
        }

        $data['added'] = true;
        $data['id'] = $award->getId();
        $data['recipientEmail'] = $award->getRecipientEmail();
        $data['recipientFirst'] = $award->getRecipientFirst();
        $data['recipientLast'] = $award->getRecipientLast();

        $grantDate = $award->getGrantDate()->format('m/d/Y');
        $data['grantDate'] = $grantDate;
        return $data;
      break;
      case "update":
        $award = $am->load($_POST['id']);

        $data['id'] = $award->getId();
        $data['recipientEmail'] = $award->getRecipientEmail();
        $data['recipientFirst'] = $award->getRecipientFirst();
        $data['recipientLast'] = $award->getRecipientLast();
        $data['granterId'] = $award->getGranter()->getId();

        $grantDate = $award->getGrantDate()->format('m/d/Y');
        $data['grantDate'] = $grantDate;
        return $data;
      break;
      case "doUpdate":
        $award = $am->load($_POST['id']);
        $award->setRecipientFirst($_POST['recipientFirst']);
        $award->setRecipientLast($_POST['recipientLast']);
        $award->setRecipientEmail($_POST['recipientEmail']);
        $award->setAwardType($_POST['awardType']);
        if($_POST['grantDate']) {
          $grantDate = DateTime::createFromFormat('m/d/Y', $_POST['grantDate']);
          $award->setGrantDate($grantDate);
        }
        $am->store($award);

        $data['id'] = $award->getId();
        $data['recipientEmail'] = $award->getRecipientEmail();
        $data['recipientFirst'] = $award->getRecipientFirst();
        $data['recipientLast'] = $award->getRecipientLast();
        $data['granterId'] = $award->getGranter()->getId();
        $grantDate = $award->getGrantDate()->format('m/d/Y');
        $data['grantDate'] = $grantDate;

        return $data;
      break;
      case "delete":
        $admin = $am->load($_POST['id']);
        $am->delete($admin);
        $data['deleted'] = true;
        return $data;
      break;
    }
  }
}
?>
