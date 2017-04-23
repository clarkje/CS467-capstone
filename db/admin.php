<?php

class Admin {

  private $id;
  private $email;
  private $created;

  /***
  Constructor - returns an Admin object for the supplied email
  Based on code at: http://stackoverflow.com/questions/3600777/read-only-properties-in-php
  */
  public function __construct($id) {

    // Returns the object with the specified id

    // If the id doesn't exist, returns null

  }


  /***
  Constructor - returns an Admin object for the supplied email
  Based on code at: http://stackoverflow.com/questions/3600777/read-only-properties-in-php
  */
  public function __construct($email) {

    // Check to see if the object exists in the database

    // If it does, return it

    // If not, create it and then return it

  }

  /***
  Getter - returns an object property for the supplied property name
  Based on code at: http://stackoverflow.com/questions/3600777/read-only-properties-in-php
  */
  public function __get($name) {
    return isset($this->name) ? $this->name : null;
  }

  public function delete() {


  }

  public function add() {

    $sql = "INSERT INTO "


  }

  public function update() {


  }

  private function getByEmail($email) {

  }

  private function getByID($id) {

  }



  /***
  Calculates and sets the appropriate password has for the user in the database
  */
  public function updatePassword($password) {

  }

}
?>
