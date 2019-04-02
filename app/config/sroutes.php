<?php
$routes=[];

/*$routes[] = [
	'method' => 'post',
	'route' => '/sent-series/{wid:[0-9]+}',
	'handler' => [new \Controllers\CommonDataController(), 'updateSeries']
];*/


$routes[] = [
	'method' => 'post',
	'route' => '/save-customer',
	'handler' => [new \Controllers\SalesDataController(), 'saveCustomer']
];


$routes[] = [
	'method' => 'options',
	'route' => '/save-customer',
	'handler' => [new \Controllers\SalesDataController(), 'options']
];


$routes[] = [
	'method' => 'post',
	'route' => '/save-job',
	'handler' => [new \Controllers\SalesDataController(), 'saveJob']
];


$routes[] = [
	'method' => 'options',
	'route' => '/save-job',
	'handler' => [new \Controllers\SalesDataController(), 'options']
];


$routes[] = [
	'method' => 'post',
	'route' => '/save-jobItem',
	'handler' => [new \Controllers\SalesDataController(), 'savejobItem']
];


$routes[] = [
	'method' => 'options',
	'route' => '/save-jobItem',
	'handler' => [new \Controllers\SalesDataController(), 'options']
];


$routes[] = [
	'method' => 'get',
	'route' => '/delete-job/{jid:[0-9]+}',
	'handler' => [new \Controllers\SalesDataController(), 'deleteJob']
];

$routes[] = [
	'method' => 'get',
	'route' => '/confirm-job/{jid:[0-9]+}',
	'handler' => [new \Controllers\SalesDataController(), 'confirmJob']
];
//http://172.16.130.180/restlida/sales-series-data/21?token=V0GKFDzK38sYb8StQRcEkGDeU3CXUZ01BPXxDGZX&sname=%D0%A1%D0%9E%D0%9C
$routes[] = [
	'method' => 'get',
	'route' => '/sales-series-data/{lid:[0-9]+}',
	'handler' => [new \Controllers\SalesDataController(), 'getSalesSeriesData']
];
http://172.16.130.180/restlida/sales-job-items/1?token=V0GKFDzK38sYb8StQRcEkGDeU3CXUZ01BPXxDGZX&sname=%D0%A1%D0%9E%D0%9C
$routes[] = [
	'method' => 'get',
	'route' => '/sales-job-items/{jid:[0-9]+}',
	'handler' => [new \Controllers\SalesDataController(), 'getJobItems']
];


$routes[] = [
	'method' => 'get',
	'route' => '/sales-jobs',
	'handler' => [new \Controllers\SalesDataController(), 'getSalesDataJobs']
];

$routes[] = [
	'method' => 'get',
	'route' => '/sales-job/{jid:[0-9]+}',
	'handler' => [new \Controllers\SalesDataController(), 'getSalesDataJob']
];


$routes[] = [
	'method' => 'get',
	'route' => '/listof-customers',
	'handler' => [new \Controllers\SalesDataController(), 'getCustomersList']
];

$routes[] = [
	'method' => 'get',
	'route' => '/listof-ips',
	'handler' => [new \Controllers\SalesDataController(), 'getIPsList']
];


$routes[] = [
	'method' => 'get',
	'route' => '/sales-storage-locations',
	'handler' => [new \Controllers\SalesDataController(), 'getSalesStorageLocations']
];


return $routes;