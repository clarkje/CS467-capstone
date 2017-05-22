<?php

// Populates a list of Award Types
//
// NOTE: This is a destructive operation.
// It will screw up any associated indices.
// Only run this if you're okay blowing away the entire existing database.

ini_set('display_errors', 'On');

require_once(__DIR__ . "/../config/admin/config.php");
require_once(__DIR__ . "/../config/admin/doctrine.php");
require_once(__DIR__ . "/../db/src/AwardType.php");
require_once(__DIR__ . "/../db/AwardTypeManager.php");

// All of the regions to be created
$awardTypes = array(
  array("description"=>"Outstanding", "templateFile"=>"outstanding.png"),
  array("description"=>"Winner", "templateFile"=>"winner.png"),
  array("description"=>"Congratulations", "templateFile"=>"congratulations.png")
);

// Set up doctrine objects
$emf = new EntityManagerFactory();
$em = $emf->getEntityManager();
$atm = new AwardTypeManager($em);

// Purge any existing data
purgeAwardTypeData($em);

// Create the region data
createAwardTypeData($em, $atm, $awardTypes);


/**
* @param EntityManager doctrine entity manager
* @param CountryManager object
* @param RegionManager object
* @return null
*/
function createCountryData($em, $cm, $rm, $countries) {
  foreach($countries AS $countryData) {
    $country = new Country();
    $country->setName($countryData['country']);
    $region = $rm->loadByName($countryData['region']);
    $country->setRegion($region[0]);
    $cm->store($country);
  }
}

/**
* Create region entries based on the regions Array
* @param EntityManager doctrine entity manager
* @param AwardTypeManager object
* @return null
*/
function createAwardTypeData($em, $atm, $awardTypes) {
  foreach($awardTypes AS $awardTypeData) {
    $awardType = new AwardType();
    $awardType->setDescription($awardTypeData['description']);
    $awardType->setTemplateFile($awardTypeData['templateFile']);
    $atm->store($awardType);
  }
}

/**
* Deletes any existing Region records from the database
*/
function purgeAwardTypeData($em) {

  // Just blow away all instances of our test users between tests.
  // From example at:
  // http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/native-sql.html
  $query = $em->createQuery('DELETE FROM AwardType a WHERE a.id > 0');
  $query->getResult();
}
?>
