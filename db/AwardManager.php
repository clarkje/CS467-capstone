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

  public function getAwardCount() {
    $query = $this->em->createQuery("SELECT COUNT(a) FROM Award a");
    $result =  $query->getResult();

    //Return something more useful than this:
    //array(1) { [0]=> array(1) { [1]=> string(3) "700" } }

    return $result[0][1];
  }


  /**
  * Returns a count of awards by the user that granted them
  * @param DateTime $startDate
  * @param DateTime $endDate
  * @return Array array of regions and counts
  */
  public function getAwardCountByGranter($startDate = null, $endDate = null, $limit = null) {

    $queryString = "SELECT count(a) AS awardCount, u.firstName, u.lastName
                      FROM Award a
                      JOIN a.granter u
    ";

    if(!empty($startDate) || !empty($endDate)) {
      $queryString .= " WHERE ";
    }

    if(!empty($startDate)) {
      $queryString .= " a.grantDate > :startDate";
    }

    if(!empty($startDate) && !empty($endDate)) {
      $queryString .= " AND ";
    }

    if(!empty($endDate)) {
      $queryString .= " a.grantDate < :endDate";
    }

    $queryString .= " GROUP BY u.firstName, u.lastName";

    $query = $this->em->createQuery($queryString);

    $params = array();
    if(!empty($startDate)) {
      $query->setParameter('startDate', $startDate);
    }

    if(!empty($endDate)) {
      $query->setParameter('endDate', $endDate);
    }

    $result = $query->getResult();
    return $result;
  }

  /**
  * Returns a count of awards granted by region for a specified date window
  * @param DateTime $startDate
  * @param DateTime $endDate
  * @return Array array of regions and counts
  */

  public function getAwardCountByRegion($startDate = null, $endDate = null) {

    $queryString = "SELECT count(a) AS awardCount, r.name
                    FROM Award a
                    JOIN a.recipientAddress addr
                    JOIN addr.country c
                    JOIN c.region r";

    if(!empty($startDate) || !empty($endDate)) {
      $queryString .= " WHERE ";
    }

    if(!empty($startDate)) {
      $queryString .= " a.grantDate > :startDate";
    }

    if(!empty($startDate) && !empty($endDate)) {
      $queryString .= " AND ";
    }

    if(!empty($endDate)) {
      $queryString .= " a.grantDate < :endDate";
    }

    $queryString .= " GROUP BY r.name";

    $query = $this->em->createQuery($queryString);

    $params = array();
    if(!empty($startDate)) {
      $query->setParameter('startDate', $startDate);
    }

    if(!empty($endDate)) {
      $query->setParameter('endDate', $endDate);
    }

    $result = $query->getResult();
    return $result;
  }



  /**
  * @return Array of Awards
  */
  public function loadAll($orderBy = null, $limit = null, $offset = null) {

    // Had to dig into the doctrine source to figure this out.
    // Only findBy supports ordering, limit and offset, but findAll is just an alias
    // that passes in an empty array for criteria...

    return $this->em->getRepository('Award')->findBy(array(), $orderBy, $limit, $offset);
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
