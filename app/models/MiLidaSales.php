<?php
namespace Models;

class MiLidaSales extends \Phalcon\Mvc\Model
{
    public $db;
    public function initialize()
    {
        $this->db=$this->getDi()->getShared('db');
    }

  
    public function getloginFormData()
    {
        $result=[];
        $sql_users=" SELECT CONCAT('oauth_user_', users.uid) AS id, users.uid AS uid,CONCAT(users.first_name,'  ',users.second_name) AS name FROM  users WHERE is_packer <1 order by users.first_name";
        $sql_workshops="SELECT * FROM workshops;";
        $this->utf8init();
        $result['users']=$this->db->fetchAll($sql_users, \Phalcon\Db::FETCH_ASSOC, []);
        $result['workshops']=$this->db->fetchAll($sql_workshops, \Phalcon\Db::FETCH_ASSOC, []);
        foreach ($result['users'] as $key=>$val) {
            $result['users'][$key]['workshops']=$this->getUserWorkshops($val['uid']);
        }
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
