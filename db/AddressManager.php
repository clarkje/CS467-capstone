<?php

require_once(__DIR__ . "/src/Address.php");

class AddressManager {

  /**
  * @var \Doctrine\ORM\EntityManager
  */
  private $em;

  public function __construct(Doctrine\ORM\EntityManager $em) {
    $this->em = $em;
  }

  /**
  * @param $addressId
  * @return Address
  */
  public function load($addressId) {
    return $this->em->find('Address', $addressId);
  }

  /**
  * Returns an array of all Addresses
  * @param Array (optional) Array of column to sort by and direction
  * example: array('description' -> 'DESC')
  * @return Array of Addresses
  */
  public function loadAll($sort = null) {
    return $this->em->getRepository('Address')->findBy(array(),$sort);
  }

  /**
  * @return bool
  * @throws Exception
  */

  public function store(Address $address) {
    $this->em->persist($address);
    $this->em->flush();
    return true;
  }

  /**
  * @return bool
  * @throws Exception
  */

  public function delete(Address $address) {
    $this->em->remove($address);
    $this->em->flush();
    return true;
  }

}
?>
