<?php
use Phalcon\Http\Response;
//use Models\MiLidaCommon;
namespace Controllers;

class CommonDataController extends \Phalcon\Mvc\Controller {


	public static function getCurrentUserInformation() {
			 $MiLidaCommonModel = new MiLidaCommon();
			 $UserInfo = $MiLidaCommonModel->getUserInfo(2);
			 $Response = new \Phalcon\Http\Response();
			// $Response->setJsonContent(['ppppsdf','sdfsdfsdf','111111sdf']);
			 $Response->setJsonContent($UserInfo);
			 return $Response;

	}

}
