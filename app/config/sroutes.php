<?php
$routes=[];
/*
$routes[] = [
	'method' => 'post',
	'route' => '/sent-series/{wid:[0-9]+}',
	'handler' => [new \Controllers\CommonDataController(), 'updateSeries']
];


$routes[] = [
	'method' => 'post',
	'route' => '/user-crud',
	'handler' => [new \Controllers\CommonDataController(), 'updateUserData']
];

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
	'route' => '/login-form-data',
	'handler' => [new \Controllers\SalesDataController(), 'getloginFormData']
];

return $routes;
