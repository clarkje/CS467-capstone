<?php
ini_set('display_errors', 'On');

require_once(__DIR__ . "/config/client/config.php");
require_once(__DIR__ . "/db/src/Award.php");
require_once(__DIR__ . "/db/AwardManager.php");

// Set up doctrine objects
$emf = new EntityManagerFactory();
$em = $emf->getEntityManager();
$um = new UserManager($em);
$am = new AwardManager($em);

// Setup the template engine
require($_SERVER['DOCUMENT_ROOT'] . '/config/client/mustache.php');
$tpl = $mustache->loadTemplate('view_award');
$data = array();  // Output for display by the template engine

// Pagination parameters
$orderBy = null;
$offset = null;
$itemsPerPage = 15 ;

if (isset($_GET['orderBy'])) {
  $orderBy = $_GET['orderBy'];
}
if (isset($_GET['offset'])) {
  $offset = $_GET['offset'];
}

// If the user is logged in, proceed.  Otherwise, show the login screen.
if( array_key_exists('logged_in',$_SESSION) && $_SESSION['logged_in'] == "true") {
  $data['awards'] = loadAllAwards($am, $um, $orderBy, $itemsPerPage, $offset);
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

$data['pagination'] = createPagination($am->getAwardCount(), $offset, $itemsPerPage);

// Pass the resulting data into the template
echo $tpl->render($data);

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

  if ($award->hasCert()){
    $data['certURL'] = $award->getCertURL();
    $data['certType'] = "application/pdf";
  }
  else {
    $data['certURL'] = "/cert_assets/not_found.png";
    $data['certType'] = "image/png";
  }

  return $data;
}

function loadCertificateHelper($award) {
  if ($award->getCertURL() === null){

  }
}

/**
* @param AwardManager $am
* @param UserManager $um
* @param String $orderBy default = null
* @param int $limit - number of records to return
* @param int $offset - offset of record window
* @return Array data for display
*/
function loadAllAwards($am, $um, $orderBy = null, $limit = null, $offset = null) {

  $loadedAwards = $am->loadAll($orderBy, $limit, $offset);

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
