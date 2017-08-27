<?php

/**
 * @author Jete O'Keeffe
 * @version 1.0
 * @link http://docs.phalconphp.com/en/latest/reference/micro.html#defining-routes
 * @eg.

$routes[] = [
 	'method' => 'post',
	'route' => '/api/update',
	'handler' => 'myFunction'
];

 */

$routes[] = [
	'method' => 'get',
	'route' => '/user-data',
	'handler' => [new \Controllers\CommonDataController(), 'getCurrentUserInformation']
];

$routes[] = [
	'method' => 'post',
	'route' => '/add-group',
	'handler' => [new \Controllers\CommonDataController(), 'createGroup']
];

$routes[] = [
	'method' => 'get',
	'route' => '/last-group',
	'handler' => [new \Controllers\CommonDataController(), 'getlastGroup']
];

$routes[] = [
	'method' => 'get',
	'route' => '/shift-production/{gid:[0-9]+}',
	'handler' => [new \Controllers\CommonDataController(), 'getShiftProduction']
];


return $routes;
