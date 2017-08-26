<?php
use Phalcon\Db;
namespace Models;

class MiLidaCommon extends \Phalcon\Mvc\Model {
	public $db;
 public function initialize()
  {
     $this->db=$this->getDi()->getShared('db');
  }

	public function getUserInfo($token)
   {
      //print_r(\Phalcon\Di::getDefault()->getShared('db')); // This is the ugly way to grab the connection.
      $result=$this->db->query("SELECT * FROM active_user_sessions where access_token = :atoken",Db::FETCH_ASSOC,$token); 
      return $result->fetchAll();
   }
}
