<?php
namespace Models;

class MiLidaCommon extends \Phalcon\Mvc\Model
{
    public $db;
    public function initialize()
    {
        $this->db=$this->getDi()->getShared('db');
    }

    public function getSentPallets($wid,$shipment="0")
    {
        /*$sql="SELECT p.*, s.*, pp.pallet_code, pp.creation_time, ll.*
            	from (SELECT count(*) cnt, pallet_id, mp.location_id  FROM packages mp
            	where  mp.location_id > :lid and location_id < 33
              and mp.location_id in (SELECT allowed_location as location_id FROM move_rules
                where workshop_id in (select workshop_id from workshops where parent_workshop_id = :wid))
            	group by pallet_id, mp.location_id ) p
            left outer join series s on s.series_id = (select max(series_id) from packages where pallet_id=p.pallet_id)
            left outer join pallets pp on p.pallet_id=pp.pallet_id
            left outer join locations ll on ll.location_id=p.location_id
            order by creation_time desc";*/

         $sql="SELECT p.*, s.*, pp.pallet_code, pp.creation_time, ll.*
            	from (SELECT count(*) cnt, pallet_id, mp.location_id  FROM packages mp
            	 where pallet_id is not null and pallet_id in
                 ( SELECT pallet_id FROM overview_by_location where location_id >:lid and  location_id < :mlid )
            	group by pallet_id, mp.location_id ) p
            left outer join series s on s.series_id = (select max(series_id) from packages where pallet_id=p.pallet_id)
            left outer join pallets pp on p.pallet_id=pp.pallet_id
            left outer join locations ll on ll.location_id=p.location_id
            order by creation_time desc";


        /*$sql_cnt_pallets="SELECT count(*) cnt FROM packages where location_id >:lid and  location_id < 34
        and location_id in (SELECT allowed_location as location_id FROM move_rules
           where workshop_id in (select workshop_id from workshops where parent_workshop_id = :wid))";*/

        $sql_cnt_pallets="SELECT count(*) cnt FROM packages
        where pallet_id is not null and pallet_id in ( SELECT pallet_id FROM overview_by_location where location_id >:lid and  location_id < :mlid )";

         $lid=10;
         $mlid=20;
        if ($shipment!="0") {$lid=30; $mlid=40;};
        $sql_locations="SELECT * FROM locations where location_id > 20 and location_id < 40";
        $this->utf8init();
        $result['cnt']=$this->db->fetchColumn($sql_cnt_pallets, ['lid'=>$lid,'mlid'=>$mlid], 'cnt');
        $result['locations']=$this->db->fetchAll($sql_locations, \Phalcon\Db::FETCH_ASSOC, []);
        $result['pallets']=[];
        if ($result['cnt']>0) {
            $result['pallets']=$this->db->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, ['lid'=>$lid,'mlid'=>$mlid]);
        }
        $result['shipmentStatus']=$shipment;
        return $result;
    }

    public function getPackageLog($search)
    {
        $sql="SELECT l.message, l.op_stmp, l.operation_id, p.label_id, pl.UUID
            FROM operation_log l left join packages p on p.label_id= l.label_id
            left join labels pl on p.label_id= pl.label_id
            WHERE UUID LIKE(:search) order by l.op_stmp";
        $this->utf8init();
        return $this->db->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, ['search'=>$search]);
    }

    public function getSeriesPackages($search, $stype="all", $selproduct, $year, $wid)
    {
        $sql_search_series="SELECT * FROM fork.series s.series_num=:sid and s.series_year=:year and s.product_id=:selproduct";
        $sql_info_by_series="SELECT 1 AS row_number, u.first_name foremanfirstname , u.second_name foremanlastname,  p.prod_stmp ptime, sh.*, l.*, p.*, g.*, s.*, pl.*, prd.product_short, lc.location_name FROM packages p
												LEFT OUTER JOIN series s on p.series_id = s.series_id
												left outer join groups g on g.series_id=s.series_id and g.group_id= p.group_id
												LEFT OUTER JOIN labels l on p.label_id = l.label_id
												LEFT OUTER JOIN pallets pl on p.pallet_id = pl.pallet_id
												LEFT OUTER JOIN shifts sh on sh.shift_id=g.shift_id
												LEFT OUTER JOIN users u on sh.uid=u.uid
                        LEFT OUTER JOIN products prd on prd.product_id=s.product_id
                        LEFT OUTER JOIN locations lc on lc.location_id=p.location_id
												where s.series_num=:sid and s.series_year=:year and s.product_id=:selproduct and s.workshop_id=:wid  order by l.label_id LIMIT 1";
        $sql_info_by_package="SELECT 1 AS row_number, u.first_name foremanfirstname , u.second_name foremanlastname,  p.prod_stmp ptime, sh.*, l.*, p.*, g.*, s.*, pl.*, prd.product_short, lc.location_name FROM packages p
												LEFT OUTER JOIN series s on p.series_id = s.series_id
												left outer join groups g on g.series_id=s.series_id and g.group_id= p.group_id
												LEFT OUTER JOIN labels l on p.label_id = l.label_id
												LEFT OUTER JOIN pallets pl on p.pallet_id = pl.pallet_id
												LEFT OUTER JOIN shifts sh on sh.shift_id=g.shift_id
												LEFT OUTER JOIN users u on sh.uid=u.uid
                        LEFT OUTER JOIN products prd on prd.product_id=s.product_id
                        LEFT OUTER JOIN locations lc on lc.location_id=p.location_id
												where (l.UUID=:uuid OR CAST(l.h_number AS UNSIGNED)=:uuid ) and s.product_id=:selproduct and s.workshop_id=:wid order by l.label_id LIMIT 1";
        $sql_packages="SELECT @row_number:=@row_number+1 AS row_number, u.first_name foremanfirstname , u.second_name foremanlastname,  p.prod_stmp ptime, sh.*, l.*, p.*, g.*, s.*, pl.*, prd.product_short, lc.location_name FROM packages p
												LEFT OUTER JOIN series s on p.series_id = s.series_id
												left outer join groups g on g.series_id=s.series_id and g.group_id= p.group_id
												LEFT OUTER JOIN labels l on p.label_id = l.label_id
												LEFT OUTER JOIN pallets pl on p.pallet_id = pl.pallet_id
												LEFT OUTER JOIN shifts sh on sh.shift_id=g.shift_id
												LEFT OUTER JOIN users u on sh.uid=u.uid
                        LEFT OUTER JOIN products prd on prd.product_id=s.product_id
                        LEFT OUTER JOIN locations lc on lc.location_id=p.location_id
												where s.series_num=:sid  and s.series_year=:year and s.product_id=:selproduct and s.workshop_id=:wid order by p.prod_stmp ";
        $this->utf8init();
        $this->db->query("SET @row_number:=0;");
        if ($stype=='all'||$stype=='series') {
            $result['series']=$this->db->fetchOne($sql_info_by_series, \Phalcon\Db::FETCH_ASSOC, ['sid'=>$search,'year'=>$year,'selproduct'=>$selproduct, 'wid'=>$wid]);
        }
        if (!isset($result['series']['label_id'])||$result['series']['label_id']<1) {
            if ($stype=='all'||$stype=='packages') {
                $result['series']=$this->db->fetchOne($sql_info_by_package, \Phalcon\Db::FETCH_ASSOC, ['uuid'=>$search, 'selproduct'=>$selproduct, 'wid'=>$wid]);
                $result['packages']=[$result['series']];
            }
        } else {
            $result['packages']=$this->db->fetchAll($sql_packages, \Phalcon\Db::FETCH_ASSOC, ['sid'=>$search,'year'=>$year,'selproduct'=>$selproduct,'wid'=>$wid]);
        }
        return $result;
    }

    public function getStorageShiftReportInfo($date, $action, $shid)
    {
        /*$sql="SELECT p.idpackage, pl.operation_id, pa.sshid, g.product_type FROM milida.packages p
                     LEFT OUTER JOIN preloaded_labels pl on pl.label_id=p.label_id
                       LEFT OUTER JOIN pallets pa on pa.pallet_id=p.pallet_id
                       LEFT OUTER JOIN groups g on g.group_id=p.group_id";*/

        $shift_suffix=" AND pa.sshid=:sshid";
        $shift_delivery_suffix=" AND pa.dsshid=:sshid";

        $sql_total="SELECT count(*) cnt FROM packages p
							LEFT OUTER JOIN preloaded_labels pl on pl.label_id=p.label_id
							LEFT OUTER JOIN pallets pa on pa.pallet_id=p.pallet_id
							LEFT OUTER JOIN groups g on g.group_id=p.group_id WHERE pl.operation_id IN (:operation_id,:operation_id2)";

        $sql_shift_total=$sql_total.$shift_suffix;
        $sql_shift_delivery_total=$sql_total.$shift_delivery_suffix;


        $sql_weight_total="SELECT sum(g.weight)/1000 weight FROM packages p
							LEFT OUTER JOIN preloaded_labels pl on pl.label_id=p.label_id
							LEFT OUTER JOIN pallets pa on pa.pallet_id=p.pallet_id
							LEFT OUTER JOIN groups g on g.group_id=p.group_id WHERE pl.operation_id IN (:operation_id,:operation_id2)";

        $sql_shift_weight_total=$sql_weight_total.$shift_suffix;
        $sql_shift_delivery_weight_total=$sql_weight_total.$shift_delivery_suffix;


        $sql_series="SELECT DISTINCT s.series_num FROM packages p
								LEFT OUTER JOIN preloaded_labels pl on pl.label_id=p.label_id
								LEFT OUTER JOIN pallets pa on pa.pallet_id=p.pallet_id
								LEFT OUTER JOIN groups g on g.group_id=p.group_id
								LEFT OUTER JOIN series s on p.series_id=s.series_id
								WHERE s.series_num is not null AND pl.operation_id IN (:operation_id,:operation_id2)";

        $sql_shift_series=$sql_series.$shift_suffix;
        $sql_shift_delivery_series=$sql_series.$shift_delivery_suffix;


        $sql_shift_chart="SELECT count(t.idpackage) cnt, t.product_type, t.h from (
								SELECT p.idpackage, g.product_type, DATE_FORMAT(pa.storage_time, '%d.%m  %H ч.' ) h  FROM packages p
								LEFT OUTER JOIN preloaded_labels pl on pl.label_id=p.label_id
								LEFT OUTER JOIN pallets pa on pa.pallet_id=p.pallet_id
								LEFT OUTER JOIN groups g on g.group_id=p.group_id
								WHERE storage_time is not null AND pa.pallet_status IN (:operation_id,:operation_id2) AND pa.sshid=:sshid) t group by product_type, h order by h, product_type";

        $sql_shift_delivery_chart="SELECT count(t.idpackage) cnt, t.product_type, t.h from (
									SELECT p.idpackage, g.product_type, DATE_FORMAT(pa.storage_time, '%d.%m  %H ч.' ) h  FROM packages p
									LEFT OUTER JOIN preloaded_labels pl on pl.label_id=p.label_id
									LEFT OUTER JOIN pallets pa on pa.pallet_id=p.pallet_id
									LEFT OUTER JOIN groups g on g.group_id=p.group_id
									WHERE storage_time is not null AND pa.pallet_status = :operation_id AND pa.dsshid=:sshid) t group by product_type, h order by h, product_type";



        $sql_chart="SELECT count(t.idpackage) cnt, t.product_type, t.h from (
															SELECT p.idpackage, g.product_type, DATE_FORMAT(pa.storage_time, '%d.%m' ) h  FROM packages p
															LEFT OUTER JOIN preloaded_labels pl on pl.label_id=p.label_id
															LEFT OUTER JOIN pallets pa on pa.pallet_id=p.pallet_id
															LEFT OUTER JOIN groups g on g.group_id=p.group_id
															WHERE storage_time is not null AND pa.pallet_status=:operation_id) t group by product_type, h order by h, product_type LIMIT 60";

        $shift_info="SELECT s.*, u.firstname, u.lastname FROM storage_shifts s LEFT OUTER JOIN users u on u.uid=s.uid order by shift_id DESC LIMIT 1";
        $this->utf8init();
        if (intval($date)>1&&intval($shid)>1) {
            $timestmp=$date;
            $sql="SELECT s.*, u.firstname, u.lastname from (SELECT *, from_unixtime(UNIX_TIMESTAMP(startstmp),'%Y-%m-%d') shd,  from_unixtime(:timestmp, '%Y-%m-%d') cd FROM storage_shifts ) s
									LEFT OUTER JOIN users u on u.uid=s.uid where s.cd=s.shd and s.shift_id in (select r.shift_id  from (
									select sshid shift_id from pallets where sshid is not null
									union
									select dsshid shift_id from pallets where dsshid is not null ) r
									group by r.shift_id
									)  order by s.shift_id limit 1";
            $qoptions=['timestmp'=>intval($timestmp)];
            if ($action=="prev") {
                $sql="SELECT s.*, u.firstname, u.lastname FROM storage_shifts s  LEFT OUTER JOIN users u on u.uid=s.uid where shift_id < :shid  and shift_id in (select r.shift_id  from (
						select sshid shift_id from pallets where sshid is not null
						union
						select dsshid shift_id from pallets where dsshid is not null ) r
						group by r.shift_id
						)  order by shift_id desc limit 1";
                $qoptions=['shid'=>intval($shid)];
            } elseif ($action=="next") {
                $sql="SELECT s.*, u.firstname, u.lastname FROM storage_shifts s  LEFT OUTER JOIN users u on u.uid=s.uid where shift_id > :shid  and shift_id in (select r.shift_id  from (
					 select sshid shift_id from pallets where sshid is not null
					 union
					 select dsshid shift_id from pallets where dsshid is not null ) r
					 group by r.shift_id
					 )  order by shift_id limit 1";
                $qoptions=['shid'=>intval($shid)];
            }

            $db_result=$this->db->fetchOne($sql, \Phalcon\Db::FETCH_ASSOC, $qoptions);
            $result['shift_info']=$db_result;
        } else {
            $result['shift_info']=$this->db->fetchOne($shift_info, \Phalcon\Db::FETCH_ASSOC, []);
            $sql=$shift_info;
        }


        $this->utf8init();
        $result['shift_sql']=$sql;
        $result['shift_sql_options']=$qoptions;
        $shid=$result['shift_info']['shift_id'];
        $result['total_packages']=$this->db->fetchColumn($sql_total, ['operation_id'=>105,'operation_id2'=>105], 'cnt');
        $result['total_shift_packages']=$this->db->fetchColumn($sql_shift_total, ['operation_id'=>105,'operation_id2'=>105,'sshid'=>$shid], 'cnt');
        $result['total_shift_packages_delivered']=$this->db->fetchColumn($sql_shift_delivery_total, ['operation_id'=>10,'operation_id2'=>10,'sshid'=>$shid], 'cnt');
        $result['total_weight']=$this->db->fetchColumn($sql_weight_total, ['operation_id'=>105,'operation_id2'=>105], 'weight');
        $result['total_shift_weight']=$this->db->fetchColumn($sql_shift_weight_total, ['operation_id'=>105,'operation_id2'=>105,'sshid'=>$shid], 'weight');
        $result['total_shift_weight_delivered']=$this->db->fetchColumn($sql_shift_delivery_weight_total, ['operation_id'=>10,'operation_id2'=>10,'sshid'=>$shid], 'weight');
        $result['series']=$this->db->fetchAll($sql_series, \Phalcon\Db::FETCH_ASSOC, ['operation_id'=>105,'operation_id2'=>105]);
        $result['shift_series']=$this->db->fetchAll($sql_shift_series, \Phalcon\Db::FETCH_ASSOC, ['operation_id'=>105,'operation_id2'=>105,'sshid'=>$shid]);
        $result['shift_delivery_series']=$this->db->fetchAll($sql_shift_delivery_series, \Phalcon\Db::FETCH_ASSOC, ['operation_id'=>10,'operation_id2'=>10,'sshid'=>$shid]);
        $result['chart']=$this->db->fetchAll($sql_chart, \Phalcon\Db::FETCH_ASSOC, ['operation_id'=>105]);
        $result['chart']=$this->prepareGroupChart($result['chart'], 'h', 'product_type', 'cnt');
        $result['shift_chart']=$this->db->fetchAll($sql_shift_chart, \Phalcon\Db::FETCH_ASSOC, ['operation_id'=>105,'operation_id2'=>10,'sshid'=>$shid]);
        $result['shift_chart']=$this->prepareGroupChart($result['shift_chart'], 'h', 'product_type', 'cnt');
        $result['shift_delivery_chart']=$this->db->fetchAll($sql_shift_delivery_chart, \Phalcon\Db::FETCH_ASSOC, ['operation_id'=>10,'sshid'=>$shid]);
        $result['shift_delivery_chart']=$this->prepareGroupChart($result['shift_delivery_chart'], 'h', 'product_type', 'cnt');
        $result['next']=$this->db->fetchColumn("SELECT count(*) next FROM storage_shifts s  LEFT OUTER JOIN users u on u.uid=s.uid where shift_id > :shid  and shift_id in (select r.shift_id  from (
																								 select sshid shift_id from pallets where sshid is not null
																								 union
																								 select dsshid shift_id from pallets where dsshid is not null ) r
																								 group by r.shift_id
																								 )", ['shid'=>$shid], 'next');
        
        $result['prev']=$this->db->fetchColumn("SELECT count(*) prev FROM storage_shifts s  LEFT OUTER JOIN users u on u.uid=s.uid where shift_id < :shid  and shift_id in (select r.shift_id  from (
																								select sshid shift_id from pallets where sshid is not null
																								union
																								select dsshid shift_id from pallets where dsshid is not null ) r
																								group by r.shift_id
																								) ", ['shid'=>$shid], 'prev');



        return $result;
    }

    private function prepareGroupChart($arr, $gkey, $nkey, $vkey)
    {
        $result=[];
        $res=[];
        foreach ($arr as $row) {
            $res[$row[$gkey]][]=['name'=>$this->getProductShortName($row[$nkey]),'value'=>$row[$vkey]];
        }
        foreach ($res as $key=>$value) {
            if (count($value)==1) {
                if ($value[0]['name']==$this->getProductShortName('1')) {
                    $value[1]=['name'=>$this->getProductShortName('10'),'value'=>'0'];
                } else {
                    $value[1]=$value[0];
                    $value[0]=['name'=>$this->getProductShortName('1'),'value'=>'0'];
                }
            } elseif (!is_array($value)||count($value)<1) {
                $value[0]=['name'=>$this->getProductShortName('1'),'value'=>'0'];
                $value[0]=['name'=>$this->getProductShortName('10'),'value'=>'0'];
            }

            $result[]=[
            'name'=>$key,
            'series'=>$value
        ];
        }
        return $result;
    }

    private function getProductShortName($key)
    {
        $products['1']="СОМ";
        $products['10']="ЦСМ";
        if (!isset($products[$key])) {
            $key='1';
        }
        return  $products[$key];
    }

    public function getStorageOverview($wid)  {
          $this->utf8init();
          $sql="SELECT * FROM overview_by_location where workshop_id=:wid and pallet_code is not null";
        return $this->db->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, ['wid'=>$wid]);
      }

    public function getStorageOverviewDates($wid){
        $this->utf8init();
        $sql="SELECT UNIX_TIMESTAMP(DATE(series_date)) edate, datediff( DATE(series_date), NOW() )+365*2 dremains, sum(total) total  FROM overview_by_location 
        where workshop_id=:wid group by edate, dremains";
       return $this->db->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, ['wid'=>$wid]);
    }

    public function getUsersList()
    {   $this->utf8init();
        $result=$this->db->fetchAll("SELECT * FROM users", \Phalcon\Db::FETCH_ASSOC, []);
        foreach ($result as $key => $value) {
            $result[$key]['roles']=$this->db->fetchAll("SELECT DISTINCT ur.role_id, rr.role_name FROM  user_role ur
            left outer join roles rr on rr.role_id=ur.role_id
            where uid=:uid", \Phalcon\Db::FETCH_ASSOC, ['uid'=>$value['uid']]);
            $result[$key]['avalible_roles']=$this->db->fetchAll("SELECT DISTINCT ur.role_id, rr.role_name FROM  user_role ur
            left outer join roles rr on rr.role_id=ur.role_id", \Phalcon\Db::FETCH_ASSOC, []);
        
            $result[$key]['workshops']=$this->db->fetchAll("SELECT ww.workshop_id as wid, ww.* FROM  workshops ww", \Phalcon\Db::FETCH_ASSOC, []);
        
            $result[$key]['avalible_workshops']=$this->db->fetchAll("SELECT DISTINCT ur.workshop_id as wid, ww.* FROM  user_role ur
            left outer join workshops ww on ww.workshop_id=ur.workshop_id  where uid=:uid", \Phalcon\Db::FETCH_ASSOC, ['uid'=>$value['uid']]);
            
        }
        return $result;
    }

    public function getShiftSuggestionsInfo($wid){
        $result=$this->db->fetchOne("SELECT * FROM groups where workshop_id=:wid order by timestmp desc LIMIT 1 ", \Phalcon\Db::FETCH_ASSOC, ['wid'=>$wid]);
        $result['last_serises_data']=$this->db->fetchOne("SELECT series_num, series_year, amount, weight, product_id, series_id
          FROM series where workshop_id=:wid and series_id = (select max(series_id) from series where workshop_id=:wid and is_manual<1 )", \Phalcon\Db::FETCH_ASSOC, ['wid'=>$wid]);
        return $result;
    }

    public function getProductionData($wid, $sid=0, $shid=0)
    {

        $result['wid']=$wid;
        /*$sql="SELECT c.*, p.*, g.*, sh.*, pckg.prod_stmp, l.h_number, l.UUID  FROM current_programm c
            left outer join series s on c.series_id=s.series_id
            left outer join products p on p.product_id=s.product_id
            left outer join groups g on g.series_id=s.series_id and g.group_id= (select max(group_id) from groups where series_id = s.series_id and workshop_id=:wid)
            left outer join shifts sh on sh.shift_id=g.shift_id and sh.shift_id = (SELECT max(shift_id) from shifts where workshop_id = :wid)
            left outer join packages pckg on pckg.label_id=(select label_id from packages where series_id = s.series_id and workshop_id=:wid order by prod_stmp DESC LIMIT 1)
            left outer join labels l on pckg.label_id=l.label_id
             where c.workshop_id=:wid LIMIT 1";

        $sql="SELECT c.*, p.*, g.*, sh.*, pckg.prod_stmp, l.h_number, l.UUID  FROM fork.current_programm c
            left outer join fork.series s on c.series_id=s.series_id
            left outer join fork.products p on p.product_id=s.product_id
            left outer join fork.groups g on g.series_id=s.series_id and g.group_id= (select max(group_id) from fork.groups where series_id = s.series_id and workshop_id=:wid)
            left outer join fork.shifts sh on sh.shift_id=g.shift_id and sh.shift_id = (SELECT max(shift_id) from fork.shifts where workshop_id = :wid)
            left outer join fork.packages pckg on pckg.label_id=(select label_id from fork.packages where series_id = s.series_id and workshop_id=:wid order by prod_stmp DESC LIMIT 1)
            left outer join fork.labels l on pckg.label_id=l.label_id
             where c.workshop_id=:wid LIMIT 1";*/


        $sql="SELECT 	A.*
          		,p.product_id, p.product_name, p.product_short,
              sh.*, CONCAT(b.second_name, ' ', b.first_name) as master_name,g.packer_name as packer_name,pckg.prod_stmp
          		,l.UUID, l.h_number
          		FROM current_programm A
          						 left outer join series s on s.series_id = a.series_id
          						 left outer join products p on p.product_id=s.product_id
          						 left join shifts sh ON sh.workshop_id = a.workshop_id and sh.shift_id = (SELECT max(shift_id) from shifts where workshop_id = :wid)
                                   left join users b on b.uid = sh.uid
                                   left join groups g on g.series_id = a.series_id and g.group_id = (SELECT max(group_id) from groups where series_id = s.series_id and workshop_id = :wid)
                                   left join packages pckg on pckg.series_id = a.series_id and pckg.label_id = (select label_id from packages where series_id = a.series_id and workshop_id= :wid order by prod_stmp DESC LIMIT 1)
                                   and pckg.label_id = (select label_id from packages where series_id = a.series_id and workshop_id= :wid
                                     order by prod_stmp DESC LIMIT 1)
          						 left join labels l on pckg.label_id=l.label_id
          WHERE A.workshop_id = :wid  ORDER BY prod_stmp DESC,  startstmp DESC LIMIT 1";



        $sql_unsort="SELECT count(*) cnt FROM packages p WHERE p.workshop_id = :wid and p.series_id = -1";
        $sql_unsorted_packages="SELECT p.prod_stmp, l.* FROM packages p left outer join labels l on p.label_id=l.label_id
        WHERE p.workshop_id = :wid and p.series_id = -1";

        $sql_params=['wid'=>$wid];
        if ($sid>0) {
            $sql_params=['sid'=>$sid,'shid'=>$shid,'wid'=>$wid];
            $sql="SELECT c.*, p.*, g.*, sh.*, pckg.prod_stmp, l.h_number, l.UUID  FROM (
                            SELECT
                        `a`.`workshop_id` AS `workshop_id`,
                        MAX(`a`.`series_id`) AS `series_id`,
                        `a`.`series_name` AS `series_name`,
                        `a`.`amount` AS `amount`,
                        (SELECT
                                COUNT(0)
                            FROM
                                `packages` `b`
                            WHERE
                                (`b`.`series_id` = `a`.`series_id`)) AS `total`
                    FROM
                        `series` `a`
                    WHERE
                        (`a`.`is_manual` = 0 and `a`.`series_id`=:sid)
                    GROUP BY `a`.`workshop_id`
                  ) c
            left outer join series s on c.series_id=s.series_id
            left outer join products p on p.product_id=s.product_id
            left outer join groups g on g.series_id=s.series_id and g.group_id= (select max(group_id) from groups where series_id = s.series_id and shift_id=:shid)
            left outer join shifts sh on sh.shift_id=g.shift_id
            left outer join packages pckg on pckg.label_id=(select max(label_id) from packages where series_id = s.series_id)
            left outer join labels l on pckg.label_id=l.label_id
             where c.workshop_id=:wid LIMIT 1";
        }

        $sql_pallets="SELECT count(*) cnt , p.pallet_id, pp.pallet_code from packages p
        left outer join pallets pp on pp.pallet_id=p.pallet_id
        where p.series_id = :sid and p.pallet_id is not null  
        GROUP BY pallet_id, pallet_code  ORDER BY  p.pallet_id ASC";
        $sql_local_storage_info="SELECT * FROM overview_by_location  where workshop_id=:wid and location_id=:wid limit 150";
        $sql_passed_storages_info="SELECT DISTINCT location_id, location_name FROM overview_by_location  where workshop_id=:wid and location_id <>:lid and location_id between 11 and 20";
        $sql_external_storages_info="SELECT * FROM overview_by_location  where workshop_id=:wid and location_id=:lid limit 150";
        $sql_last_ten_series="SELECT series_id, series_name FROM series where workshop_id=:wid and series_id > 0 order by timestmp desc limit 15";
        $sql_manual_proramm="SELECT * FROM current_program_manual where workshop_id=:wid order by series_id desc limit 1";

        $this->utf8init();
        $result=$this->db->fetchOne($sql, \Phalcon\Db::FETCH_ASSOC, $sql_params);

        if ($result['series_id']>0) {
            $result['pallets']=$this->db->fetchAll($sql_pallets, \Phalcon\Db::FETCH_ASSOC, ['sid'=>$result['series_id']]);
            $result['unsorted_cnt']=$this->db->fetchColumn($sql_unsort, ['wid'=>$wid], 'cnt');
            $result['unsorted_packages']=$this->db->fetchAll($sql_unsorted_packages, \Phalcon\Db::FETCH_ASSOC, ['wid'=>$wid]);
            $result['manual_proramm']=$this->db->fetchOne($sql_manual_proramm, \Phalcon\Db::FETCH_ASSOC, ['wid'=>$wid]);
            $result['local_stroage']=$this->db->fetchAll($sql_local_storage_info, \Phalcon\Db::FETCH_ASSOC, ['wid'=>$wid]);
            $result['last_ten_series']=$this->db->fetchAll($sql_last_ten_series, \Phalcon\Db::FETCH_ASSOC, ['wid'=>$wid]);
            $result['passedto_locatons']=$this->db->fetchAll($sql_passed_storages_info, \Phalcon\Db::FETCH_ASSOC, ['wid'=>$wid,'lid'=>$wid]);
            foreach ($result['passedto_locatons'] as $key => $value) {
                $result['passedto_locatons'][$key]['pallets']=$this->db->fetchAll($sql_external_storages_info, \Phalcon\Db::FETCH_ASSOC, ['wid'=>$wid,'lid'=>$value['location_id']]);
            }
            if ($result['shift_id']>0) {
                $result['shift_series_products']=$this->getShiftProductionReportArea($result['shift_id'],$wid);
            }

        }


        return $result;
    }

    private function getShiftProductionReportArea($shid,$wid)
    {
        $sql_shift_series_products="SELECT count(*) cnt, SUM(s.weight) wtotal, s.series_name, pr.product_id, pr.product_short from packages p
                                  left outer join series s on s.series_id=p.series_id
                                  left outer join products pr on pr.product_id=s.product_id
                                  where p.workshop_id=:wid and pr.product_id >0 and s.series_id in (select distinct series_id from groups where shift_id=:shid)
                                  group by s.series_name, pr.product_id, pr.product_short";

    $sql_shift_series_products="SELECT count(*) cnt, SUM(s.weight) wtotal, s.series_name, pr.product_id, pr.product_short 
    from packages p
            left outer join series s on s.series_id=p.series_id
            left outer join products pr on pr.product_id=s.product_id
        where p.workshop_id=:wid and pr.product_id >0 and s.series_id in (select distinct series_id from groups where shift_id=:shid)
        and p.prod_stmp < COALESCE( (select startstmp from shifts where workshop_id=:wid and shift_id > :shid order by shift_id limit 1), '9999-12-31') 
                group by s.series_name, pr.product_id, pr.product_short";
    

        $res=$this->db->fetchAll($sql_shift_series_products, \Phalcon\Db::FETCH_ASSOC, ['wid'=>$wid,'shid'=>$shid]);
        $result=['prows'=>[],'products'=>[],'tweight'=>0,'tcnt'=>0];
        foreach ($res as $row) {
            $result['prows'][$row['product_id']][]=$row;
            $result['products'][intval($row['product_id'])]=['id'=>$row['product_id'],'name'=>$row['product_short']];
            $result['tweight']+=$row['wtotal'];
            $result['tcnt']+=$row['cnt'];
        }
        $result['products']=array_values($result['products']);

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
        $sql_first_package="SELECT timestmp FROM packages_info where group_id in (select group_id from groups where shift_id=:shift_id)  order by timestmp limit 1";
        $sql_last_package="SELECT p.*, IFNULL(ss.series_num,0) series, pl.UUID FROM packages p left outer join preloaded_labels pl on pl.label_id = p.label_id left outer join series ss on ss.series_id = p.series_id where group_id in (select group_id from shifts where shift_id=:shift_id)  order by idpackage desc limit 1";
        $sql_all_series="SELECT GROUP_CONCAT(sr.series_num SEPARATOR ', ') allseries from (SELECT s.series_num FROM packages p left outer join series s on s.series_id=p.series_id  where p.group_id in (select group_id from groups where shift_id=:shift_id) group by series_num ) sr";
        $sql_all_packers="SELECT GROUP_CONCAT(first_name SEPARATOR ', ') allpackers FROM groups where shift_id=:shift_id";
        $sql_current_series="select count(*) produced, (select quantity from series  order by series_id desc limit 1) planned, (select series_num from series  order by series_id desc limit 1) snum from  packages p where p.series_id = (select max(series_id) from series)";
        $sql_last_series="select (select quantity from series  order by series_id desc limit 1) planned, (select series_num from series  order by series_id desc limit 1) snum from  dual";
        $sql_chart_prod_per_hour="SELECT count(h) pkg, h from (SELECT DATE_FORMAT(p.timestmp, '%d.%m  %H ч.' ) h FROM packages p where group_id in (select group_id from groups where shift_id=:shift_id) ) pp group by h";
        $sql_avalible_labels="SELECT count(*) lcnt FROM preloaded_labels where operation_id>105";
        $sql_nopacked_packages="SELECT count(*) lcnt FROM packages_info where group_id in (select group_id from groups where shift_id=:shift_id) AND pallet_id is null";
        $sql_pallets_uncompleted="SELECT count(*) pcnt, pa.pallet_code FROM packages_info p left join pallets pa on pa.pallet_id=p.pallet_id
        WHERE group_id in (select group_id from groups where  shift_id=:shift_id) AND pa.pallet_code is not null GROUP BY p.pallet_id HAVING pcnt < :cnt";
        //SELECT p.series_id, s.series_num FROM milida.packages p left outer join series s on s.series_id=p.series_id  where p.group_id in (select group_id from groups where shift_id=8) group by series_id, series_num

        $this->utf8init();
        $result['shift_info']=$this->getShiftInfo($gid);
        $group=$this->getGroupInfo($gid);
        $shid=$result['shift_info']['shift_id'];
        $result['packages_produced_by_product']=$this->db->fetchAll($sql_packages_by_product, \Phalcon\Db::FETCH_ASSOC, ['shift_id'=>$shid]);
        $result['packages_produced']=$this->db->fetchColumn($sql_packages, ['shift_id'=>$shid], 'cnt');
        $result['packages_passed']=$this->db->fetchColumn($sql_packages_passed, ['operation_id'=>4,'shift_id'=>$shid], 'cnt');
        $result['packages_passed_by_product']=$this->db->fetchAll($sql_packages_passed_by_product, \Phalcon\Db::FETCH_ASSOC, ['operation_id'=>4,'shift_id'=>$shid]);
        $result['pallets_produced']=$this->db->fetchColumn($sql_pallets, ['shift_id'=>$shid], 'cnt');
        $result['pallets_produced_by_product']=$this->db->fetchAll($sql_pallets_by_product, \Phalcon\Db::FETCH_ASSOC, ['shift_id'=>$shid]);
        $result['pallets_passed']=$this->db->fetchColumn($sql_pallets_passed, ['operation_id'=>4,'shift_id'=>$shid], 'cnt');
        $result['pallets_passed_by_product']=$this->db->fetchAll($sql_pallets_passed_by_product, \Phalcon\Db::FETCH_ASSOC, ['operation_id'=>4,'shift_id'=>$shid]);
        $result['pallets_uncompleted']=$this->db->fetchAll($sql_pallets_uncompleted, \Phalcon\Db::FETCH_ASSOC, ['shift_id'=>$shid,'cnt'=>intval($group['pallet_capacity'])]);
        $result['first_package']=$this->db->fetchColumn($sql_first_package, ['shift_id'=>$shid], 'timestmp');
        $result['last_package']=$this->db->fetchOne($sql_last_package, \Phalcon\Db::FETCH_ASSOC, ['shift_id'=>$shid]);
        $result['all_series']=$this->db->fetchColumn($sql_all_series, ['shift_id'=>$shid], 'allseries');
        $result['all_packers']=$this->db->fetchColumn($sql_all_packers, ['shift_id'=>$shid], 'allpackers');
        $result['current_series']=$this->db->fetchOne($sql_current_series, \Phalcon\Db::FETCH_ASSOC, []);
        $result['labels_avalible']=$this->db->fetchOne($sql_nopacked_packages, \Phalcon\Db::FETCH_ASSOC, ['shift_id'=>$shid]);
        $result['last_series']=$this->db->fetchOne($sql_last_series, \Phalcon\Db::FETCH_ASSOC, []);
        $result['chart_prod_per_hour']=$this->db->fetchAll($sql_chart_prod_per_hour, \Phalcon\Db::FETCH_ASSOC, ['shift_id'=>$shid]);
        return $result;
    }

    private function getGroupInfo($gid)
    {
        return $this->db->fetchOne("SELECT * FROM groups  where group_id =:group_id", \Phalcon\Db::FETCH_ASSOC, ['group_id'=>$gid]);
    }


    private function getShiftInfo($gid)
    {
        $this->utf8init();
        $shid=$this->db->fetchOne("SELECT * FROM groups  where group_id =:group_id", \Phalcon\Db::FETCH_ASSOC, ['group_id'=>$gid]);
        $result=$this->db->fetchOne("SELECT s.*, u.firstname, u.lastname FROM shifts s left outer join users u on u.uid=s.uid  where shift_id =:shift_id ", \Phalcon\Db::FETCH_ASSOC, ['shift_id'=>$shid['shift_id']]);
        return $result;
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

    public function getSeriesFormData($wid)
    {
        $result=[];
        $sql_users=" SELECT CONCAT('oauth_user_', users.uid) AS id, users.uid AS uid,CONCAT(users.first_name,'  ',users.second_name) AS name FROM users WHERE is_packer =1";
        $sql_workshops="SELECT * FROM products;";
        $this->utf8init();
        $result['users']=$this->db->fetchAll($sql_users, \Phalcon\Db::FETCH_ASSOC, []);
        $result['products']=$this->db->fetchAll($sql_workshops, \Phalcon\Db::FETCH_ASSOC, []);
        $result['suggestion']=$this->getShiftSuggestionsInfo($wid);
        return $result;
    }

    public function getSummaryReport($stdate,$endate){
        $result=[];
        $sdate=date("Y-m-d",intval($stdate)).' 00:00:00';
        $edate=date("Y-m-d",intval($endate)).' 23:59:59';
        $result['sdateFormatted']=date("Y-m-d",intval($stdate)).' 00:00';
        $result['edateFormatted']=date("Y-m-d",intval($endate)).' 23:59';
        $result['report2'][1]=$this->db->fetchAll("CALL report_2(21);", \Phalcon\Db::FETCH_ASSOC, []);
        $result['report2'][2]=$this->db->fetchAll("CALL report_2(22);", \Phalcon\Db::FETCH_ASSOC, []);
        $result['report2'][3]=$this->db->fetchAll("CALL report_2(23);", \Phalcon\Db::FETCH_ASSOC, []);
        
        // $result['report2'][0]=$this->db->fetchAll("CALL report_2(0);", \Phalcon\Db::FETCH_ASSOC, []);
        
        $sql_workshops="SELECT * FROM workshops where workshop_id < 4;";
        $sql_storage_workshops="SELECT * FROM workshops where workshop_id between 20 and 30";
        $this->utf8init();
        $result["workshops"]=$this->db->fetchAll($sql_workshops, \Phalcon\Db::FETCH_ASSOC, []);
        //$result["storageWorkshops"]=$this->db->fetchAll($sql_storage_workshops, \Phalcon\Db::FETCH_ASSOC, []);
        $result['report1'][0]=$this->db->fetchAll("CALL report_1(0, '$sdate', '$edate');", \Phalcon\Db::FETCH_ASSOC, []);
        $result['report3'][0]=$this->db->fetchAll("CALL report_3(0, '$sdate', '$edate');", \Phalcon\Db::FETCH_ASSOC, []);
        
        foreach ($result["workshops"] as $row) {
            $sql_report_1="CALL report_1({$row['workshop_id']}, '$sdate', '$edate');";
            $sql_report_3="CALL report_3({$row['workshop_id']}, '$sdate', '$edate');";
            $result['report1'][$row['workshop_id']]=$this->db->fetchAll($sql_report_1, \Phalcon\Db::FETCH_ASSOC, []);
            $result['report3'][$row['workshop_id']]=$this->db->fetchAll($sql_report_3, \Phalcon\Db::FETCH_ASSOC, []);
        } 
        
      
        $result['report2'][0]=$this->db->fetchAll("CALL report_2(0);", \Phalcon\Db::FETCH_ASSOC, []);
       

        /* sorting results */
        foreach ($result['report1'] as $key => $value) {
            usort( $result['report1'][$key], function ($item1, $item2) {
                return $item1['product_id'] > $item2['product_id'];
            });
        }

        foreach ($result['report2'] as $key => $value) {
            usort( $result['report2'][$key], function ($item1, $item2) {
                return $item1['product_id'] > $item2['product_id'];
            });
        }

        foreach ($result['report3'] as $key => $value) {
            usort( $result['report3'][$key], function ($item1, $item2) {
                return $item1['product_id'] > $item2['product_id'];
            });
        }
        //$result['report2'][1]=$this->db->fetchAll("CALL report_2(21);", \Phalcon\Db::FETCH_ASSOC, []);
        //$result['report2'][2]=$this->db->fetchAll("CALL report_2(22);", \Phalcon\Db::FETCH_ASSOC, []);
        //$result['report2'][3]=$this->db->fetchAll("CALL report_2(23);", \Phalcon\Db::FETCH_ASSOC, []);
       
       /*
        foreach ($result["storageWorkshops"] as $srow) {
            $sql_report_2="CALL report_2(".$srow['workshop_id'].");";
            $result['report2'][$srow['workshop_id']]=$this->db->fetchAll($sql_report_2, \Phalcon\Db::FETCH_ASSOC, []);
        } */
        return $result;
    }


    public function getUserInfo($token)
    {
        //print_r(\Phalcon\Di::getDefault()->getShared('db')); // This is the ugly way to grab the connection.
        $this->utf8init();
        //$result=$this->db->fetchOne("SELECT * FROM active_user_sessions where access_token = :atoken",\Phalcon\Db::FETCH_ASSOC,['atoken'=>$token]);
        $result=$this->db->fetchOne("SELECT * FROM active_user_sessions where access_token = :atoken", \Phalcon\Db::FETCH_ASSOC, ['atoken'=>$token]);
        $roles=[];
        $workshops=[];

        if ($result['uid']>0) {
            $roles=$this->getUserRoles($result['uid']);
            $workshops=$this->getUserWorkshops($result['uid']);
        }
        $result['roles']=$roles;
        $result['workshops']=$workshops;

        return $result;
    }

    private function getUserRoles($uid)
    {
        $result=[];
        $res=$this->db->fetchAll("SELECT role_id from user_role where uid=:uid", \Phalcon\Db::FETCH_ASSOC, ['uid'=>$uid]);
        foreach ($res as $val) {
            $result[]=$val['role_id'];
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


    public function getlastGroup()
    {
        $this->utf8init();
        $result=$this->db->fetchOne("SELECT * FROM groups order by timestmp desc LIMIT 1 ", \Phalcon\Db::FETCH_ASSOC, []);

        return $result;
    }

    public function getProbeData($serach,$pid,$year)
    {
        $this->utf8init();
        // $sql="SELECT p.*, s.series_num FROM probes p left outer join series s on s.series_id=p.seriesId where s.series_num = :snum LIMIT 1";
        $sql="SELECT s.*,  p.product_name, p.product_short, pr.* FROM fork.series  s
        left outer join products p on p.product_id=s.product_id
        left outer join probes pr on pr.seriesId=s.series_id
         where s.product_id=:pid and s.series_num=:snum and s.series_year=:year LIMIT 1";
        $result=$this->db->fetchOne($sql, \Phalcon\Db::FETCH_ASSOC, ['snum'=>intval($serach),'pid'=>intval($pid),'year'=>intval($year)]);
        return $result;
    }


    public function getShiftbyDate($date, $action, $shid, $wid)
    {
        //$timestmp= strtotime(str_replace('/', '.', $date));
        $timestmp=$date;
        $res['status']=0;
        $res['reportData']=[];
        $res['shiftProductionInfo']=[];
        $sql="SELECT * from (SELECT *, from_unixtime(UNIX_TIMESTAMP(startstmp),'%Y-%m-%d') shd,  from_unixtime(:timestmp, '%Y-%m-%d') cd FROM shifts ) s where s.cd=s.shd and s.workshop_id=:wid order by s.shift_id limit 1";
        $qoptions=['timestmp'=>intval($timestmp),'wid'=>$wid];
        if ($action=="prev") {
            $sql="SELECT * FROM shifts where shift_id < :shid and workshop_id=:wid and shift_id in (select shift_id from groups group by shift_id ) order by shift_id desc limit 1";
            $qoptions=['shid'=>intval($shid),'wid'=>$wid];
        } elseif ($action=="next") {
            $sql="SELECT * FROM shifts where shift_id > :shid  and workshop_id=:wid and shift_id in (select shift_id from groups group by shift_id ) order by shift_id limit 1";
            $qoptions=['shid'=>intval($shid),'wid'=>$wid];
        }

        $result=$this->db->fetchOne($sql, \Phalcon\Db::FETCH_ASSOC, $qoptions);
        $res['shift_search']=$result;
        if (isset($result['shift_id'])&&$result['shift_id']>0) {
            $gid=$this->db->fetchColumn("SELECT max(group_id) gid FROM groups where shift_id=:shift_id  and workshop_id=:wid", ['shift_id'=>$result['shift_id'],'wid'=>$wid], 'gid');
            $sid=$this->db->fetchColumn("SELECT max(series_id) sid FROM groups where group_id=:group_id and workshop_id=:wid", ['group_id'=>$gid,'wid'=>$wid], 'sid');
            // $wid=$this->db->fetchColumn("SELECT workshop_id wid FROM groups where group_id=:group_id LIMIT 1", ['group_id'=>$gid], 'wid');

            if ($gid>0&&$wid>0&&$sid>0) {
                $res['status']=1;
                $res['group']=$this->db->fetchOne("SELECT * FROM groups where group_id = :group_id and workshop_id=:wid LIMIT 1 ", \Phalcon\Db::FETCH_ASSOC, ['group_id'=>$gid,'wid'=>$wid]);
                $res['productionData']= $this->getProductionData($wid, $sid, $result['shift_id']);
                /* $res['reportData']=$this->db->fetchOne("SELECT * FROM groups where group_id = :group_id LIMIT 1 ", \Phalcon\Db::FETCH_ASSOC, ['group_id'=>$gid]);
                 $res['shiftProductionInfo']= $this->getShiftProductionInfo($gid);
                 $res['reportData']=$this->db->fetchOne("SELECT * FROM groups where group_id = :group_id LIMIT 1 ", \Phalcon\Db::FETCH_ASSOC, ['group_id'=>$gid]);*/
                $res['next']=$this->db->fetchColumn("SELECT count(*) next FROM shifts where shift_id > :shid and workshop_id=:wid", ['shid'=>$result['shift_id'],'wid'=>$wid], 'next');
                $res['prev']=$this->db->fetchColumn("SELECT count(*) prev FROM shifts where shift_id < :shid and workshop_id=:wid", ['shid'=>$result['shift_id'],'wid'=>$wid], 'prev');
            }
        }
        return $res;
    }


    public function createGropup($data, $uid)
    {
        //print_r(\Phalcon\Di::getDefault()->getShared('db')); // This is the ugly way to grab the connection.
        $this->utf8init();
        /*

        "wrks": this.workshop.id,
        "prod": this.seriesForm.value.selproduct!==undefined?this.seriesForm.value.selproduct: data.product_id,
        "year": this.seriesForm.value.yearNum!==undefined?this.seriesForm.value.yearNum: data.series_year,
        "amount": this.seriesForm.value.amount!==undefined?this.seriesForm.value.amount: data.amount,
        "weight": this.seriesForm.value.pweight!==undefined?this.seriesForm.value.pweight: data.weight,
        "manual": this.seriesForm.value.manual!==undefined?this.seriesForm.value.manual: '0',
        "sid": this.seriesForm.value.sid?0:data.series_id,
        "puid": this.seriesForm.value.packer.uid,
        "uid":  this.authService.user.uid,


         */
        //<{IN `wrks` INT}>, <{IN `prod` INT}>, <{IN `year` INT(4)}>, <{IN `snum` INT}>, <{IN `amount` INT}>, <{IN `weight` INT}>, <{IN `manual` INT}>, <{IN `sid` INT}>, <{IN `packer_uid` INT}>, <{IN `usrid` INT}>, <{OUT xmsg VARCHAR(64)}>
        $sid='null';
        if (intval($data->sid)>0) {
            $sid=$data->sid;
        }

        $sql = "CALL `create_group`($data->wrks, $data->prod, $data->year,$data->snum,$data->amount,$data->weight,$data->manual,$sid,$data->puid,$data->puid2,$uid, @smsg);";
        $this->db->query($sql);
        $sql_res="SELECT @smsg as smsg;";

        /*$shid=$this->get_shift_id($data, $uid);
        $result=$this->db->query(
                "INSERT INTO groups (group_number,  first_name, surname, foreman_name, foreman_surname, workshop, product_type, weight, pallet_capacity, series_capcity, labman_name, labman_surname, uid, shift_id) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )",
             array($data->group_number, $data->first_name, $data->surname, $data->foreman_name, $data->foreman_surname, $data->workshop, $data->product_type, $data->weight, $data->pallet_capacity, $data->series_capcity, $data->labman_name, $data->labman_surname, $uid,$shid)
            );
        */

        return $this->db->fetchOne($sql_res, \Phalcon\Db::FETCH_ASSOC, []);
    }

    public function createShift($pdata, $uid)
    {
        $shiftNum=1;
        $hh=intval(date("H"));
        if ($hh>20||$hh<8) {
            $shiftNum=2;
        }
        $this->utf8init();
        $this->db->query("INSERT INTO shifts (shift_number,uid,workshop_id) VALUES ( ?, ?, ?)", array($shiftNum, $uid, $pdata->workshop));
        $shid=$this->get_shift_id(null, $uid);
        /*$data=(object)$this->getlastGroup();
        $result=$this->db->query(
                "INSERT INTO groups (group_number,  first_name, surname, foreman_name, foreman_surname, workshop, product_type, weight, pallet_capacity, series_capcity, labman_name, labman_surname, uid, shift_id) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )",
             array($data->group_number, $data->first_name, $data->surname, $data->foreman_name, $data->foreman_surname,$pdata->workshop, $data->product_type, $data->weight, $data->pallet_capacity, $data->series_capcity, $data->labman_name, $data->labman_surname, $uid,$shid)
           );*/

        return $result;
    }

    public function updateUPackages($data, $user)
    {
        if (isset($data->packages)&&(count($data->packages)>0&&$data->series_id>0)) {
            $series_id=intval($data->series_id);
            $lids=[];
            foreach ($data->packages as $package) {
                $lids[]=$package->label_id;
            }
            $sql="UPDATE packages SET series_id=$series_id WHERE label_id IN(".implode(',', $lids).")";
            $this->db->query($sql);
            }
    }

    public function updateUserData($data, $UserInfo){
        if ($data->del==true&&$data->uid>0){
            $this->db->query("DELETE FROM users WHERE uid=".$data->uid);
            $this->db->query("DELETE FROM user_role WHERE uid=".$data->uid);

        } elseif ($data->uid>0){
            $this->updateRoles($data->uid,$data->roles,$data->workshops);
            $sql="UPDATE `users` SET `second_name`='{$data->sname}', `first_name`='{$data->fname}',
             `password`='{$data->pass}', `is_packer`={$data->packer} WHERE `uid`='{$data->uid}'";
            $this->db->query($sql);
        } else {
            $sql="INSERT INTO `users` (`second_name`, `first_name`, `password`, `is_packer`) VALUES ('{$data->sname}', '{$data->fname}', '{$data->pass}', {$data->packer})";
            $this->db->query($sql);
            $uid=$this->db->lastInsertId();
            $this->updateRoles($uid,$data->roles,$data->workshops);
        }
       
    }

    private function updateRoles($uid,$roles,$workshops){
        $this->db->query("DELETE FROM user_role WHERE uid=".$uid);
        foreach ($workshops as $workshop) {
           foreach ($roles as $role) {
            $this->db->query("INSERT INTO `user_role` (`uid`, `role_id`, `workshop_id`) VALUES ('$uid', '$role', '$workshop')");
           }
        }
    }

    public function updatePallets($data, $user)
    {
        if (isset($data->pallets)&&(count($data->pallets)>0)) {
            $location=intval($data->location);
            if ($location>30) {
                $cuser=$user['first_name'].' '.$user['second_name'].' '.$user['uid'];
                $this->db->query("INSERT INTO shipments (doc_number, client_name) VALUES ( ?, ?)", array($data->invoice,$data->customer));
                $location=$this->db->lastInsertId();
                $location=$this->db->fetchColumn("SELECT min(ship_id) ship_id FROM shipments", [], 'ship_id');

            }
            $date = new \DateTime("NOW");
            $futuredate = $date->format('Y-m-d H:i:s');
            $pids=[];
            $sql_res='SELECT @smsg as smsg;';
            foreach ($data->pallets as $pallet) {
                //$pids[]=$pallet->pallet_id; move_pallet (wrks, pallet_code, new_location=31 | 32 | 33, null, msg);
                $sql = "CALL `move_pallet`($data->wrks, $pallet->pallet_code, $location, null, @smsg);";
                $this->db->query($sql);
            }
            return $this->db->fetchOne($sql_res, \Phalcon\Db::FETCH_ASSOC, []);
            //$sql="UPDATE pallets SET pallet_status=?, storage_time=? WHERE pallet_id IN(".implode(',', $pids).")";
            //$psql= "UPDATE preloaded_labels SET operation_id=?  WHERE label_id IN(SELECT label_id from packages  where  pallet_id IN(".implode(',', $pids)."))";
            //$result = $this->db->query($sql, array(105,$futuredate));
            //$result = $this->db->query($psql, array(105));
        }
    }

    public function getStorageShift($uid)
    {
        $sql="SELECT shift_id FROM storage_shifts where uid=:uid and shift_date > DATE_SUB(NOW(), INTERVAL 12 HOUR) ORDER BY shift_date DESC LIMIT 1";
        $shid=$this->db->fetchColumn($sql, ['uid'=>$uid], 'shift_id');
        if ($shid<1) {
            $isql="INSERT INTO storage_shifts (`startstmp`, `shift_date`, `uid`) VALUES (NOW(), NOW(), ?)";
            $result = $this->db->query($isql, array($uid));
            $shid=$this->db->lastInsertId();
        }
        return $shid;
    }

    public function createProbe($data, $uid)
    {
        //print_r(\Phalcon\Di::getDefault()->getShared('db')); // This is the ugly way to grab the connection.
        $this->utf8init();
        if (isset($data->seriesId)&&($data->seriesId>0)) {
            $query ="UPDATE probes SET fat=?, moisture=?, como=?, protein=?, acidity=?, milkAcidity=?,
						 purityLevel=?, solubility=?, enterobacteria=?, enterococci=?, koe=?, yeast=?, bgkp=?,
						 expirationTime=?, storingRequirement=?, timestmp='', uid=?, labman=?, standart=? WHERE seriesId = ?";

            $result = $this->db->query($query, array(
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
                    $data->standart,
                    $data->seriesId,
                ));
        } else {
            $sql="SELECT s.series_id FROM series s where s.series_num = :snum  and s.product_id=:pid and s.series_year=:year LIMIT 1";
            $seriesId=0+$this->db->fetchColumn($sql, ['snum'=>$data->series_num,'pid'=>$data->pid,'year'=>$data->year,], 'series_id');
            if ($seriesId>0) {
                $result=$this->db->query(
                "INSERT INTO probes (`seriesId`, `fat`, `moisture`, `como`, `protein`, `acidity`, `milkAcidity`, `purityLevel`, `solubility`, `enterobacteria`, `enterococci`, `koe`, `yeast`, `bgkp`, `expirationTime`, `storingRequirement`, `uid`, `labman`,`standart`)
			VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
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
                $data->standart
            )
            );
            }
        }
    }


    private function get_shift_id($data, $uid)
    {
        if (isset($data->new_shift)&&$data->new_shift==1) {
            //INSERT INTO `milida`.`shifts` (`shift_id`, `startstmp`, `shift_number`, `uid`) VALUES ('2', '', '1', '2');
            $this->db->query("INSERT INTO shifts (shift_number,uid) VALUES ( ?, ?)", array(1, $uid));
        }
        $result=$this->db->fetchOne("SELECT * FROM shifts order by startstmp desc LIMIT 1 ", \Phalcon\Db::FETCH_ASSOC, []);
        return  $result['shift_id'];
    }

    private function utf8init()
    {
        $this->db->query("SET NAMES 'utf8'");
        $this->db->query("SET CHARACTER SET 'utf8'");
        $this->db->query("SET SESSION collation_connection = 'utf8_general_ci'");
    }
}
