<?php
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
			/*$this->db->query("SET NAMES 'utf8'");
			$this->db->query("SET CHARACTER SET 'utf8'");
			$this->db->query("SET SESSION collation_connection = 'utf8_general_ci'");*/
      $result=$this->db->fetchOne("SELECT * FROM active_user_sessions where access_token = :atoken",\Phalcon\Db::FETCH_ASSOC,['atoken'=>$token]);
      return $result;
   }
}
