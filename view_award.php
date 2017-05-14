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
$tpl = $mustache->loadTemplate('view_award');
$data = array();  // Output for display by the template engine

// If the user is logged in, proceed.  Otherwise, show the login screen.
if( array_key_exists('logged_in',$_SESSION) && $_SESSION['logged_in'] == "true") {
  $data['awards'] = loadAllAwards($am, $um);
  $data['user_info'] = true;
  $data['page_title'] = 'View Award';
  $data['awardType'] = array(array('id'=>"1", 'label'=>"Generic Award"));

  if(isset($_GET['view'])) {
    $data['award'] = loadAward($am, $_GET['view']);
  }
} else {
  $data['page_title'] = 'Log In';
}
$data['title'] = 'Project Phoenix - Employee Recognition System';

// Pass the resulting data into the template
echo $tpl->render($data);

/**
* @param AwardManager $am
* @param int Award ID
* @return Array data for display
*/

function loadAward($am, $id) {

  $award = $am->load($id);

  $data['id'] = $award->getId();
  $data['recipientEmail'] = $award->getRecipientEmail();
  $data['recipientFirst'] = $award->getRecipientFirst();
  $data['recipientLast'] = $award->getRecipientLast();
  $data['granterId'] = $award->getGranter()->getId();

  $grantDate = $award->getGrantDate()->format('m/d/Y');
  $data['grantDate'] = $grantDate;

  $data['granter'] = $award->getGranter()->getFirstName() . " "
    . $award->getGranter()->getLastName();

  $data['certURL'] = $award->getCertURL();

  return $data;
}

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
?>
