<?php

require_once(__DIR__ . "/src/Award.php");

class AwardManager {

  /**
  * @var \Doctrine\ORM\EntityManager
  */
  private $em;

  public function __construct(Doctrine\ORM\EntityManager $em) {
    $this->em = $em;
  }

  /**
  * @param $awardId
  * @return Award
  */
  public function load($awardId) {
    return $this->em->find('Award', $awardId);
  }

  /**
  * @return Array of Awards
  */
  public function loadAll() {
    return $this->em->getRepository('Award')->findAll();
  }

  /**
  * @return bool
  * @throws Exception
  */
  public function store(Award $award) {
    $this->em->persist($award);
    $this->em->flush();
    return true;
  }

  /**
  * @return bool
  * @throws Exception
  */
  public function delete(Award $award) {
    $this->em->remove($award);
    $this->em->flush();
    return true;
  }
}
?>
