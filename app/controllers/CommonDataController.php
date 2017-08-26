<?php
use Phalcon\Http\Response;
namespace Controllers;

class CommonDataController extends \Phalcon\Mvc\Controller {


	public static function getCurrentUserInformation() {
			 $response = new \Phalcon\Http\Response();
			 $response->setJsonContent(['ppppsdf','sdfsdfsdf','111111sdf']);
			 return $response;

	}

}
