<?php
use Phalcon\Http\Response;
namespace Controllers;

class CommonDataController extends \Phalcon\Mvc\Controller {
	
	public function getCurrentUserInformation() {
			 $MiLidaCommonModel = new \Models\MiLidaCommon();
			 $UserInfo = $MiLidaCommonModel->getUserInfo(2);
			 $Response = new \Phalcon\Http\Response();
			 $Response->setJsonContent(['ppppsdf','sdfsdfsdf','111111sdf']);
			 //$Response->setJsonContent($UserInfo);
			 $Response->setJsonContent($this->request->get("token"));
			 return $Response;

	}

}
