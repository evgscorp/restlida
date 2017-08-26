<?php
use Phalcon\Http\Response;
namespace Controllers;

class CommonDataController extends \Phalcon\Mvc\Controller {

	public function getCurrentUserInformation() {
			 $MiLidaCommonModel = new \Models\MiLidaCommon();
			 $UserInfo = $MiLidaCommonModel->getUserInfo($this->request->get("token"));
			 //$Response = new \Phalcon\Http\Response();
			 $Response=$this->response;
			 //$Response->setJsonContent(['ppppsdf','sdfsdfsdf','111111sdf']);
			 // $Response->setJsonContent($this->request->get("token"));
			 return $Response->setJsonContent($UserInfo);

	}

}
