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

    public function getSalesStorageLocations(){
        return ['data'=>$this->db->fetchAll("SELECT * FROM fork.locations where location_id >20 and  location_id <31", \Phalcon\Db::FETCH_ASSOC, [])];
    } 

    public function getCustomersList(){
        $result=[];
        $sql_jobs="SELECT * FROM customers";
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

    public function getSalesDataJobs($jid=0)
    {
        $result=[];
        $sql_jid='';
        if ($jid>0) $sql_jobs="SELECT j.*, s.status_text, c.name_full FROM jobs j 
        LEFT OUTER JOIN job_statuses s on s.status_id =  j.status  
        LEFT OUTER JOIN customers c on c.customer_id =  j.customer_id  
        WHERE  j.job_id = ".$jid;
        else $sql_jobs="SELECT j.*,  st.status_text, sum(s.weight) as task_weight from jobs j 
        LEFT OUTER join jobs_items s on s.job_id = j.job_id  
        LEFT OUTER JOIN job_statuses st on st.status_id =  j.status  group by j.job_id LIMIT 1000";
        $this->utf8init();
        $result['jobs']=$this->db->fetchAll($sql_jobs, \Phalcon\Db::FETCH_ASSOC, []);
        $result['jid'] =  $jid;
       /* $result['workshops']=$this->db->fetchAll($sql_workshops, \Phalcon\Db::FETCH_ASSOC, []);
        foreach ($result['users'] as $key=>$val) {
            $result['users'][$key]['workshops']=$this->getUserWorkshops($val['uid']);
        }*/
        return $result;
    }

    public function getSalesSeriesData($lid,$sname='', $ip = ''){
        $like="";
        if (strlen($sname)>0) $like.= "and series_name LIKE '%$sname%'"; 
        if (strlen($ip)>0)  $like.= "and ip LIKE '%$ip%'"; 
        
        $sql ="SELECT * FROM sales_start where location_id = $lid $like limit 150";
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

    public function  saveJob($data){
        $this->utf8init();
        $result=[];
      //  $this->db->query("INSERT INTO jobs (`job_id`, `customer_id`, `location_id`, `plan_weight`, `status`, `plan_date`, `rank`) VALUES ( ?, ?, ?, ?, ?, ?, ?)", 
      //  array(0, $data->customerId, $data->location_id?$data->location_id:null , $data->weight, 10, $data->sdate, $data->priority ));
        return $result['res']=$this->db->fetchAll("CALL create_job($data->customerId, $data->weight,  
        $data->location_id, $data->location_id, $data->product, $data->ip, $data->precise,
        '$data->sstdate','$data->sedate','$data->sdate', $data->priority);", \Phalcon\Db::FETCH_ASSOC, []);
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
