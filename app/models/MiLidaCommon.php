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
			$this->db->query("SET NAMES 'utf8'");
			$this->db->query("SET CHARACTER SET 'utf8'");
			$this->db->query("SET SESSION collation_connection = 'utf8_general_ci'");
      $result=$this->db->fetchOne("SELECT * FROM active_user_sessions where access_token = :atoken",\Phalcon\Db::FETCH_ASSOC,['atoken'=>$token]);
      return $result;
   }

	 public function createGropup($data, $uid)
    {
       //print_r(\Phalcon\Di::getDefault()->getShared('db')); // This is the ugly way to grab the connection.
 			$this->db->query("SET NAMES 'utf8'");
 			$this->db->query("SET CHARACTER SET 'utf8'");
 			$this->db->query("SET SESSION collation_connection = 'utf8_general_ci'");
			$db->query("INSERT INTO groups (group_number,  first_name, surname, foreman_name, foreman_surname, workshop, product_type, weight, pallet_capacity, series_capcity, labman_name, labman_surname, uid)
			VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )",  array($data->group_number, $data->first_name, $data->surname, $data->foreman_name, $data->foreman_surname, $data->workshop, $data->product_type, $data->weight, $data->pallet_capacity, $data->series_capcity, $data->labman_name, $data->labman_surname, $uid));
			 return $result;
    }

}
