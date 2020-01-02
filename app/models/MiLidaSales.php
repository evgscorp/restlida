<?php
namespace Models;

class MiLidaSales extends \Phalcon\Mvc\Model
{
    public $db;
    public function initialize()
    {
        $this->db=$this->getDi()->getShared('db');
        $this->utf8init();
    }

    // REST for Warehouse terminals
    public function getSalesJobsList($lid=21){
        $result=[];
        $sql_jobs="SELECT j.job_id, c.name_short as customer, j.plan_weight, j.plan_date  FROM fork.jobs j join customers c on j.customer_id = c.customer_id
            WHERE j.`status` = 20 and j.location_id =" . $lid;
        $this->utf8init();
        $result['jobs']=$this->db->fetchAll($sql_jobs, \Phalcon\Db::FETCH_ASSOC, []);
        return $result;

    }

    public function getSalesloginFormData(){
        $result=[];
        $sql_users="  SELECT CONCAT('oauth_user_', users.uid) AS id, users.uid AS uid,
        CONCAT(users.first_name,'  ',users.second_name) AS name
        FROM  users WHERE uid in (
        SELECT distinct uid FROM fork.user_role 
       where role_id in (101, 102))
        order by users.first_name";
        $sql_workshops="SELECT * FROM workshops;";
        $this->utf8init();
        $result['users']=$this->db->fetchAll($sql_users, \Phalcon\Db::FETCH_ASSOC, []);
       /* $result['workshops']=$this->db->fetchAll($sql_workshops, \Phalcon\Db::FETCH_ASSOC, []);
        foreach ($result['users'] as $key=>$val) {
            $result['users'][$key]['workshops']=$this->getUserWorkshops($val['uid']);
        }*/
        return $result;
    }

  public function getSalesJobsItems ($lid, $jid=null)

    {
        /* -- По дефолту $lid>0 => отдаем ВСЕ задания по ID склада
         * --- Если передан единственный job_id, => отдаем только его
         * --- Если передан массив job_id, => ораничиваем WHERE IN (...) 
         */
        $result=[];
        $sql="SELECT j.job_id, c.name_short as customer, j.plan_weight, j.plan_date  
                FROM fork.jobs j 
                join customers c on j.customer_id = c.customer_id
            WHERE j.status = 20 ";
        if ($lid>0){
            $sql .= " AND j.location_id = " . $lid;
        }
        if (is_array($jid)){
            $sql .= " AND j.job_id IN (". implode(",", $jid).")";
        } elseif (!empty($jid)){
            $sql .= " AND j.job_id =" . $jid;
        }

        $this->utf8init();
        $jobs=$this->db->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, []);
        foreach ($jobs as $key=>$job){
         
            $sql1 = "SELECT p.label_id, pa.pallet_code, la.UUID, se.series_name FROM packages p
            JOIN labels la on la.label_id = p.label_id
            JOIN pallets pa on pa.pallet_id = p.pallet_id
            JOIN series se on se.series_id = p.series_id
            WHERE p.series_id IN 
                (SELECT series_id FROM jobs_items WHERE job_id=" . $job['job_id'] .")";
            $series['series']=$this->db->fetchAll($sql1, \Phalcon\Db::FETCH_ASSOC, []);    
            $result["jobs"]["$key"] = array_merge_recursive($job, $series); 
        }
        return $result;
    }
    
    public function getSalesJobLock($jid)
    {
        $this->db->query("UPDATE jobs SET `status` = 30 WHERE job_id = ?", array($jid));
        return ['job'=>$this->db->fetchAll("SELECT `status` FROM jobs WHERE job_id = ?",\Phalcon\Db::FETCH_ASSOC, array($jid) )];
    }

    public function getSalesJobUnLock($jid)
    {
        $this->db->query("UPDATE jobs SET `status` = 20 WHERE job_id = ?", array($jid));
        return ['job'=>$this->db->fetchAll("SELECT `status` FROM jobs WHERE job_id = ?",\Phalcon\Db::FETCH_ASSOC, array($jid) )];
    }


    public function postJobsResults ($data)
    {
        $p = 0;
        $l = 0;
        $jobs = [];
        foreach ($data->rows as $row){
            if ($row->job_id) {
                if (!in_array($row->job_id,$jobs)){
                    $jobs[] = $row->job_id;
                }
            
               if ($row->UUID) {
                    $this->db->query("INSERT INTO jobs_results (job_id, label_id) SELECT ?, label_id FROM labels WHERE UUID=?",
                    array($row->job_id, $row->UUID));
                    $l = $l + 1;
                }
                if ($row->pallet_code) {
                    $this->db->query("INSERT INTO jobs_results (job_id, label_id) SELECT ?, label_id FROM packages WHERE pallet_id in
                    (SELECT pallet_id FROM pallets WHERE pallet_code = ?)",
                    array($row->job_id, $row->pallet_code) );
                    $p = $p + 1;
                }
            }
        }

        foreach ($jobs as $job){
            $this->db->query("UPDATE jobs SET `status` = 40 WHERE job_id = ?", array($job));
        }
        $result['pallet'] = $p;
        $result['labels'] = $l;
        $result['jobs'] = $jobs;
        return $result;
    }
    public function movePallets($data, $uid){
        $pallets = 0;
        foreach ($data->rows as $row){
            if ($row->pallet_id && $row->location_id) {
                $sql = "CALL move_pallet_ii($row->pallet_id, $row->location_id, $uid);";
                $this->db->query($sql);        
                $pallets++;   
            } else {
                error_log("Incorrect Tag supplied to movePallets");
                error_log( print_r($row), true);
            }
        }
        return ["pallets" => $pallets];
    }

    // -----------------------------------
    public function getSalesStorageLocations(){
        return ['data'=>$this->db->fetchAll("SELECT * FROM fork.locations where location_id >20 and  location_id <31", \Phalcon\Db::FETCH_ASSOC, [])];
    } 

    public function getCustomersList($valid = 1){
        $result=[];
        $sql_jobs="SELECT * FROM customers where valid = $valid";
        if (intval($valid) == 0)   $sql_jobs="SELECT * FROM customers";
        $this->utf8init();
        $result['customers']=$this->db->fetchAll($sql_jobs, \Phalcon\Db::FETCH_ASSOC, []);
        return $result;

    }

    public function getIPsList(){
        $sql = "SELECT * FROM probes_ip";
        $this->utf8init();
        $result['ips']=$this->db->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, []);
        return $result;

    }

    public function getProductsList(){
    
        $sql = "SELECT * FROM products;";
        $this->utf8init();
        $result['products']=$this->db->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, []);
        return $result;

    }

    public function getSalesDataJobs($jid=0, $locationId=null, $customerId=null, $statusId=null, $productId=null )
    {
        $result=[];
        $sql_jid='';
        if ($jid>0) $sql_jobs="SELECT j.*, s.status_text, c.name_full, sum(t.weight) as ship_weight FROM jobs j 
            LEFT OUTER JOIN job_statuses s on s.status_id =  j.status  
            LEFT OUTER JOIN customers c on c.customer_id =  j.customer_id  
            LEFT OUTER join jobs_items t on t.job_id = j.job_id  
            WHERE  j.job_id = ".$jid;
        else {
            $where = " WHERE j.job_id >0 ";
            if (!is_null($locationId)&&$locationId!='null') $where.=" AND j.location_id = ".$locationId;
            if (!is_null($customerId)&&$customerId!='null') $where.=" AND j.customer_id = ".$customerId;
            if (!is_null($statusId)&&$statusId!='null') $where.=" AND j.status = ".$statusId;
            if (!is_null($productId)&&$productId!='null') $where.=" AND j.product_id = ".$productId;
            
            
            $sql_jobs="SELECT j.*,  st.status_text, sum(s.weight) as task_weight from jobs j 
            LEFT OUTER join jobs_items s on s.job_id = j.job_id  
            LEFT OUTER JOIN job_statuses st on st.status_id =  j.status $where group by j.job_id LIMIT 1000";

        }
            $this->utf8init();
            $result['jobs']=$this->db->fetchAll($sql_jobs, \Phalcon\Db::FETCH_ASSOC, []);
            //$result['sql'] =  $sql_jobs;
            $result['jid'] =  $jid;
       
       /* $result['workshops']=$this->db->fetchAll($sql_workshops, \Phalcon\Db::FETCH_ASSOC, []);
        foreach ($result['users'] as $key=>$val) {
            $result['users'][$key]['workshops']=$this->getUserWorkshops($val['uid']);
        }*/
        return $result;
    }

    public function getShipmentReport($stdate, $etdate, $location='Склад КЦ', $customer=0){
        $sdate=date("Y-m-d",intval($stdate)).' 00:00:00';
        $edate=date("Y-m-d",intval($etdate)).' 23:59:59';
      
        $cfilter = "customer_id = $customer and ";
        $lfilter = "and location_short = '$location'";
        if ($customer<1){
            $cfilter = "";
        }
        if (strlen($location)<2){
            $lfilter = "";
        }
        
       // $sql = "SELECT *  FROM fork.shipment_report where $cfilter ship_stmp > CURDATE() - INTERVAL $days DAY $lfilter";
        $sql = "SELECT *  FROM fork.shipment_report where $cfilter  ship_stmp >= '$sdate' and ship_stmp <=  '$edate' $lfilter";
        //return ['sql'=>$sql];
        return ['data'=>$this->db->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, [])];
    }

    public function getSalesSeriesData($lid,$sname='', $ip = '',$jid = "0"){
        $like="";
        if (strlen($sname)>0) $like.= "and series_name LIKE '%$sname%'"; 
        if (strlen($ip)>0)  $like.= "and ip = $ip"; 
        if (intval($jid) > 0) $like.= " and product_id = (SELECT product_id FROM jobs where job_id = $jid)";
        //SELECT product_id FROM jobs where job_id = 48
        // andron
        //$sql ="SELECT * FROM sales_start where location_id = $lid $like limit 150";
	$sql ="SELECT * FROM sales_series where location_id = $lid $like and avail > 0 limit 150";
        return ['data'=>$this->db->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, [])];
    }

    public function getJobItems($jid){
        $sql = "SELECT i.*, ips.ip FROM jobs_items i 
        LEFT OUTER JOIN series s on s.series_id= i.series_id
        LEFT OUTER JOIN probes_ip ips on ips.id = s.ident_number where i.job_id = $jid";
        return ['data'=>$this->db->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, [])];
    }

    public function  saveCustomer($data){
        $this->utf8init();
        $this->db->query("INSERT INTO customers (`customer_id`, `unp`, `type`, `name_short`, `name_full`, `valid`) VALUES ( ?, ?, ?, ?, ?, ?)", 
        array(0, $data->unp, $data->ctype, $data->shortName, $data->fullName, 1  ));
    }

    public function  updateCustomer($data){
        $this->utf8init();
        $this->db->query("UPDATE customers SET  `unp` = $data->unp, `type`= '$data->ctype', `name_short` = '$data->shortName', `name_full` = '$data->fullName', `valid` =  $data->valid WHERE customer_id = ?", array(intval($data->id)));
    }



    public function saveDelivery($data){
		
		$this->db->query("INSERT INTO shipments (doc_number, doc_number2, driver_name, vh_number, job_id ) VALUES ( ?, ?, ?, ?, ?)", 
                   array($data->invoice,$data->invoice2, $data->fullName, $data->vehicle,$data->jid));
		$shid = $this->db->fetchColumn("SELECT min(ship_id) ship_id FROM shipments", [], 'ship_id');      
		$this->db->query("CALL make_shipping( $data->jid, $shid, @msg)");
		$st = $this->db->fetchOne("SELECT @msg as msg", \Phalcon\Db::FETCH_ASSOC, []);
		if ($st > 0) {
			$rt = ['status' => 'ok'];
		} else {
			$rt = ['status' => 'error'];
		}
	
	return $rt;      
    }

    public function  saveJob($data){
        $this->utf8init();
        $result=[];
        $this->db->query("INSERT INTO jobs (`job_id`, `customer_id`, `location_id`, `plan_weight`, `status`, `plan_date`, `rank`, `product_id`) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?)", 
        array(0, $data->customerId, $data->location?$data->location:null , $data->weight, 10, $data->sdate, $data->priority, $data->product?$data->product:null ));
      /* $this->db->query("CALL create_job($data->customerId, $data->weight, $data->location, 
        $data->product, $data->ip, $data->precise, '$data->sstdate', '$data->sedate', '$data->sdate', 
        $data->priority, @ID, @xmsg);", \Phalcon\Db::FETCH_ASSOC, []);
        $sql_res="SELECT @xmsg as msg, @ID as jid";
        $result['res']=$this->db->fetchOne($sql_res, \Phalcon\Db::FETCH_ASSOC, []);
        $result['sql']="CALL create_job($data->customerId, $data->weight, $data->location, $data->product, $data->ip, $data->precise, '$data->sstdate', '$data->sedate', '$data->sdate', $data->priority, @ID, @xmsg);";
      */  
        return $result['jid']= $this->db->lastInsertId();
       // return $result['res']="CALL create_job($data->customerId, $data->weight, $data->location, $data->product, $data->ip, $data->precise, '$data->sstdate', '$data->sedate', '$data->sdate', $data->priority);";
    }

    public function saveJobItem($data){
        $this->utf8init();
            if($data->jobId>0&&$data->seriesId>0){
                $this->db->query("DELETE FROM `jobs_items` WHERE (`job_id` = '$data->jobId') and (`series_id` = '$data->seriesId')");
                if ($data->delete!=true){
// andron	
//                   $this->db->query("INSERT INTO jobs_items (`job_id`, `series_id`, `weight`, `series_name`, `fact_weigh`) VALUES ( ?, ?, ?, ?, ?)", 
//                   array($data->jobId, $data->seriesId, $data->weight, $data->shortName, $data->orderedWeight ));
         	     $this->db->query("INSERT INTO jobs_items (`job_id`, `series_id`, `weight`, `series_name`) VALUES ( ?, ?, ?, ?)", 
                     array($data->jobId, $data->seriesId, $data->orderedWeight, $data->shortName  ));
                }

            }
        //INSERT INTO `fork`.`jobs_items` (`job_id`, `series_id`, `weight`, `series_name`, `fact_weigh`) VALUES ('1', '6203', '4000', '2019-СЦМ-81А', '0');
        /*
            {"delete":false,"weight":25,"orderedWeight":25,"jobId":"1","seriesId":"4019","shortName":"2018-СОМ-198A"}
        */
       
    }

    public function deleteJob($jid){
        if ($jid>0)
        $this->db->query("DELETE FROM jobs WHERE job_id=".$jid);
        return ['ok'];
    }

    public function  confirmJob($jid, $reverse=0){
        $status = 20;
        if ($reverse>0) $status = 10;
        if ($jid>0)
        $this->db->query("UPDATE jobs SET status='$status' WHERE job_id=".$jid);
        return ['ok'];

    }

    private function getUserWorkshops($uid)
    {
        $result=[];
        $res=$this->db->fetchAll("SELECT DISTINCT w.* FROM user_role ur LEFT OUTER JOIN workshops w on w.workshop_id = ur.workshop_id
        where uid=:uid", \Phalcon\Db::FETCH_ASSOC, ['uid'=>$uid]);
        foreach ($res as $val) {
            $result[$val['workshop_id']]=$val;
        }
        return $result;
    }
    private function utf8init()
    {
        $this->db->query("SET NAMES 'utf8'");
        $this->db->query("SET CHARACTER SET 'utf8'");
        $this->db->query("SET SESSION collation_connection = 'utf8_general_ci'");
    }
}