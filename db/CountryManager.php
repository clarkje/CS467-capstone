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
  * @return Array of Countries
  */
  public function loadAll() {
    return $this->em->getRepository('Country')->findAll();
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
