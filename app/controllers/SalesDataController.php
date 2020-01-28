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

    public function getSalesloginFormData(){
        $request = new \Phalcon\Http\Request();
        $MiLidaSalesModel = new \Models\MiLidaSales();
		$result = $MiLidaSalesModel->getSalesloginFormData();
		$Response = $this->allowCORS();
		return $Response->setJsonContent($result);
    }
    
    public function getJobsItems ($lid)
    {
	    $request = new \Phalcon\Http\Request();
        $MiLidaSalesModel = new \Models\MiLidaSales();
        $result = $MiLidaSalesModel->getSalesJobsItems($lid, $request->get("job_id"));
        $Response = $this->allowCORS();
        return $Response->setJsonContent($result);	
    }

    public function getJobLock($jid)
    {
	    $request = new \Phalcon\Http\Request();
        $MiLidaSalesModel = new \Models\MiLidaSales();
        $result = $MiLidaSalesModel->getSalesJobLock($jid);
        $Response = $this->allowCORS();
        return $Response->setJsonContent($result);	
    }

    public function getJobUnLock($jid)
    {
	    $request = new \Phalcon\Http\Request();
        $MiLidaSalesModel = new \Models\MiLidaSales();
        $result = $MiLidaSalesModel->getSalesJobUnLock($jid);
        $Response = $this->allowCORS();
        return $Response->setJsonContent($result);	
    }


    public function getJobsItemsList(){
        $jobs = [];
        try {
            $MiLidaSalesModel = new \Models\MiLidaSales();
            $data = $this->request->getJsonRawBody();
            foreach ($data->rows as $row){
                if($row->job_id){
                    $jobs[] = $row->job_id;
                }
            }
            $lid = 0; // Склад пофигу, передаем список заданий
            $res = $MiLidaSalesModel->getSalesJobsItems($lid, $jobs);
            // меняем статус задания
            foreach ($jobs as $job_id){
	            $x = $MiLidaSalesModel->getSalesJobLock($job_id);
            }
		} catch (\Exception $e) {
			$res = 'Error: ' . get_class($e) . ": " . $e->getMessage();
		}
		$Response = $this->allowCORS();
		return $Response->setJsonContent($res);
    }

    public function saveJobsResult()
    {
        $res = 'error';
		try {
            $MiLidaSalesModel = new \Models\MiLidaSales();
			$data = $this->request->getJsonRawBody();
			$res = $MiLidaSalesModel->postJobsResults($data);
	
		} catch (\Exception $e) {
			$res = 'Error: ' . get_class($e) . ": " . $e->getMessage();
		}
		$Response = $this->allowCORS();
		return $Response->setJsonContent(['status' => $res]);
    }

    public function movePallets(){
        $res = 'error';
        error_log("Action movePallet");
		try {
            $MiLidaCommonModel = new \Models\MiLidaCommon();
            $MiLidaSalesModel = new \Models\MiLidaSales();
            $data = $this->request->getJsonRawBody();
            if (!$data){
                error_log("Incorrect JSON input");
            }
            $user = $MiLidaCommonModel->getUserInfo($this->resource->getAccessToken());
            if ( !empty($user['uid'])){
                 $res = $MiLidaSalesModel->movePallets($data, $user['uid']);
                } else {
                 $res = $MiLidaSalesModel->movePallets($data, 'null');    
                }
		} catch (\Exception $e) {
			$res = 'Error: ' . get_class($e) . ": " . $e->getMessage();
		}
		$Response = $this->allowCORS();
		return $Response->setJsonContent(['status' => $res]);
    }
// ----------------------------------

    public function getSalesDataJobs()
    {
        $request = new \Phalcon\Http\Request();
        $MiLidaSalesModel = new \Models\MiLidaSales();

        $result = $MiLidaSalesModel->getSalesDataJobs(null,$request->get("locationId"),$request->get("customerId"),$request->get("statusId"),$request->get("productId"));
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
        $result = $MiLidaSalesModel->getCustomersList($request->get("valid"));
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
		return $Response->setJsonContent($MiLidaSalesModel->getSalesSeriesData($lid,$request->get("sname"),$request->get("ip"),$request->get("jid")));
    }

    public function getShipmentReport(){
        $request = new \Phalcon\Http\Request();
        $MiLidaSalesModel = new \Models\MiLidaSales();
        $Response = $this->allowCORS();
		return $Response->setJsonContent($MiLidaSalesModel->getShipmentReport($request->get("sdate"), $request->get("edate"), $request->get("location"), $request->get("customer")));
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
		return $Response->setJsonContent($res);

    }

    public function updateCustomer()
	{
		$res = 'error';
		try {
            $MiLidaSalesModel = new \Models\MiLidaSales();
			$data = $this->request->getJsonRawBody();
			$MiLidaSalesModel->updateCustomer($data);
			$res = 'ok';
			
		} catch (\Exception $e) {
			$res = 'Error: ' . get_class($e) . ": " . $e->getMessage();
		}
		$Response = $this->allowCORS();
		return $Response->setJsonContent($res);

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
            $MiLidaCommonModel = new \Models\MiLidaCommon();
            $request = new \Phalcon\Http\Request();
            $authHeader =$request->getHeaders()['Authorization'];
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $UserInfo = $MiLidaCommonModel->getUserInfo($matches[1]);
            } else $UserInfo = $MiLidaCommonModel->getUserInfo($request->get("token"));
			$data = $this->request->getJsonRawBody();
			$res = $MiLidaSalesModel->saveJob($data,$UserInfo['uname']);
			
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
        $MiLidaCommonModel = new \Models\MiLidaCommon();
        $UserInfo = $MiLidaCommonModel->getUserInfo($request->get("token"));
		$this->allowCORS($this->response);
		$Response = $this->allowCORS();
        return $Response->setJsonContent($MiLidaSalesModel->confirmJob($jid, $request->get("reverse"), $UserInfo['uname']));
        //return $Response->setJsonContent($UserInfo);
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
