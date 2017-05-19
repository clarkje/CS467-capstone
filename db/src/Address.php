<?php
use Doctrine\ORM\Mapping as ORM;

/**
* @Table(name="address", uniqueConstraints={@UniqueConstraint(name="description_idx",columns={"description"})})
* @Entity
**/
class Address {

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
  * @Column(name="description", type="string", nullable=false)
  **/
  protected $description;


  /**
  * @var string
  *
  * @Column(name="address1", type="string", nullable=false)
  **/
  protected $address1;

  /**
  * @var string
  *
  * @Column(name="address2", type="string", nullable=true)
  **/
  protected $address2;

  /**
  * @var string
  *
  * @Column(name="address3", type="string", nullable=true)
  **/
  protected $address3;

  /**
  * @var string
  *
  * @Column(name="city", type="string", nullable=false)
  **/
  protected $city;

  /**
  * @var string
  *
  * @Column(name="state", type="string", nullable=true)
  **/
  protected $state;   // not all countries have states... 

  /**
  * @var string
  *
  * @Column(name="country", type="string", nullable=false)
  **/
  protected $country;

  /**
  * @var string
  *
  * @Column(name="zipcode", type="string", nullable=false)
  **/
  protected $zipcode;

  /**
  * @return int
  **/

  public function getId() {
    return $this->id;
  }

  /**
  * @return string
  **/
  public function getDescription() {
    return $this->description;
  }

  /**
  * @return string
  **/
  public function getAddress1() {
    return $this->address1;
  }

  /**
  * @return string
  **/
  public function getAddress2() {
    return $this->address2;
  }

  /**
  * @return string
  **/
  public function getAddress3() {
    return $this->address3;
  }

  /**
  * @return string
  **/
  public function getCity() {
    return $this->city;
  }

  /**
  * @return string
  **/
  public function getState() {
    return $this->state;
  }

  /**
  * @return string
  **/
  public function getCountry() {
    return $this->country;
  }

  /**
  * @return string
  **/
  public function getZipcode() {
    return $this->zipcode;
  }

  /**
  * @param string
  * @return null
  */
  public function setDescription($description) {
    $this->description = $description;
  }

  /**
  * @param string
  * @return null
  */
  public function setAddress1($address) {
    $this->address1 = $address;
  }

  /**
  * @param string
  * @return null
  */
  public function setAddress2($address) {
    $this->address2 = $address;
  }

  /**
  * @param string
  * @return null
  */
  public function setAddress3($address) {
    $this->address3 = $address;
  }

  /**
  * @param string
  * @return null
  */
  public function setCity($city) {
    $this->city = $city;
  }

  /**
  * @param string
  * @return null
  */
  public function setState($state) {
    $this->state = $state;
  }

  /**
  * @param string
  * @return null
  */
  public function setCountry($country) {
    $this->country = $country;
  }

  /**
  * @param string
  * @return null
  */
  public function setZipcode($zipcode) {
    $this->zipcode = $zipcode;
  }

}
?>
