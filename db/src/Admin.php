<?php
use Doctrine\ORM\Mapping as ORM;

/**
* @Table(name="admin")
* @Entity
**/
class Admin {

  /**
  * @var integer
  *
  * @Id
  * @GeneratedValue(strategy="IDENTITY")
  * @Column(name="id", type="integer", nullable=false)
  **/
  private $id;


  /**
  * @var string
  *
  * @Column(name="email", type="string", nullable=false)
  **/
  protected $email;


  /**
  * @var string
  *
  * @Column(name="password", type="string", nullable=false)
  **/
  protected $password;


  /**
  * @var datetime
  *
  * @Column(name="created", type="datetime", nullable=false)
  **/
  protected $created;

  /**
  * Sets the creation timestamp automatically, in the format MySQL DATEIME wants
  **/
  public function __construct() {
    // Set the default timezone for our application
    date_default_timezone_set('America/Los_Angeles');
    $this->created = new DateTime("now");
  }

  /**
  * @return int
  **/

  public function getId() {
    return $this->id;
  }

  /**
  * @return string
  **/
  public function getEmail() {
    return $this->email;
  }

  /**
  * @return date
  **/
  public function getCreated() {
    return $this->created;
  }

  /**
  * @param string
  * @return null
  **/

  public function setEmail($email) {
    $this->email = $email;
  }

  /**
  * Calcaulates a salted password hash using PHP's built-in password hashing functions
  * PHP automatically selects a secure salt and encodes everything in the hash for future verification
  * @param password
  * @return null
  **/
  public function setPassword($password) {
    $this->password = password_hash($password, PASSWORD_DEFAULT);
  }

  /**
  * Validates that the provided password matches the persisted hash
  * @param password
  * @return bool
  **/
  public function verifyPassword($password) {
    return password_verify($password, $this->password);
  }
}
?>
