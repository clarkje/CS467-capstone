<?php
ini_set('display_errors', 'On');

require_once(__DIR__ . "/config/client/config.php");
require_once(__DIR__ . "/db/src/Award.php");
require_once(__DIR__ . "/db/AwardManager.php");
require_once(__DIR__ . "/db/src/User.php");
require_once(__DIR__ . "/db/UserManager.php");
require_once(__DIR__ . "/lib/CertGenerator.php");

// Set up doctrine objects
$emf = new EntityManagerFactory();
$em = $emf->getEntityManager();
$um = new UserManager($em);
$am = new AwardManager($em);

// Setup the template engine
require($_SERVER['DOCUMENT_ROOT'] . '/config/client/mustache.php');
$tpl = $mustache->loadTemplate('report_region');
$data = array();  // Output for display by the template engine

$data['formAction'] = $_SERVER['PHP_SELF'];


// Handle any custom dates that might get set
if(isset($_GET['startDate'])) {
  $startDate = DateTime::createFromFormat('m/d/Y', $_GET['startDate']);
  $data['dateRangeStart'] = date_format($startDate, "M d Y");
  $data['startDate'] = date_format($startDate, "m/d/Y");  // it gets validated this way...
} else {
  $startDate = null;
}

if(isset($_GET['endDate'])) {
  $endDate = DateTime::createFromFormat('m/d/Y', $_GET['endDate']);
  $data['dateRangeEnd'] = date_format($endDate, "M d Y");
  $data['endDate'] = date_format($endDate, "m/d/Y");
} else {
  $endDate = null;
}

// If the user is logged in, proceed.  Otherwise, show the login screen.
if( array_key_exists('logged_in',$_SESSION) && $_SESSION['logged_in'] == "true") {

  // Tells the template to show the content instead of the login page
  $data['user_info'] = true;

  $countArray = loadAwardCountByRegion($am, $startDate, $endDate);
  $data['chartDataString'] = buildRegionGraphData($countArray);

  // Set the display title of the page
  $data['page_title'] = 'Reports - Awards By Region';
} else {
  $data['page_title'] = 'Log In';
}
$data['title'] = 'Project Phoenix - Reports - Awards By Region';

// Pass the resulting data into the template
echo $tpl->render($data);

/**
* @param AwardManager $awardManager
* @param DateTime (optional) - Start Date
* @param DateTime (optional) - End Date
* return Array counts by region
*/
function loadAwardCountByRegion($awardManager, $startDate = null, $endDate = null) {
  $awardCountArray = $awardManager->getAwardCountByRegion($startDate, $endDate);
  if(empty($awardCountArray)) {
    return null;
  }
  return $awardCountArray;
}

/**
* Formats the data expected by the Google Pie Graph and returns it as prepared string
* @param Array Count of Awards By Region
* @return String Formatted data string for Google Graphs
*/

function buildRegionGraphData($countArray) {

  // Label the graph elements
  $graphData = "['Region', 'Awards Received']";

  // Build the array string
  for($i = 0; $i < sizeof($countArray); $i++) {
    $region = $countArray[$i];
    $graphData .= ",['" . $region['name'] . "'," . $region['awardCount'] . "]";
  }

  return $graphData;
}
?>
