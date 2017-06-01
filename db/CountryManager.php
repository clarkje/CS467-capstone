<?php

require_once(__DIR__ . "/src/Country.php");

class CountryManager {

  /**
  * @var \Doctrine\ORM\EntityManager
  */
  private $em;

  public function __construct(Doctrine\ORM\EntityManager $em) {
    $this->em = $em;
  }

  /**
  * @param $countryId
  * @return Country
  */
  public function load($countryId) {
    return $this->em->find('Country', $countryId);
  }

  /**
  * Returns an array of all Countries
  * @param Array (optional) Array of column to sort by and direction
  * example: array('name' => 'ASC')
  * @return Array of Countries
  */
  public function loadAll($sort = null) {
    return $this->em->getRepository('Country')->findBy(array(),$sort);
  }

  /**
  * @return bool
  * @throws Exception
  */
  public function store(Country $country) {
    $this->em->persist($country);
    $this->em->flush();
    return true;
  }

  /**
  * @return bool
  * @throws Exception
  */
  public function delete(Country $country) {
    $this->em->remove($country);
    $this->em->flush();
    return true;
  }
}
?>
