<?php

// Populates a list of Regions
//
// NOTE: This is a destructive operation.
// Only run this if you're okay blowing away the entire existing database.
//
// Country and Region list taken from:
// https://gist.githubusercontent.com/richjenks/15b75f1960bc3321e295/raw/62749882ed0e9dffa3edd7a9a44a7be59df8402c/countries.md

ini_set('display_errors', 'On');

require_once(__DIR__ . "/../config/admin/config.php");
require_once(__DIR__ . "/../config/admin/doctrine.php");
require_once(__DIR__ . "/../db/src/Region.php");
require_once(__DIR__ . "/../db/RegionManager.php");
require_once(__DIR__ . "/../db/src/Country.php");
require_once(__DIR__ . "/../db/CountryManager.php");
require_once(__DIR__ . "/../vendor/autoload.php");

// All of the regions to be created
$regions = array(
  array("name"=>"APAC"),
  array("name"=>"EMEA"),
  array("name"=>"AMER")
);

// All of the countries to be created
$countries = Array(
  array("country"=>"Afghanistan","ISO"=>"AF","region"=>"EMEA"),
  array("country"=>"Åland Islands","ISO"=>"AX","region"=>"EMEA"),
  array("country"=>"Albania","ISO"=>"AL","region"=>"EMEA"),
  array("country"=>"Algeria","ISO"=>"DZ","region"=>"EMEA"),
  array("country"=>"American Samoa","ISO"=>"AS","region"=>"APAC"),
  array("country"=>"Andorra","ISO"=>"AD","region"=>"EMEA"),
  array("country"=>"Angola","ISO"=>"AO","region"=>"EMEA"),
  array("country"=>"Anguilla","ISO"=>"AI","region"=>"AMER"),
  array("country"=>"Antarctica","ISO"=>"AQ","region"=>"APAC"),
  array("country"=>"Antigua and Barbuda","ISO"=>"AG","region"=>"AMER"),
  array("country"=>"Argentina","ISO"=>"AR","region"=>"AMER"),
  array("country"=>"Armenia","ISO"=>"AM","region"=>"EMEA"),
  array("country"=>"Aruba","ISO"=>"AW","region"=>"AMER"),
  array("country"=>"Australia","ISO"=>"AU","region"=>"APAC"),
  array("country"=>"Austria","ISO"=>"AT","region"=>"EMEA"),
  array("country"=>"Azerbaijan","ISO"=>"AZ","region"=>"EMEA"),
  array("country"=>"Bahamas","ISO"=>"BS","region"=>"AMER"),
  array("country"=>"Bahrain","ISO"=>"BH","region"=>"EMEA"),
  array("country"=>"Bangladesh","ISO"=>"BD","region"=>"APAC"),
  array("country"=>"Barbados","ISO"=>"BB","region"=>"AMER"),
  array("country"=>"Belarus","ISO"=>"BY","region"=>"EMEA"),
  array("country"=>"Belgium","ISO"=>"BE","region"=>"EMEA"),
  array("country"=>"Belize","ISO"=>"BZ","region"=>"AMER"),
  array("country"=>"Benin","ISO"=>"BJ","region"=>"EMEA"),
  array("country"=>"Bermuda","ISO"=>"BM","region"=>"AMER"),
  array("country"=>"Bhutan","ISO"=>"BT","region"=>"APAC"),
  array("country"=>"Bolivia (Plurinational State of)","ISO"=>"BO","region"=>"AMER"),
  array("country"=>"Bonaire - Sint Eustatius and Saba","ISO"=>"BQ","region"=>"AMER"),
  array("country"=>"Bosnia and Herzegovina","ISO"=>"BA","region"=>"EMEA"),
  array("country"=>"Botswana","ISO"=>"BW","region"=>"EMEA"),
  array("country"=>"Bouvet Island","ISO"=>"BV","region"=>"EMEA"),
  array("country"=>"Brazil","ISO"=>"BR","region"=>"AMER"),
  array("country"=>"British Indian Ocean Territory","ISO"=>"IO","region"=>"APAC"),
  array("country"=>"Brunei Darussalam","ISO"=>"BN","region"=>"APAC"),
  array("country"=>"Bulgaria","ISO"=>"BG","region"=>"EMEA"),
  array("country"=>"Burkina Faso","ISO"=>"BF","region"=>"EMEA"),
  array("country"=>"Burundi","ISO"=>"BI","region"=>"EMEA"),
  array("country"=>"Cambodia","ISO"=>"KH","region"=>"APAC"),
  array("country"=>"Cameroon","ISO"=>"CM","region"=>"EMEA"),
  array("country"=>"Canada","ISO"=>"CA","region"=>"AMER"),
  array("country"=>"Cabo Verde","ISO"=>"CV","region"=>"EMEA"),
  array("country"=>"Cayman Islands","ISO"=>"KY","region"=>"AMER"),
  array("country"=>"Central African Republic","ISO"=>"CF","region"=>"EMEA"),
  array("country"=>"Chad","ISO"=>"TD","region"=>"EMEA"),
  array("country"=>"Chile","ISO"=>"CL","region"=>"AMER"),
  array("country"=>"China","ISO"=>"CN","region"=>"APAC"),
  array("country"=>"Christmas Island","ISO"=>"CX","region"=>"APAC"),
  array("country"=>"Cocos (Keeling) Islands","ISO"=>"CC","region"=>"APAC"),
  array("country"=>"Colombia","ISO"=>"CO","region"=>"AMER"),
  array("country"=>"Comoros","ISO"=>"KM","region"=>"EMEA"),
  array("country"=>"Congo (Democratic Republic of the)","ISO"=>"CD","region"=>"EMEA"),
  array("country"=>"Congo","ISO"=>"CG","region"=>"EMEA"),
  array("country"=>"Cook Islands","ISO"=>"CK","region"=>"APAC"),
  array("country"=>"Costa Rica","ISO"=>"CR","region"=>"AMER"),
  array("country"=>"Côte d’Ivoire","ISO"=>"CI","region"=>"EMEA"),
  array("country"=>"Croatia","ISO"=>"HR","region"=>"EMEA"),
  array("country"=>"Cuba","ISO"=>"CU","region"=>"AMER"),
  array("country"=>"Curaçao","ISO"=>"CW","region"=>"AMER"),
  array("country"=>"Cyprus","ISO"=>"CY","region"=>"EMEA"),
  array("country"=>"Czech Republic","ISO"=>"CZ","region"=>"EMEA"),
  array("country"=>"Denmark","ISO"=>"DK","region"=>"EMEA"),
  array("country"=>"Djibouti","ISO"=>"DJ","region"=>"EMEA"),
  array("country"=>"Dominica","ISO"=>"DM","region"=>"AMER"),
  array("country"=>"Dominican Republic","ISO"=>"DO","region"=>"AMER"),
  array("country"=>"Ecuador","ISO"=>"EC","region"=>"AMER"),
  array("country"=>"Egypt","ISO"=>"EG","region"=>"EMEA"),
  array("country"=>"El Salvador","ISO"=>"SV","region"=>"AMER"),
  array("country"=>"Equatorial Guinea","ISO"=>"GQ","region"=>"EMEA"),
  array("country"=>"Eritrea","ISO"=>"ER","region"=>"EMEA"),
  array("country"=>"Estonia","ISO"=>"EE","region"=>"EMEA"),
  array("country"=>"Ethiopia","ISO"=>"ET","region"=>"EMEA"),
  array("country"=>"Falkland Islands","ISO"=>"FK","region"=>"EMEA"),
  array("country"=>"Faroe Islands","ISO"=>"FO","region"=>"EMEA"),
  array("country"=>"Fiji","ISO"=>"FJ","region"=>"APAC"),
  array("country"=>"Finland","ISO"=>"FI","region"=>"EMEA"),
  array("country"=>"France","ISO"=>"FR","region"=>"EMEA"),
  array("country"=>"French Guiana","ISO"=>"GF","region"=>"AMER"),
  array("country"=>"French Polynesia","ISO"=>"PF","region"=>"APAC"),
  array("country"=>"French Southern Territories","ISO"=>"TF","region"=>"APAC"),
  array("country"=>"Gabon","ISO"=>"GA","region"=>"EMEA"),
  array("country"=>"Gambia","ISO"=>"GM","region"=>"EMEA"),
  array("country"=>"Georgia","ISO"=>"GE","region"=>"EMEA"),
  array("country"=>"Germany","ISO"=>"DE","region"=>"EMEA"),
  array("country"=>"Ghana","ISO"=>"GH","region"=>"EMEA"),
  array("country"=>"Gibraltar","ISO"=>"GI","region"=>"EMEA"),
  array("country"=>"Greece","ISO"=>"GR","region"=>"EMEA"),
  array("country"=>"Greenland","ISO"=>"GL","region"=>"EMEA"),
  array("country"=>"Grenada","ISO"=>"GD","region"=>"AMER"),
  array("country"=>"Guadeloupe","ISO"=>"GP","region"=>"AMER"),
  array("country"=>"Guam","ISO"=>"GU","region"=>"APAC"),
  array("country"=>"Guatemala","ISO"=>"GT","region"=>"AMER"),
  array("country"=>"Guernsey","ISO"=>"GG","region"=>"EMEA"),
  array("country"=>"Guinea","ISO"=>"GN","region"=>"EMEA"),
  array("country"=>"Guinea-Bissau","ISO"=>"GW","region"=>"EMEA"),
  array("country"=>"Guyana","ISO"=>"GY","region"=>"AMER"),
  array("country"=>"Haiti","ISO"=>"HT","region"=>"AMER"),
  array("country"=>"Heard Island and McDonald Islands","ISO"=>"HM","region"=>"APAC"),
  array("country"=>"Vatican City","ISO"=>"VA","region"=>"EMEA"),
  array("country"=>"Honduras","ISO"=>"HN","region"=>"AMER"),
  array("country"=>"Hong Kong","ISO"=>"HK","region"=>"APAC"),
  array("country"=>"Hungary","ISO"=>"HU","region"=>"EMEA"),
  array("country"=>"Iceland","ISO"=>"IS","region"=>"EMEA"),
  array("country"=>"India","ISO"=>"IN","region"=>"APAC"),
  array("country"=>"Indonesia","ISO"=>"ID","region"=>"APAC"),
  array("country"=>"Iran","ISO"=>"IR","region"=>"EMEA"),
  array("country"=>"Iraq","ISO"=>"IQ","region"=>"EMEA"),
  array("country"=>"Ireland","ISO"=>"IE","region"=>"EMEA"),
  array("country"=>"Isle of Man","ISO"=>"IM","region"=>"EMEA"),
  array("country"=>"Israel","ISO"=>"IL","region"=>"EMEA"),
  array("country"=>"Italy","ISO"=>"IT","region"=>"EMEA"),
  array("country"=>"Jamaica","ISO"=>"JM","region"=>"AMER"),
  array("country"=>"Japan","ISO"=>"JP","region"=>"APAC"),
  array("country"=>"Jersey","ISO"=>"JE","region"=>"EMEA"),
  array("country"=>"Jordan","ISO"=>"JO","region"=>"EMEA"),
  array("country"=>"Kazakhstan","ISO"=>"KZ","region"=>"EMEA"),
  array("country"=>"Kenya","ISO"=>"KE","region"=>"EMEA"),
  array("country"=>"Kiribati","ISO"=>"KI","region"=>"APAC"),
  array("country"=>"Korea (Democratic People’s Republic of)","ISO"=>"KV","region"=>"APAC"),
  array("country"=>"Korea (Republic of)","ISO"=>"KR","region"=>"APAC"),
  array("country"=>"Kuwait","ISO"=>"KW","region"=>"EMEA"),
  array("country"=>"Kyrgyzstan","ISO"=>"KG","region"=>"APAC"),
  array("country"=>"Laos","ISO"=>"LA","region"=>"APAC"),
  array("country"=>"Latvia","ISO"=>"LV","region"=>"EMEA"),
  array("country"=>"Lebanon","ISO"=>"LB","region"=>"EMEA"),
  array("country"=>"Lesotho","ISO"=>"LS","region"=>"EMEA"),
  array("country"=>"Liberia","ISO"=>"LR","region"=>"EMEA"),
  array("country"=>"Libya","ISO"=>"LY","region"=>"EMEA"),
  array("country"=>"Liechtenstein","ISO"=>"LI","region"=>"EMEA"),
  array("country"=>"Lithuania","ISO"=>"LT","region"=>"EMEA"),
  array("country"=>"Luxembourg","ISO"=>"LU","region"=>"EMEA"),
  array("country"=>"Macao","ISO"=>"MO","region"=>"APAC"),
  array("country"=>"Macedonia","ISO"=>"MK","region"=>"EMEA"),
  array("country"=>"Madagascar","ISO"=>"MG","region"=>"EMEA"),
  array("country"=>"Malawi","ISO"=>"MW","region"=>"EMEA"),
  array("country"=>"Malaysia","ISO"=>"MY","region"=>"APAC"),
  array("country"=>"Maldives","ISO"=>"MV","region"=>"APAC"),
  array("country"=>"Mali","ISO"=>"ML","region"=>"EMEA"),
  array("country"=>"Malta","ISO"=>"MT","region"=>"EMEA"),
  array("country"=>"Marshall Islands","ISO"=>"MH","region"=>"APAC"),
  array("country"=>"Martinique","ISO"=>"MQ","region"=>"AMER"),
  array("country"=>"Mauritania","ISO"=>"MR","region"=>"EMEA"),
  array("country"=>"Mauritius","ISO"=>"MU","region"=>"EMEA"),
  array("country"=>"Mayotte","ISO"=>"YT","region"=>"EMEA"),
  array("country"=>"Mexico","ISO"=>"MX","region"=>"AMER"),
  array("country"=>"Micronesia","ISO"=>"FM","region"=>"APAC"),
  array("country"=>"Moldova","ISO"=>"MD","region"=>"EMEA"),
  array("country"=>"Monaco","ISO"=>"MC","region"=>"EMEA"),
  array("country"=>"Mongolia","ISO"=>"MN","region"=>"APAC"),
  array("country"=>"Montenegro","ISO"=>"ME","region"=>"EMEA"),
  array("country"=>"Montserrat","ISO"=>"MS","region"=>"AMER"),
  array("country"=>"Morocco","ISO"=>"MA","region"=>"EMEA"),
  array("country"=>"Mozambique","ISO"=>"MZ","region"=>"EMEA"),
  array("country"=>"Myanmar","ISO"=>"MM","region"=>"APAC"),
  array("country"=>"Namibia","ISO"=>"NA","region"=>"EMEA"),
  array("country"=>"Nauru","ISO"=>"NR","region"=>"APAC"),
  array("country"=>"Nepal","ISO"=>"NP","region"=>"APAC"),
  array("country"=>"Netherlands","ISO"=>"NL","region"=>"EMEA"),
  array("country"=>"New Caledonia","ISO"=>"NC","region"=>"APAC"),
  array("country"=>"New Zealand","ISO"=>"NZ","region"=>"APAC"),
  array("country"=>"Nicaragua","ISO"=>"NI","region"=>"AMER"),
  array("country"=>"Niger","ISO"=>"NE","region"=>"EMEA"),
  array("country"=>"Nigeria","ISO"=>"NG","region"=>"EMEA"),
  array("country"=>"Niue","ISO"=>"NU","region"=>"APAC"),
  array("country"=>"Norfolk Island","ISO"=>"NF","region"=>"APAC"),
  array("country"=>"Northern Mariana Islands","ISO"=>"MP","region"=>"APAC"),
  array("country"=>"Norway","ISO"=>"NO","region"=>"EMEA"),
  array("country"=>"Oman","ISO"=>"OM","region"=>"EMEA"),
  array("country"=>"Pakistan","ISO"=>"PK","region"=>"APAC"),
  array("country"=>"Palau","ISO"=>"PW","region"=>"APAC"),
  array("country"=>"Palestine","ISO"=>"PS","region"=>"EMEA"),
  array("country"=>"Panama","ISO"=>"PA","region"=>"AMER"),
  array("country"=>"Papua New Guinea","ISO"=>"PG","region"=>"APAC"),
  array("country"=>"Paraguay","ISO"=>"PY","region"=>"AMER"),
  array("country"=>"Peru","ISO"=>"PE","region"=>"AMER"),
  array("country"=>"Philippines","ISO"=>"PH","region"=>"APAC"),
  array("country"=>"Pitcairn","ISO"=>"PN","region"=>"APAC"),
  array("country"=>"Poland","ISO"=>"PL","region"=>"EMEA"),
  array("country"=>"Portugal","ISO"=>"PT","region"=>"EMEA"),
  array("country"=>"Puerto Rico","ISO"=>"PR","region"=>"AMER"),
  array("country"=>"Qatar","ISO"=>"QA","region"=>"EMEA"),
  array("country"=>"Réunion","ISO"=>"RE","region"=>"EMEA"),
  array("country"=>"Romania","ISO"=>"RO","region"=>"EMEA"),
  array("country"=>"Russia","ISO"=>"RU","region"=>"EMEA"),
  array("country"=>"Rwanda","ISO"=>"RW","region"=>"EMEA"),
  array("country"=>"Saint Barthélemy","ISO"=>"BL","region"=>"AMER"),
  array("country"=>"Saint Helena","ISO"=>"SH","region"=>"EMEA"),
  array("country"=>"Saint Kitts And Nevis","ISO"=>"KN","region"=>"AMER"),
  array("country"=>"Saint Lucia","ISO"=>"LC","region"=>"AMER"),
  array("country"=>"Saint Martin","ISO"=>"MF","region"=>"AMER"),
  array("country"=>"Saint Pierre and Miquelon","ISO"=>"PM","region"=>"AMER"),
  array("country"=>"Saint Vincent and The Grenadines","ISO"=>"VC","region"=>"AMER"),
  array("country"=>"Samoa","ISO"=>"WS","region"=>"APAC"),
  array("country"=>"San Marino","ISO"=>"SM","region"=>"EMEA"),
  array("country"=>"Sao Tome and Principe","ISO"=>"ST","region"=>"EMEA"),
  array("country"=>"Saudi Arabia","ISO"=>"SA","region"=>"EMEA"),
  array("country"=>"Senegal","ISO"=>"SN","region"=>"EMEA"),
  array("country"=>"Serbia","ISO"=>"RS","region"=>"EMEA"),
  array("country"=>"Seychelles","ISO"=>"SC","region"=>"EMEA"),
  array("country"=>"Sierra Leone","ISO"=>"SL","region"=>"EMEA"),
  array("country"=>"Singapore","ISO"=>"SG","region"=>"APAC"),
  array("country"=>"Sint Maarten (Dutch part)","ISO"=>"SX","region"=>"AMER"),
  array("country"=>"Slovakia","ISO"=>"SK","region"=>"EMEA"),
  array("country"=>"Slovenia","ISO"=>"SI","region"=>"EMEA"),
  array("country"=>"Solomon Islands","ISO"=>"SB","region"=>"APAC"),
  array("country"=>"Somalia","ISO"=>"SO","region"=>"EMEA"),
  array("country"=>"South Africa","ISO"=>"ZA","region"=>"EMEA"),
  array("country"=>"South Georgia and the South Sandwich Islands","ISO"=>"GS","region"=>"EMEA"),
  array("country"=>"South Sudan","ISO"=>"SS","region"=>"APAC"),
  array("country"=>"Spain","ISO"=>"ES","region"=>"EMEA"),
  array("country"=>"Sri Lanka","ISO"=>"LK","region"=>"APAC"),
  array("country"=>"Sudan","ISO"=>"SD","region"=>"EMEA"),
  array("country"=>"Suriname","ISO"=>"SR","region"=>"AMER"),
  array("country"=>"Svalbard","ISO"=>"SJ","region"=>"EMEA"),
  array("country"=>"Swaziland","ISO"=>"SZ","region"=>"EMEA"),
  array("country"=>"Sweden","ISO"=>"SE","region"=>"EMEA"),
  array("country"=>"Switzerland","ISO"=>"CH","region"=>"EMEA"),
  array("country"=>"Syria","ISO"=>"SY","region"=>"EMEA"),
  array("country"=>"Taiwan","ISO"=>"TW","region"=>"APAC"),
  array("country"=>"Tajikistan","ISO"=>"TJ","region"=>"APAC"),
  array("country"=>"Tanzania","ISO"=>"TZ","region"=>"EMEA"),
  array("country"=>"Thailand","ISO"=>"TH","region"=>"APAC"),
  array("country"=>"Timor-Leste","ISO"=>"TL","region"=>"APAC"),
  array("country"=>"Togo","ISO"=>"TG","region"=>"EMEA"),
  array("country"=>"Tokelau","ISO"=>"TK","region"=>"APAC"),
  array("country"=>"Tonga","ISO"=>"TO","region"=>"APAC"),
  array("country"=>"Trinidad and Tobago","ISO"=>"TT","region"=>"AMER"),
  array("country"=>"Tunisia","ISO"=>"TN","region"=>"EMEA"),
  array("country"=>"Turkey","ISO"=>"TR","region"=>"EMEA"),
  array("country"=>"Turkmenistan","ISO"=>"TM","region"=>"APAC"),
  array("country"=>"Turks and Caicos Islands","ISO"=>"TC","region"=>"AMER"),
  array("country"=>"Tuvalu","ISO"=>"TV","region"=>"APAC"),
  array("country"=>"Uganda","ISO"=>"UG","region"=>"EMEA"),
  array("country"=>"Ukraine","ISO"=>"UA","region"=>"EMEA"),
  array("country"=>"United Arab Emirates","ISO"=>"AE","region"=>"EMEA"),
  array("country"=>"United Kingdom","ISO"=>"GB","region"=>"EMEA"),
  array("country"=>"United States","ISO"=>"US","region"=>"AMER"),
  array("country"=>"United States Minor Outlying Islands","ISO"=>"UM","region"=>"APAC"),
  array("country"=>"Uruguay","ISO"=>"UY","region"=>"AMER"),
  array("country"=>"Uzbekistan","ISO"=>"UZ","region"=>"APAC"),
  array("country"=>"Vanuatu","ISO"=>"VU","region"=>"APAC"),
  array("country"=>"Venezuela","ISO"=>"VE","region"=>"AMER"),
  array("country"=>"Viet Nam","ISO"=>"VN","region"=>"APAC"),
  array("country"=>"Virgin Islands (British)","ISO"=>"VG","region"=>"AMER"),
  array("country"=>"Virgin Islands (U.S.)","ISO"=>"VI","region"=>"AMER"),
  array("country"=>"Wallis and Futuna","ISO"=>"WF","region"=>"APAC"),
  array("country"=>"Western Sahara","ISO"=>"EH","region"=>"EMEA"),
  array("country"=>"Yemen","ISO"=>"YE","region"=>"EMEA"),
  array("country"=>"Zambia","ISO"=>"ZM","region"=>"EMEA"),
  array("country"=>"Zimbabwe","ISO"=>"ZW","region"=>"EMEA")
);

// Set up doctrine objects
$emf = new EntityManagerFactory();
$em = $emf->getEntityManager();
$rm = new RegionManager($em);
$cm = new CountryManager($em);

// Purge any existing data
purgeRegionData($em);

// Create the region data
createRegionData($em, $rm, $regions);

// Purge any country database
purgeCountryData($em);

// Create the country database
createCountryData($em, $cm, $rm, $countries);


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
* @param RegionManager object
* @return null
*/
function createRegionData($em, $rm, $regions) {
  foreach($regions AS $regionData) {
    $region = new Region();
    $region->setName($regionData['name']);
    $rm->store($region);
  }
}

/**
* Deletes any existing Region records from the database
*/
function purgeRegionData($em) {

  // Just blow away all instances of our test users between tests.
  // From example at:
  // http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/native-sql.html
  $query = $em->createQuery('DELETE Region r WHERE r.id > 0');
  $query->getResult();
}

/**
* Deletes any existing Region records from the database
*/
function purgeCountryData($em) {

  // Just blow away all instances of our test users between tests.
  // From example at:
  // http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/native-sql.html
  $query = $em->createQuery('DELETE Country c WHERE c.id > 0');
  $query->getResult();
}





?>
