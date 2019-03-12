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
