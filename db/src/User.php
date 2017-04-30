<?php
use Doctrine\ORM\Mapping as ORM;

/**
* @Table(name="user")
* @Entity
**/
class User {

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
  * @var string
  *
  * @Column(name="signature_path", type="string")
  **/
  protected $signaturePath;

  /**
  * @var string
  *
  * @Column(name="first_name", type="string", nullable=false)
  **/
  protected $firstName;

  /**
  * @var string
  *
  * @Column(name="last_name", type="string", nullable=false)
  **/
  protected $lastName;

  // TODO: Setup the One To Many relationship for Addresses
  /**
  * @var Array of Addresses
  *
  **/
  protected $addresses;

  /**
  * Sets the creation timestamp automatically, in the format MySQL DATEIME wants
  **/

  // TODO: Define the signature path at construction
  public function __construct() {
    // Set the default timezone for our application
    date_default_timezone_set('America/Los_Angeles');
    $this->created = new DateTime("now");
    $this->signaturePath = "/dev/null";
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
  * @return string
  **/
  public function getFirstName() {
    return $this->firstName;
  }

  /**
  * @return string
  **/
  public function getLastName() {
    return $this->lastName;
  }

  // TODO: Return actual address records...
  /**
  * @return Array of Addresses
  **/
  public function getAddresses() {
    return null;
  }

  /**
  * @return string
  **/
  public function getSignaturePath() {
    return $this->signaturePath;
  }

  /**
  * @param string
  * @return null
  **/
  public function setEmail($email) {
    $this->email = $email;
  }

  /**
  * @param string
  * @return null
  **/
  public function setFirstName($firstName) {
    $this->firstName = $firstName;
  }

  /**
  * @param string
  * @return null
  **/
  public function setLastName($lastName) {
    $this->lastName = $lastName;
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
