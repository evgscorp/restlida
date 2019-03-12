<?php
use Phalcon\Http\Response;

namespace Controllers;

class SalesDataController extends \Phalcon\Mvc\Controller
{

    public function getSalesData()
    {
        $request = new \Phalcon\Http\Request();
        $MiLidaSalesModel = new \Models\MiLidaSales();
        $result = $MiLidaSalesModel->getloginFormData();
        $Response = $this->allowCORS();
        return $Response->setJsonContent($result);
    }

    public function allowCORS()
    {
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, origin, authorization, accept, client-security-token');
        $this->response->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, PATCH, DELETE');
        $this->response->setHeader('Access-Control-Max-Age', '1000');
        $this->response->sendHeaders();
        return $this->response;
    }

}
