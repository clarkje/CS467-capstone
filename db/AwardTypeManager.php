<?php

require_once(__DIR__ . "/src/AwardType.php");

class AwardTypeManager {

  /**
  * @var \Doctrine\ORM\EntityManager
  */
  private $em;

  public function __construct(Doctrine\ORM\EntityManager $em) {
    $this->em = $em;
  }

  /**
  * @param $awardTypeId
  * @return AwardType
  */
  public function load($awardTypeId) {
    return $this->em->find('AwardType', $awardTypeId);
  }

  /**
  * @return Array of Awards
  */
  public function loadAll() {
    return $this->em->getRepository('AwardType')->findAll();
  }

  /**
  * @return bool
  * @throws Exception
  */
  public function store(AwardType $awardType) {
    $this->em->persist($awardType);
    $this->em->flush();
    return true;
  }

  /**
  * @return bool
  * @throws Exception
  */
  public function delete(AwardType $awardType) {
    $this->em->remove($awardType);
    $this->em->flush();
    return true;
  }
}
?>
