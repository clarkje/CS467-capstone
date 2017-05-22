<?php

require_once(__DIR__ . "/src/Country.php");

class RegionManager {

  /**
  * @var \Doctrine\ORM\EntityManager
  */
  private $em;

  public function __construct(Doctrine\ORM\EntityManager $em) {
    $this->em = $em;
  }

  /**
  * @param $regionId
  * @return Region
  */
  public function load($regionId) {
    return $this->em->find('Region', $regionId);
  }

  /**
  * @return Array of Regions
  */
  public function loadAll() {
    return $this->em->getRepository('Region')->findAll();
  }

  /**
  * @pearam $email
  * @return Admin
  */

  public function loadByName($name) {
    return $this->em->getRepository('Region')->findBy(array('name' => $name));
  }

  /**
  * @return bool
  * @throws Exception
  */
  public function store(Region $region) {
    $this->em->persist($region);
    $this->em->flush();
    return true;
  }

  /**
  * @return bool
  * @throws Exception
  */
  public function delete(Region $region) {
    $this->em->remove($region);
    $this->em->flush();
    return true;
  }
}
?>
