<?php
use Phalcon\Http\Response;

namespace Controllers;

class SalesDataController extends \Phalcon\Mvc\Controller
{

    public function getSalesDataJobs()
    {
        $request = new \Phalcon\Http\Request();
        $MiLidaSalesModel = new \Models\MiLidaSales();
        $result = $MiLidaSalesModel->getSalesDataJobs();
        $Response = $this->allowCORS();
        return $Response->setJsonContent($result);
    }

    public function getCustomersList()
    {
        $request = new \Phalcon\Http\Request();
        $MiLidaSalesModel = new \Models\MiLidaSales();
        $result = $MiLidaSalesModel->getCustomersList();
        $Response = $this->allowCORS();
        return $Response->setJsonContent($result);

    }

    public function options(){
        return TRUE;
    }

    public function saveCustomer()
	{
		$res = 'error';
		try {
            $MiLidaSalesModel = new \Models\MiLidaSales();
			$data = $this->request->getJsonRawBody();
			$MiLidaSalesModel->saveCustomer($data);
			$res = 'ok';
			
		} catch (\Exception $e) {
			$res = 'Error: ' . get_class($e) . ": " . $e->getMessage();
		}
		$Response = $this->allowCORS();
		return $Response->setJsonContent(['status' => $res]);

    }
    
    public function saveJob()
	{
		$res = 'error';
		try {
            $MiLidaSalesModel = new \Models\MiLidaSales();
			$data = $this->request->getJsonRawBody();
			$MiLidaSalesModel->saveJob($data);
			$res = 'ok';
			
		} catch (\Exception $e) {
			$res = 'Error: ' . get_class($e) . ": " . $e->getMessage();
		}
		$Response = $this->allowCORS();
		return $Response->setJsonContent(['status' => $res]);

	}

    public function deleteJob($jid){
        $MiLidaSalesModel = new \Models\MiLidaSales();
		$request = new \Phalcon\Http\Request();
		$this->allowCORS($this->response);
		$Response = $this->allowCORS();
		return $Response->setJsonContent( $MiLidaSalesModel->deleteJob($jid));
    }

    public function confirmJob($jid){
        $MiLidaSalesModel = new \Models\MiLidaSales();
		$request = new \Phalcon\Http\Request();
		$this->allowCORS($this->response);
		$Response = $this->allowCORS();
		return $Response->setJsonContent( $MiLidaSalesModel->confirmJob($jid));
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
