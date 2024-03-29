<?php

require_once(__DIR__ . "/src/Admin.php");

class AdminManager {

  /**
  * @var \Doctrine\ORM\EntityManager
  */
  private $em;

  public function __construct(Doctrine\ORM\EntityManager $em) {
    $this->em = $em;
  }

  /**
  * @param $adminId
  * @return Admin
  */
  public function load($adminId) {
    return $this->em->find('Admin', $adminId);
  }

  /**
  * @return Array of Admins
  */
  public function loadAll() {
    return $this->em->getRepository('Admin')->findAll();
  }

  /**
  * @pearam $email
  * @return Admin
  */

  public function loadByEmail($email) {
    return $this->em->getRepository('Admin')->findBy(array('email' => $email));
  }

  /**
  * @return bool
  * @throws Exception
  */
  public function store(Admin $admin) {
    $this->em->persist($admin);
    $this->em->flush();
    return true;
  }

  /**
  * @return bool
  * @throws Exception
  */
  public function delete(Admin $admin) {
    $this->em->remove($admin);
    $this->em->flush();
    return true;
  }

}
?>
