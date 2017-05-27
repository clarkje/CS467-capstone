<?php
use Doctrine\ORM\Mapping as ORM;

// TODO: Refactor to eliminate redundant code between Admin/User

/**
* @Table(name="country", uniqueConstraints={@UniqueConstraint(name="name_idx",columns={"name"})})
* @Entity
**/
class Country {

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
  * @Column(name="name", type="string", nullable=false)
  **/
  protected $name;

  /**
  * @var int
  * Many Countries have one Region
  * @ManyToOne(targetEntity="Region", cascade={"persist"})
  * @JoinColumn(name="region_id", referencedColumnName="id", nullable=false)
  */
  private $region;


  /**
  * @return int
  **/

  public function getId() {
    return $this->id;
  }

  /**
  * @return string
  **/
  public function getName() {
    return $this->name;
  }

  /**
  * @return int
  **/
  public function getRegion() {
    return $this->region;
  }

  /**
  * @param string
  * @return null
  **/
  public function setName($name) {
    $this->name = $name;
  }

  /**
  * @param string
  * @return null
  **/
  public function setRegion($region) {
    $this->region = $region;
  }
}
?>
