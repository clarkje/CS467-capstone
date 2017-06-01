<?php
ini_set('display_errors', 'On');

require_once(__DIR__ . "/config/client/config.php");
require_once(__DIR__ . "/db/AddressManager.php");
require_once(__DIR__ . "/db/AwardManager.php");
require_once(__DIR__ . "/db/CountryManager.php");
require_once(__DIR__ . "/db/UserManager.php");
require_once(__DIR__ . "/lib/CertGenerator.php");

// Set up doctrine objects
$emf = new EntityManagerFactory();
$entityManager = $emf->getEntityManager();
$userManager = new UserManager($entityManager);
$awardManager = new AwardManager($entityManager);
$addressManager = new AddressManager($entityManager);
$countryManager = new CountryManager($entityManager);

// Setup the template engine
require($_SERVER['DOCUMENT_ROOT'] . '/config/client/mustache.php');
$tpl = $mustache->loadTemplate('create_award');
$data = array();  // Output for display by the template engine

// Pagination parameters
$orderBy = null;
$offset = null;
$itemsPerPage = 15;

if (isset($_GET['orderBy'])) {
  $orderBy = $_GET['orderBy'];
}
if (isset($_GET['offset'])) {
  $offset = $_GET['offset'];
}

// If the user is logged in, proceed.  Otherwise, show the login screen.
if( array_key_exists('logged_in',$_SESSION) && $_SESSION['logged_in'] == "true") {
  $data['award'] = handleFormInput($awardManager, $userManager, $addressManager, $countryManager);
  $data['addressOptions'] = $data['award']['addressOptions'];
  $data['awards'] = loadAllAwards($awardManager, $userManager, $orderBy, $itemsPerPage, $offset);
  $data['user'] = loadUserData($userManager);
  $data['user_info'] = true;
  $data['page_title'] = 'Create Award';
  $data['awardType'] = array(array('id'=>"1", 'label'=>"Generic Award"));
} else {
  $data['page_title'] = 'Log In';
}
$data['title'] = 'Project Phoenix - Employee Recognition System';
$data['pagination'] = createPagination($awardManager->getAwardCount(), $offset, $itemsPerPage);

// Pass the resulting data into the template
echo $tpl->render($data);

/**
* @param AwardManager $awardManager
* @param UserManager $userManager
* @param String $orderBy default = null
* @param int $limit - number of records to return
* @param int $offset - offset of record window
* @return Array data for display
*/
function loadAllAwards($awardManager, $userManager, $orderBy = null, $limit = null, $offset = null) {

  $loadedAwards = $awardManager->loadAll($orderBy, $limit, $offset);
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
* @param UserManager $userManager
* @return Array data for display
*/
function loadUserData($userManager) {

  // Load user data from the session
  try {
    $user = $userManager->load($_SESSION['id']);
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
* @param AwardManager $awardManager
* @param UserManager $userManager
* @return Array data for display
**/
function handleFormInput($awardManager, $userManager, $addressManager, $countryManager) {

  if(isset($_POST['action'])) {
    switch($_POST['action']) {
      case "add":

        // Doctrine wants the actual user object and not the ID
        $user = $userManager->load($_POST['granterId']);

        $award = new Award();
        $award->setRecipientFirst($_POST['recipientFirst']);
        $award->setRecipientLast($_POST['recipientLast']);
        $award->setRecipientEmail($_POST['recipientEmail']);
        $award->setGranter($user);
        $award->setGrantDate(new DateTime($_POST['grantDate']));

        try {
          $awardManager->store($award);
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

        // Fire off the cerificate creation process
        $cg =  new CertGenerator();
        $cg->createCertificate($award);

        $data['certURL'] = $award->getCertURL();

        return $data;
      break;
      case "update":
        $award = $awardManager->load($_POST['id']);

        $data['id'] = $award->getId();
        $data['recipientEmail'] = $award->getRecipientEmail();
        $data['recipientFirst'] = $award->getRecipientFirst();
        $data['recipientLast'] = $award->getRecipientLast();
        $data['granterId'] = $award->getGranter()->getId();

        $grantDate = $award->getGrantDate()->format('m/d/Y');
        $data['grantDate'] = $grantDate;
        $data['certURL'] = $award->getCertURL();

        $address = $award->getRecipientAddress();
        $country = $address->getCountry();
        $data['addressOptions'] = createAddressOptions($addressManager, $address->getId());
        $data['countryOptions'] = createCountryOptions($countryManager, $country->getId());
        return $data;
      break;
      case "doUpdate":
        $award = $awardManager->load($_POST['id']);
        $award->setRecipientFirst($_POST['recipientFirst']);
        $award->setRecipientLast($_POST['recipientLast']);
        $award->setRecipientEmail($_POST['recipientEmail']);
        $award->setAwardType($_POST['awardType']);
        if($_POST['grantDate']) {
          $grantDate = DateTime::createFromFormat('m/d/Y', $_POST['grantDate']);
          $award->setGrantDate($grantDate);
        }
        $awardManager->store($award);

        $data['id'] = $award->getId();
        $data['recipientEmail'] = $award->getRecipientEmail();
        $data['recipientFirst'] = $award->getRecipientFirst();
        $data['recipientLast'] = $award->getRecipientLast();
        $data['granterId'] = $award->getGranter()->getId();
        $grantDate = $award->getGrantDate()->format('m/d/Y');
        $data['grantDate'] = $grantDate;
        $data['certURL'] = $award->getCertURL();
        return $data;
      break;
      case "delete":
        $addressManagerin = $awardManager->load($_POST['id']);
        $awardManager->delete($addressManagerin);
        $data['deleted'] = true;
        return $data;
      break;
    }
  }
}

/**
* Creates a set of country option elements for display by the template engine
* @param countryManager
* @param int (optional) ID of address to mark selected
* @return string prepared HTML select options for template engine
*/

function createCountryOptions($countryManager, $countryId=null) {

  $output = "";
  $countries = $countryManager->loadAll(array('name' => 'ASC'));
  foreach($countries as $country) {
    $output .= "<option value='" . $country->getId() . "'";
    if($countryId == $country->getId()) {
      $output .= " selected ";
    }
    $output .= ">";
    $output .= $country->getName();
    $output .= "</option>";
  }
  return $output;
}

/**
* Creates a set of address option elements for display by the template engine
* @param addressManager
* @param int (optional) ID of address to mark selected
* @return string prepared HTML select options for template engine
*/
function createAddressOptions($addressManager, $addressId = null) {
  $output = "";
  $addresses = $addressManager->loadAll(array('description' => 'ASC'));

  foreach($addresses as $addressEntry) {
    $output .= "<option value='" . $addressEntry->getId() . "'";
    if($addressId == $addressEntry->getId()) {
      $output .= " selected ";
    }
    $output .=">";

    $output .= $addressEntry->getDescription();
    $output .= "</option>";
  }

  return $output;
}

/**
* @param int total number of records
* @param int offset of group to display
* @return array number of pages and current page
*/

function createPagination($totalRecords, $offset = 1, $itemsPerPage = 25) {

    if($offset == null) {
      $offset = 0;
    }

    //  We want a sliding window of pages with back, next, first and last buttons
    //  e.g << < [1] 2 3 4 5 6 7 8 9 > >>
    //      << < 10 11 12 13 [14] 15 16 17 18 > >>

    // If we have a partial last page, make sure we count that correctly
    $numPages = floor($totalRecords / $itemsPerPage);

    $remainder = $totalRecords % $itemsPerPage;
    if($remainder != 0) {
      $numPages++;
    }

    if ($offset > $itemsPerPage - 1) {
      $currentPage = ceil($offset / $itemsPerPage) + 1;
    } else {
      $currentPage = 1;
    }

    $pageNumbers = array();

    // If you try to go backwards from the first page, do nothing
    if($currentPage > 1) {
      $backOffset = ($currentPage - 2) * $itemsPerPage;
    } else {
      $backOffset = 0;
    }

    // If you try to go forward from the last page, stay at the same offset
    if($currentPage == $numPages) {
      $nextOffset = $offset;
      $lastOffset = $offset;
    } else {
      $nextOffset = $offset + $itemsPerPage;

      if($remainder > 0) {
        $lastOffset = $totalRecords - $remainder;
      } else {
        $lastOffset = ($numPages - 1) * $itemsPerPage;
      }
    }

    // Showing <$currentOffsetStart> - <$currentOffsetEnd> of 123845 records
    $currentOffsetStart = $offset;
    if($totalRecords < $offset + $itemsPerPage) {
      $currentOffsetEnd = $totalRecords;
    } else {
      $currentOffsetEnd = $offset + $itemsPerPage - 1;
    }

    $pageNumbers[] = array("label"=>"<<", "offset"=>0);
    $pageNumbers[] = array("label"=>"<", "offset"=>$backOffset);


    // If the last page is visible, we don't want to advance the left side
    if (($numPages - $currentPage) < 4) {
      $firstPage = $currentPage - 7;
      $lastPage = $numPages;
    // Otherwise, we try to keep the current page in the middle of the visible list
    } else {
      // If the current page is above 4, we need to start moving the window to the right
      if ($currentPage > 4) {
        $firstPage = $currentPage - 4;
        if($numPages > $currentPage + 4) {
          $lastPage = $currentPage + 4;
        } else {
          $firstPage = $numPages - 8;
          $lastPage = $numPages;
        }
      } else {
        $firstPage = 1;
        if($numPages < 8) {
          $lastPage = $numPages;
        }
        $lastPage = 9;
      }
    }

    for ($i = $firstPage; $i < $lastPage + 1; $i++) {

      $pageData = array(
              'label' => $i,
              'offset' => ($i - 1) * $itemsPerPage);

      // The template engine just differentiates by whether it exists or not
      if ($i == $currentPage) {
        $pageData['active'] = true;
      }
      $pageNumbers[] = $pageData;
    }

    // Next element
    $nextNav = array(
            "label"=>">",
            "offset"=>$nextOffset
          );
    $lastNav = array(
            "label"=>">>",
            "offset"=>$lastOffset
    );

    // HACK: Working around some mustache nesting limitation by passing
    // the string I want in the classname instead of a flag..
    if($currentPage == $numPages) {
      $lastNav['disabled'] = "disabled";
      $nextNav['disabled'] = "disabled";
    }

    // Last element
    $pageNumbers[] = $nextNav;
    $pageNumbers[] = $lastNav;

    return array('numPages' => $numPages,
                 'currentOffsetStart' => $currentOffsetStart,
                 'currentOffsetEnd' => $currentOffsetEnd,
                 'pageData' => $pageNumbers,
                 'pageLink' => $_SERVER['PHP_SELF']
               );
}
?>
