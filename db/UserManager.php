<?php

require_once(__DIR__ . "/src/User.php");
require_once(__DIR__ . "/../config/admin/doctrine.php");

class UserManager {

  /**
  * @var \Doctrine\ORM\EntityManager
  */
  private $em;

  public function __construct(Doctrine\ORM\EntityManager $em) {
    $this->em = $em;
  }

  /**
  * @param $userId
  * @return User
  */
  public function load($userId) {
    return $this->em->find('User', $userId);
  }

  /**
  * @pearam $email
  * @return User
  */

  public function loadByEmail($email) {
    return $this->em->getRepository('User')->findBy(array('email' => $email));
  }

  /**
  * @return bool
  * @throws Exception
  */
  public function store(User $user) {
    $this->em->persist($user);
    $this->em->flush();
    return true;
  }

  /**
  * @return bool
  * @throws Exception
  */
  public function delete(User $user) {
    $this->em->remove($user);
    $this->em->flush();
    return true;
  }

}
?>
