<?php
use Phalcon\Http\Response;
namespace Controllers;

class CommonDataController extends \Phalcon\Mvc\Controller {
//http://172.16.130.180/restlida/user-data?token=20WIh7QKUt8U0sJBOMTAYmRy0ZNFwkeQn6LPSeeD
	public function getCurrentUserInformation() {
			 $MiLidaCommonModel = new \Models\MiLidaCommon();
			 $UserInfo = $MiLidaCommonModel->getUserInfo($this->request->get("token"));
			 $Response=$this->response;
			 return $Response->setJsonContent($UserInfo);

	}

	public function createGroup() {
			 $res='error';
			 try {
				 $MiLidaCommonModel = new \Models\MiLidaCommon();
				 $UserInfo = $MiLidaCommonModel->getUserInfo($this->request->get("token"));
				 if (isset($UserInfo['uid'])&&$UserInfo['uid']>1&&$UserInfo['uid']!=3){
					$data=$this->request->getJsonRawBody();
				 	$MiLidaCommonModel->createGropup($data,$UserInfo['uid']);
				 	$res='ok';
			   }
			 }
			 catch (\Exception $e) {
				 $res='Error: '.get_class($e).": ".$e->getMessage();
			 }
			 $Response=$this->response;
			 return $Response->setJsonContent(['status'=>$res]);

	}


}
