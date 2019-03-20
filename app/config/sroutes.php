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
	'method' => 'get',
	'route' => '/delete-job/{jid:[0-9]+}',
	'handler' => [new \Controllers\SalesDataController(), 'deleteJob']
];

$routes[] = [
	'method' => 'get',
	'route' => '/confirm-job/{jid:[0-9]+}',
	'handler' => [new \Controllers\SalesDataController(), 'confirmJob']
];


/*
$routes[] = [
	'method' => 'get',
	'route' => '/product-types',
	'handler' => [new \Controllers\CommonDataController(), 'getProductTypes']
];

$routes[] = [
	'method' => 'get',
	'route' => '/allowed-moves/{wid:[0-9]+}',
	'handler' => [new \Controllers\CommonDataController(), 'getAllowedMoves']
];
*/
$routes[] = [
	'method' => 'get',
	'route' => '/sales-jobs',
	'handler' => [new \Controllers\SalesDataController(), 'getSalesDataJobs']
];

$routes[] = [
	'method' => 'get',
	'route' => '/listof-customers',
	'handler' => [new \Controllers\SalesDataController(), 'getCustomersList']
];


return $routes;
