<?php
namespace Models;

class MiLidaSales extends \Phalcon\Mvc\Model
{
    public $db;
    public function initialize()
    {
        $this->db=$this->getDi()->getShared('db');
    }

    public function getCustomersList(){
        $result=[];
        $sql_jobs="SELECT * FROM customers";
        $this->utf8init();
        $result['customers']=$this->db->fetchAll($sql_jobs, \Phalcon\Db::FETCH_ASSOC, []);
        return $result;

    }

    public function getSalesDataJobs()
    {
        $result=[];
        $sql_jobs="SELECT * FROM jobs;";
        $this->utf8init();
        $result['jobs']=$this->db->fetchAll($sql_jobs, \Phalcon\Db::FETCH_ASSOC, []);
       /* $result['workshops']=$this->db->fetchAll($sql_workshops, \Phalcon\Db::FETCH_ASSOC, []);
        foreach ($result['users'] as $key=>$val) {
            $result['users'][$key]['workshops']=$this->getUserWorkshops($val['uid']);
        }*/
        return $result;
    }

    public function  saveCustomer($data){
        $this->utf8init();
        $this->db->query("INSERT INTO customers (`customer_id`, `unp`, `type`, `name_short`, `name_full`, `valid`) VALUES ( ?, ?, ?, ?, ?, ?)", 
        array(0, $data->unp, $data->ctype, $data->shortName, $data->fullName, 1  ));
    }

    public function  saveJob($data){
        $this->utf8init();
        /**
         *    weight: number;
         *   pdate: Date;
         *   priority: number;
         *   customerId: number;
         *
         INSERT INTO `fork`.`jobs` (`job_id`, `customer_id`, `location_id`, `plan_weight`, `status`, `plan_date`, `rank`) 
         VALUES (0, '4', '3', '4000', '1', '2019-05-05 00:00:00', '3');
         *  VALUES ('4', '4', '3', '', '', '4000', '1', '2019-05-05 00:00:00', '3');

         */
        $this->db->query("INSERT INTO jobs (`job_id`, `customer_id`, `location_id`, `plan_weight`, `status`, `plan_date`, `rank`) VALUES ( ?, ?, ?, ?, ?, ?, ?)", 
        array(0, $data->customerId, 1, $data->weight, 1, $data->sdate, $data->priority ));
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
