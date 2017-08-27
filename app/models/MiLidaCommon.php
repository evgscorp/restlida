<?php
namespace Models;

class MiLidaCommon extends \Phalcon\Mvc\Model {
	public $db;
 public function initialize()
  {
     $this->db=$this->getDi()->getShared('db');
  }

	public function getShiftProductionInfo($gid)
   {
		 $sql_packages="SELECT count(*) cnt FROM packages_info where operation_id = :operation_id and group_id =:group_id";
		 $sql_pallets="SELECT count(d.pallet_id) cnt FROM (SELECT pallet_id FROM packages_info where operation_id = :operation_id and group_id =:group_id and pallet_id >0 group by pallet_id ) d";
		 $sql_first_package="SELECT * FROM packages_info where operation_id = :operation_id and group_id =:group_id  order by timestmp desc limit 1";
		 $this->utf8init();
		 $result['packages_produced']=$this->db->fetchColumn($sql_packages,['operation_id'=>17,'group_id'=>$gid],'cnt');
		 $result['packages_passed']=$this->db->fetchColumn($sql_packages,['operation_id'=>2,'group_id'=>$gid],'cnt');
		 //$result['pallets_produced']=$this->db->fetchColumn($sql_pallets,\Phalcon\Db::FETCH_ASSOC,['operation_id'=>17,'group_id'=>$gid]);
     //$result['pallets_passed']=$this->db->fetchColumn($sql_pallets,\Phalcon\Db::FETCH_ASSOC,['operation_id'=>2,'group_id'=>$gid]);
		 $result['first_package']=$this->db->fetchOne($sql_first_package,\Phalcon\Db::FETCH_ASSOC,['operation_id'=>17,'group_id'=>$gid]);
     return $result;
   }



	public function getUserInfo($token)
   {
      //print_r(\Phalcon\Di::getDefault()->getShared('db')); // This is the ugly way to grab the connection.
			$this->utf8init();
			$result=$this->db->fetchOne("SELECT * FROM active_user_sessions where access_token = :atoken",\Phalcon\Db::FETCH_ASSOC,['atoken'=>$token]);
      return $result;
   }

	 public function getlastGroup()
		 {
			 $this->utf8init();
			 $result=$this->db->fetchOne("SELECT * FROM groups order by timestmp desc LIMIT 1 ",\Phalcon\Db::FETCH_ASSOC,[]);
			 return $result;
		 }


	 public function createGropup($data, $uid)
    {
       //print_r(\Phalcon\Di::getDefault()->getShared('db')); // This is the ugly way to grab the connection.
 			$this->utf8init();
			$result=$this->db->query("INSERT INTO groups (group_number,  first_name, surname, foreman_name, foreman_surname, workshop, product_type, weight, pallet_capacity, series_capcity, labman_name, labman_surname, uid) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )",
			 array($data->group_number, $data->first_name, $data->surname, $data->foreman_name, $data->foreman_surname, $data->workshop, $data->product_type, $data->weight, $data->pallet_capacity, $data->series_capcity, $data->labman_name, $data->labman_surname, $uid));
			 return $result;
    }

		private function utf8init(){
			$this->db->query("SET NAMES 'utf8'");
			$this->db->query("SET CHARACTER SET 'utf8'");
			$this->db->query("SET SESSION collation_connection = 'utf8_general_ci'");
		}

}
