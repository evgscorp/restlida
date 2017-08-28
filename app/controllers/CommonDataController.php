<?php
use Phalcon\Http\Response;
namespace Controllers;

class CommonDataController extends \Phalcon\Mvc\Controller {
//http://172.16.130.180/restlida/user-data?token=20WIh7QKUt8U0sJBOMTAYmRy0ZNFwkeQn6LPSeeD
	public function getCurrentUserInformation() {
			 $MiLidaCommonModel = new \Models\MiLidaCommon();
			 $UserInfo = $MiLidaCommonModel->getUserInfo($this->request->get("token"));
			 $Response=$this->allowCORS();
			 return $Response->setJsonContent($UserInfo);

	}

//http://172.16.130.180/restlida/last-group?token=ufMCdE8EehMSZ7uiQhEVuZfTWbUA8X7yXBxLBufL

	public function getlastGroup() {
			 $MiLidaCommonModel = new \Models\MiLidaCommon();
			 $this->allowCORS($this->response);
			 $Response=$this->allowCORS();
			 return $Response->setJsonContent($MiLidaCommonModel->getlastGroup());
}

// http://172.16.130.180/restlida/shift-production/1?token=ufMCdE8EehMSZ7uiQhEVuZfTWbUA8X7yXBxLBufL
public function getShiftProduction($gid){
	$MiLidaCommonModel = new \Models\MiLidaCommon();
	$Response=$this->allowCORS();
	return $Response->setJsonContent($MiLidaCommonModel->getShiftProductionInfo($gid));
}


  /*
	URL: http://172.16.130.180/restlida/add-group?token=20WIh7QKUt8U0sJBOMTAYmRy0ZNFwkeQn6LPSeeD
	Header:
	Content-type: application/json
	Authorization: Bearer 20WIh7QKUt8U0sJBOMTAYmRy0ZNFwkeQn6LPSeeD
	JSON body example:
	{ "group_number": "123459999123",
	  "first_name": "Павел",
	  "surname": "Павлов",
	  "foreman_name": "Александр",
	  "foreman_surname": "Александров",
	  "labman_name": "Иван",
	  "labman_surname": "Васильев",
	  "workshop": "1й цех",
	  "product_type": "4",
	  "weight": "25",
	  "pallet_capacity":"60",
	  "series_capcity": 1200
	}
*/
	public function createGroup() {
			 $res='error';
			 try {
				 $MiLidaCommonModel = new \Models\MiLidaCommon();
				 $UserInfo = $MiLidaCommonModel->getUserInfo($this->resource->getAccessToken());
				 if (isset($UserInfo['uid'])&&$UserInfo['uid']>1&&$UserInfo['uid']!=3){
					$data=$this->request->getJsonRawBody();
				 	$MiLidaCommonModel->createGropup($data,$UserInfo['uid']);
				 	$res='ok';
			   }
			 }
			 catch (\Exception $e) {
				 $res='Error: '.get_class($e).": ".$e->getMessage();
			 }
			 $Response=$this->allowCORS();
			 return $Response->setJsonContent(['status'=>$res]);

	}

 public function allowCORS(){
	 $this->response->setHeader('Access-Control-Allow-Origin', '*');
	 $this->response->setHeader('Access-Control-Allow-Headers', 'X-Requested-With');
	 $this->response->sendHeaders();
	 return $this->response;
 }

}
