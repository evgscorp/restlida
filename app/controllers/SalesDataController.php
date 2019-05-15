<?php
use Phalcon\Http\Response;

namespace Controllers;

class SalesDataController extends \Phalcon\Mvc\Controller
{
// REST for Warehouse terminals 
   public function getJobsList($lid)
    {
	    $request = new \Phalcon\Http\Request();
        $MiLidaSalesModel = new \Models\MiLidaSales();
        $result = $MiLidaSalesModel->getSalesJobsList($lid);
        $Response = $this->allowCORS();
        return $Response->setJsonContent($result);	
    }
    
    public function getJobsItems ($lid)
    {
	    $request = new \Phalcon\Http\Request();
        $MiLidaSalesModel = new \Models\MiLidaSales();
        $result = $MiLidaSalesModel->getSalesJobsItems($lid);
        $Response = $this->allowCORS();
        return $Response->setJsonContent($result);	
    }
// ----------------------------------

    public function getSalesDataJobs()
    {
        $request = new \Phalcon\Http\Request();
        $MiLidaSalesModel = new \Models\MiLidaSales();

        $result = $MiLidaSalesModel->getSalesDataJobs(null,$request->get("locationId"),$request->get("customerId"),$request->get("statusId"));
        $Response = $this->allowCORS();
        return $Response->setJsonContent($result);
    }

    public function getSalesDataJob($jid)
    {
        $request = new \Phalcon\Http\Request();
        $MiLidaSalesModel = new \Models\MiLidaSales();
        $result = $MiLidaSalesModel->getSalesDataJobs($jid);
        $Response = $this->allowCORS();
        return $Response->setJsonContent($result);
    }


    public function getSalesStorageLocations()
    {
        $request = new \Phalcon\Http\Request();
        $MiLidaSalesModel = new \Models\MiLidaSales();
        $result = $MiLidaSalesModel->getSalesStorageLocations();
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

    public function  getIPsList()
    {
        
        $request = new \Phalcon\Http\Request();
        $MiLidaSalesModel = new \Models\MiLidaSales();
        $result = $MiLidaSalesModel->getIPsList();
        $Response = $this->allowCORS();
        return $Response->setJsonContent($result);

    }

    public function getProductsList() {
        
        $request = new \Phalcon\Http\Request();
        $MiLidaSalesModel = new \Models\MiLidaSales();
        $result = $MiLidaSalesModel->getProductsList();
        $Response = $this->allowCORS();
        return $Response->setJsonContent($result);

    }

    public function  getSalesSeriesData($lid){
        $request = new \Phalcon\Http\Request();
        $MiLidaSalesModel = new \Models\MiLidaSales();
        $Response = $this->allowCORS();
		return $Response->setJsonContent($MiLidaSalesModel->getSalesSeriesData($lid,$request->get("sname"),$request->get("ip")));
    }

    public function  getJobItems($jid){
        $request = new \Phalcon\Http\Request();
        $MiLidaSalesModel = new \Models\MiLidaSales();
        $Response = $this->allowCORS();
		return $Response->setJsonContent($MiLidaSalesModel->getJobItems($jid));
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

    public function saveDelivery(){

        $res = 'error';
		try {
            $MiLidaSalesModel = new \Models\MiLidaSales();
			$data = $this->request->getJsonRawBody();
			$res = $MiLidaSalesModel->saveDelivery($data);
			
		} catch (\Exception $e) {
			$res = 'Error: ' . get_class($e) . ": " . $e->getMessage();
		}
		$Response = $this->allowCORS();
		return $Response->setJsonContent($res);

    }
    
    public function saveJob()
	{
		$res = 'error';
		try {
            $MiLidaSalesModel = new \Models\MiLidaSales();
			$data = $this->request->getJsonRawBody();
			$res = $MiLidaSalesModel->saveJob($data);
			
		} catch (\Exception $e) {
			$res = 'Error: ' . get_class($e) . ": " . $e->getMessage();
		}
		$Response = $this->allowCORS();
		return $Response->setJsonContent($res);

    }
    

    public function savejobItem(){
       
        $res = 'error';
		try {
            $MiLidaSalesModel = new \Models\MiLidaSales();
			$data = $this->request->getJsonRawBody();
			$MiLidaSalesModel->saveJobItem($data);
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
		return $Response->setJsonContent( $MiLidaSalesModel->confirmJob($jid, $request->get("reverse")));
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
