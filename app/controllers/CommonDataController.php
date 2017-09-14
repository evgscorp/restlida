<?php
use Phalcon\Http\Response;
namespace Controllers;

class CommonDataController extends \Phalcon\Mvc\Controller {
//http://172.16.130.180/restlida/user-data?token=20WIh7QKUt8U0sJBOMTAYmRy0ZNFwkeQn6LPSeeD
	public function getCurrentUserInformation() {
			 $MiLidaCommonModel = new \Models\MiLidaCommon();
			 if (!isset($this->request)){
				 $request = new \Phalcon\Http\Request();
			 } else {$request=$this->request; };

			 $UserInfo = $MiLidaCommonModel->getUserInfo($request->get("token"));
			 $Response=$this->allowCORS();
			 return $Response->setJsonContent($UserInfo);

	}

public function getProbe(){
	$request = new \Phalcon\Http\Request();
	$MiLidaCommonModel = new \Models\MiLidaCommon();
	$result=$MiLidaCommonModel->getProbeData($request->get("search"));
	$Response=$this->allowCORS();
	return $Response->setJsonContent($result);
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

// http://172.16.130.180/restlida/shift-suggestion?token=ufMCdE8EehMSZ7uiQhEVuZfTWbUA8X7yXBxLBufL
public function getShiftSuggestions(){
	$MiLidaCommonModel = new \Models\MiLidaCommon();
	$Response=$this->allowCORS();
	return $Response->setJsonContent($MiLidaCommonModel->getShiftSuggestionsInfo());
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
	 //$this->response->setHeader('Access-Control-Allow-Headers', 'X-Requested-With');
	 $this->response->setHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, origin, authorization, accept, client-security-token');
	 $this->response->setHeader('Access-Control-Allow-Methods','POST, GET, OPTIONS, PUT, PATCH, DELETE');
	 $this->response->setHeader('Access-Control-Max-Age','1000');




		/* if (array_key_exists('HTTP_ACCESS_CONTROL_REQUEST_HEADERS', $_SERVER)) {
				 header('Access-Control-Allow-Headers: '.$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']);
		 } else {
				 header('Access-Control-Allow-Headers: *');
		 }*/
	 $this->response->sendHeaders();
	 return $this->response;
 }

}
