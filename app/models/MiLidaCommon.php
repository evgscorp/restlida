<?php
namespace Models;

class MiLidaCommon extends \Phalcon\Mvc\Model {
	public $db;
 public function initialize()
  {
     $this->db=$this->getDi()->getShared('db');
  }

 public function getShiftSuggestionsInfo(){
	 $sql_min_serises_num="SELECT max(series_num)+1 cnt FROM milida.series";
	 $result=$this->db->fetchOne("SELECT * FROM groups order by timestmp desc LIMIT 1 ",\Phalcon\Db::FETCH_ASSOC,[]);
	 $result['min_serises_num']=$this->db->fetchColumn($sql_min_serises_num,'cnt');
	return $result;
 }

	public function getShiftProductionInfo($gid)
   {
		 $sql_packages="SELECT count(*) cnt FROM packages_info where group_id in (select group_id from groups where shift_id=:shift_id)";
		 $sql_packages_by_product="SELECT count(pi.idpackage) idpackage, g.product_type FROM packages_info pi left outer join groups g on g.group_id=pi.group_id where pi.group_id in (select group_id from groups  where shift_id=:shift_id)   group by product_type";
		 $sql_packages_passed="SELECT count(*) cnt FROM packages_info where group_id in (select group_id from groups where shift_id=:shift_id) and operation_id =:operation_id";
		 $sql_packages_passed_by_product="SELECT count(pi.idpackage) cnt, g.product_type FROM packages_info pi left outer join groups g on g.group_id=pi.group_id where pi.group_id in (select group_id from groups  where shift_id=:shift_id) and operation_id=:operation_id  group by product_type";
		 $sql_pallets="SELECT count(d.pallet_id) cnt FROM (SELECT pallet_id FROM packages_info where group_id in (select group_id from groups where shift_id=:shift_id) and pallet_id >0 group by pallet_id ) d";
		 $sql_pallets_by_product="SELECT count(t.pallet_id) cnt, t.product_type from (SELECT pi.pallet_id, g.product_type FROM packages_info pi left outer join groups g on g.group_id=pi.group_id where pi.group_id in (select group_id from groups  where shift_id=:shift_id) and  pallet_id>0 group by product_type, pallet_id ) t group by product_type";
		 $sql_pallets_passed="SELECT count(d.pallet_id) cnt FROM (SELECT pallet_id FROM packages_info where group_id in (select group_id from groups where shift_id=:shift_id) and operation_id =:operation_id and pallet_id >0 group by pallet_id ) d";
		 $sql_pallets_passed_by_product="SELECT count(t.pallet_id) cnt, t.product_type from (SELECT pi.pallet_id, g.product_type FROM packages_info pi left outer join groups g on g.group_id=pi.group_id where pi.group_id in (select group_id from groups  where shift_id=:shift_id) and operation_id=:operation_id and pallet_id>0 group by product_type, pallet_id ) t group by product_type";
		 $sql_first_package="SELECT timestmp FROM packages_info where group_id in (select group_id from groups where shift_id=:shift_id)  order by timestmp desc limit 1";
		 $sql_last_package="SELECT p.*, IFNULL(ss.series_num,0) series, pl.UUID FROM packages p left outer join preloaded_labels pl on pl.label_id = p.label_id left outer join series ss on ss.series_id = p.series_id where group_id in (select group_id from shifts where shift_id=:shift_id)  order by idpackage desc limit 1";
		 $sql_all_series="SELECT GROUP_CONCAT(sr.series_num SEPARATOR ', ') allseries from (SELECT s.series_num FROM packages p left outer join series s on s.series_id=p.series_id  where p.group_id in (select group_id from groups where shift_id=:shift_id) group by series_num ) sr";
		 $sql_current_series="select count(*) produced, (select quantity from series  order by series_id desc limit 1) planned, (select series_num from series  order by series_id desc limit 1) snum from  packages p where p.series_id = (select max(series_id) from series)";
		 $sql_last_series="select (select quantity from series  order by series_id desc limit 1) planned, (select series_num from series  order by series_id desc limit 1) snum from  dual";
		 //SELECT p.series_id, s.series_num FROM milida.packages p left outer join series s on s.series_id=p.series_id  where p.group_id in (select group_id from groups where shift_id=8) group by series_id, series_num

		 $this->utf8init();
		 $result['shift_info']=$this->getShiftInfo($gid);
		 $shid=$result['shift_info']['shift_id'];
		 $result['packages_produced_by_product']=$this->db->fetchAll($sql_packages_by_product,\Phalcon\Db::FETCH_ASSOC,['shift_id'=>$shid]);
		 $result['packages_produced']=$this->db->fetchColumn($sql_packages,['shift_id'=>$shid],'cnt');
		 $result['packages_passed']=$this->db->fetchColumn($sql_packages_passed,['operation_id'=>4,'shift_id'=>$shid],'cnt');
		 $result['packages_passed_by_product']=$this->db->fetchAll( $sql_packages_passed_by_product,\Phalcon\Db::FETCH_ASSOC,['operation_id'=>4,'shift_id'=>$shid]);
		 $result['pallets_produced']=$this->db->fetchColumn($sql_pallets,['shift_id'=>$shid],'cnt');
		 $result['pallets_produced_by_product']=$this->db->fetchAll($sql_pallets_by_product,\Phalcon\Db::FETCH_ASSOC,['shift_id'=>$shid]);
	   $result['pallets_passed']=$this->db->fetchColumn($sql_pallets_passed,['operation_id'=>4,'shift_id'=>$shid],'cnt');
		 $result['pallets_passed_by_product']=$this->db->fetchAll($sql_pallets_passed_by_product,\Phalcon\Db::FETCH_ASSOC,['operation_id'=>4,'shift_id'=>$shid]);
		 $result['first_package']=$this->db->fetchColumn($sql_first_package,['shift_id'=>$shid],'timestmp');
		 $result['last_package']=$this->db->fetchOne($sql_last_package,\Phalcon\Db::FETCH_ASSOC,['shift_id'=>$shid]);
		 $result['all_series']=$this->db->fetchColumn($sql_all_series,['shift_id'=>$shid],'allseries');
		 $result['current_series']=$this->db->fetchOne($sql_current_series,\Phalcon\Db::FETCH_ASSOC,[]);
		 $result['last_series']=$this->db->fetchOne($sql_last_series,\Phalcon\Db::FETCH_ASSOC,[]);


     return $result;
   }

	 private function getShiftInfo($gid){
  		 $shid=$this->db->fetchOne("SELECT * FROM groups  where group_id =:group_id",\Phalcon\Db::FETCH_ASSOC,['group_id'=>$gid]);
			 $result=$this->db->fetchOne("SELECT s.*, u.firstname, u.lastname FROM shifts s left outer join users u on u.uid=s.uid  where shift_id =:shift_id ",\Phalcon\Db::FETCH_ASSOC,['shift_id'=>$shid['shift_id']]);
		return $result;
	 }

	public function getUserInfo($token)
   {
      //print_r(\Phalcon\Di::getDefault()->getShared('db')); // This is the ugly way to grab the connection.
			$this->utf8init();
			//$result=$this->db->fetchOne("SELECT * FROM active_user_sessions where access_token = :atoken",\Phalcon\Db::FETCH_ASSOC,['atoken'=>$token]);
			$result=$this->db->fetchOne("SELECT * FROM active_user_sessions where access_token = :atoken",\Phalcon\Db::FETCH_ASSOC,['atoken'=>$token]);


      return $result;
   }

	 public function getlastGroup()
		 {
			 $this->utf8init();
			 $result=$this->db->fetchOne("SELECT * FROM groups order by timestmp desc LIMIT 1 ",\Phalcon\Db::FETCH_ASSOC,[]);
			 return $result;
		 }

	 public function getProbeData($serach)
  		 {
  			 $this->utf8init();
				 $sql="SELECT p.*, s.series_num FROM probes p left outer join series s on s.series_id=p.seriesId where s.series_num = :snum LIMIT 1";
  			 $result=$this->db->fetchOne($sql,\Phalcon\Db::FETCH_ASSOC,['snum'=>intval($serach)]);
  			 return $result;
  		 }


	public function getShiftbyDate($date,$action, $shid){
		//$timestmp= strtotime(str_replace('/', '.', $date));
		$timestmp=$date;
		$res['status']=0;
		$res['reportData']=[];
		$res['shiftProductionInfo']=[];
		$sql="SELECT * from (SELECT *, from_unixtime(UNIX_TIMESTAMP(startstmp),'%Y-%m-%d') shd,  from_unixtime(:timestmp, '%Y-%m-%d') cd FROM shifts ) s where s.cd=s.shd order by s.shift_id limit 1";
		$result=$this->db->fetchOne($sql,\Phalcon\Db::FETCH_ASSOC,['timestmp'=>intval($timestmp)]);
		if (isset($result['shift_id'])&&$result['shift_id']>0){
			$gid=$this->db->fetchColumn("SELECT min(group_id) gid FROM groups where shift_id=:shift_id",['shift_id'=>$result['shift_id']],'gid');
			if ($gid>0){
				$res['status']=1;
				$res['reportData']=$this->db->fetchOne("SELECT * FROM groups where group_id = :group_id LIMIT 1 ",\Phalcon\Db::FETCH_ASSOC,['group_id'=>$gid]);
				$res['shiftProductionInfo']= $this->getShiftProductionInfo($gid);
			}
		}
		return $res;
	 }


	 public function createGropup($data, $uid)
    {
       //print_r(\Phalcon\Di::getDefault()->getShared('db')); // This is the ugly way to grab the connection.
 			$this->utf8init();
			$shid=$this->get_shift_id($data,$uid);
			$result=$this->db->query("INSERT INTO groups (group_number,  first_name, surname, foreman_name, foreman_surname, workshop, product_type, weight, pallet_capacity, series_capcity, labman_name, labman_surname, uid, shift_id) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )",
			 array($data->group_number, $data->first_name, $data->surname, $data->foreman_name, $data->foreman_surname, $data->workshop, $data->product_type, $data->weight, $data->pallet_capacity, $data->series_capcity, $data->labman_name, $data->labman_surname, $uid,$shid));

			 return $shid;
    }

		public function createShift($pdata, $uid)
     {
			$shiftNum=1;
			$hh=intval(date("H"));
			if ($hh>20||$hh<8) $shiftNum=2;
		 	$this->utf8init();
			$this->db->query("INSERT INTO shifts (shift_number,uid) VALUES ( ?, ?)", array($shiftNum, $uid));
			$shid=$this->get_shift_id(null,$uid);
			$data=(object)$this->getlastGroup();
			$result=$this->db->query("INSERT INTO groups (group_number,  first_name, surname, foreman_name, foreman_surname, workshop, product_type, weight, pallet_capacity, series_capcity, labman_name, labman_surname, uid, shift_id) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )",
 			 array($data->group_number, $data->first_name, $data->surname, $data->foreman_name, $data->foreman_surname,$pdata->workshop, $data->product_type, $data->weight, $data->pallet_capacity, $data->series_capcity, $data->labman_name, $data->labman_surname, $uid,$shid));

 			 return $result;
     }


		public function createProbe($data, $uid)
	 	{
	 		 //print_r(\Phalcon\Di::getDefault()->getShared('db')); // This is the ugly way to grab the connection.
	 		$this->utf8init();
			if (isset($data->seriesId)&&($data->seriesId>0)){

				$query ="UPDATE probes SET fat=?, moisture=?, como=?, protein=?, acidity=?, milkAcidity=?,
						 purityLevel=?, solubility=?, enterobacteria=?, enterococci=?, koe=?, yeast=?, bgkp=?,
						 expirationTime=?, storingRequirement=?, timestmp='', uid=?, labman=? WHERE seriesId = ?";

				$result = $this->db->query($query,array(
					$data->fat,
					$data->moisture,
					$data->como,
					$data->protein,
					$data->acidity,
					$data->milkAcidity,
					$data->purityLevel,
					$data->solubility,
					$data->enterobacteria,
					$data->enterococci,
					$data->koe,
					$data->yeast,
					$data->bgkp,
					$data->expirationTime,
					$data->storingRequirement,
					$uid,
					$data->labman,
					$data->seriesId,
				));


			} else {
			$sql="SELECT s.series_id FROM series s where s.series_num = :snum LIMIT 1";
 			$seriesId=0+$this->db->fetchColumn($sql,['snum'=>$data->series_num],'series_id');
 			if ($seriesId>0){
	 		$result=$this->db->query("INSERT INTO probes (`seriesId`, `fat`, `moisture`, `como`, `protein`, `acidity`, `milkAcidity`, `purityLevel`, `solubility`, `enterobacteria`, `enterococci`, `koe`, `yeast`, `bgkp`, `expirationTime`, `storingRequirement`, `uid`, `labman`)
			VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )",
			array($seriesId,
				$data->fat,
				$data->moisture,
				$data->como,
				$data->protein,
				$data->acidity,
				$data->milkAcidity,
				$data->purityLevel,
				$data->solubility,
				$data->enterobacteria,
				$data->enterococci,
				$data->koe,
				$data->yeast,
				$data->bgkp,
				$data->expirationTime,
				$data->storingRequirement,
				$uid,
				$data->labman,
			));
		}
	}

	 	}


		private function get_shift_id($data,$uid) {
			if (isset($data->new_shift)&&$data->new_shift==1){
				//INSERT INTO `milida`.`shifts` (`shift_id`, `startstmp`, `shift_number`, `uid`) VALUES ('2', '', '1', '2');
				$this->db->query("INSERT INTO shifts (shift_number,uid) VALUES ( ?, ?)", array(1, $uid));
			}
			 $result=$this->db->fetchOne("SELECT * FROM shifts order by startstmp desc LIMIT 1 ",\Phalcon\Db::FETCH_ASSOC,[]);
			return  $result['shift_id'];
		}

		private function utf8init(){
			$this->db->query("SET NAMES 'utf8'");
			$this->db->query("SET CHARACTER SET 'utf8'");
			$this->db->query("SET SESSION collation_connection = 'utf8_general_ci'");
		}

}
