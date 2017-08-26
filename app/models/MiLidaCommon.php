<?php

namespace Models;

class MiLidaCommon extends \Phalcon\Mvc\Model {
	public $db;
 public function initialize()
  {
     $this->db=$this->getDi()->getShared('db');
  }

	public function getUserInfo($uid)
   {
      //print_r(\Phalcon\Di::getDefault()->getShared('db')); // This is the ugly way to grab the connection.

      $result=$this->db->query("SELECT * FROM users"); // Working now
      return $result->fetchAll();
   }
}
