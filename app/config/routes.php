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
	'method' => 'get',
	'route' => '/loginform-data',
	'handler' => [new \Controllers\CommonDataController(), 'getloginFormData']
];

$routes[] = [
	'method' => 'post',
	'route' => '/add-shift',
	'handler' => [new \Controllers\CommonDataController(), 'createShift']
];


$routes[] = [
	'method' => 'post',
	'route' => '/add-series',
	'handler' => [new \Controllers\CommonDataController(), 'createGroup']
];

$routes[] = [
	'method' => 'post',
	'route' => '/add-probe',
	'handler' => [new \Controllers\CommonDataController(), 'createProbe']
];

$routes[] = [
	'method' => 'post',
	'route' => '/update-pallets',
	'handler' => [new \Controllers\CommonDataController(), 'updatePallets']
];

$routes[] = [
	'method' => 'post',
	'route' => '/sent-series/{wid:[0-9]+}',
	'handler' => [new \Controllers\CommonDataController(), 'updateSeries']
];


$routes[] = [
	'method' => 'post',
	'route' => '/update-upackages',
	'handler' => [new \Controllers\CommonDataController(), 'updateUPackages']
];

$routes[] = [
	'method' => 'post',
	'route' => '/user-crud',
	'handler' => [new \Controllers\CommonDataController(), 'updateUserData']
];


$routes[] = [
	'method' => 'get',
	'route' => '/last-group',
	'handler' => [new \Controllers\CommonDataController(), 'getlastGroup']
];

$routes[] = [
	'method' => 'get',
	'route' => '/sents-pallets/{wid:[0-9]+}',
	'handler' => [new \Controllers\CommonDataController(), 'getSentPallets']
];


$routes[] = [
	'method' => 'get',
	'route' => '/seriesform-data/{wid:[0-9]+}',
	'handler' => [new \Controllers\CommonDataController(), 'getSeriesFormData']
];


$routes[] = [
	'method' => 'get',
	'route' => '/search-pallet-packages',
	'handler' => [new \Controllers\CommonDataController(), 'getPalletsPackages']
];

$routes[] = [
	'method' => 'get',
	'route' => '/series-probes',
	'handler' => [new \Controllers\CommonDataController(), 'getProbe']
];

$routes[] = [
	'method' => 'get',
	'route' => '/search-shift-production',
	'handler' => [new \Controllers\CommonDataController(), 'getShiftbyDate']
];

$routes[] = [
	'method' => 'get',
	'route' => '/search-shift-storage',
	'handler' => [new \Controllers\CommonDataController(), 'getStorageShiftReport']
];

$routes[] = [
	'method' => 'get',
	'route' => '/storage-overview/{wid:[0-9]+}',
	'handler' => [new \Controllers\CommonDataController(), 'getStorageOverview']
];

$routes[] = [
	'method' => 'get',
	'route' => '/storage-overview-dates/{wid:[0-9]+}',
	'handler' => [new \Controllers\CommonDataController(), 'getStorageOverviewDates']
];


$routes[] = [
	'method' => 'get',
	'route' => '/search-series-packages',
	'handler' => [new \Controllers\CommonDataController(), 'getSeriesPackages']
];

$routes[] = [
	'method' => 'get',
	'route' => '/package-log-info',
	'handler' => [new \Controllers\CommonDataController(), 'getPackageLog']
];

$routes[] = [
	'method' => 'get',
	'route' => '/shift-production/{gid:[0-9]+}',
	'handler' => [new \Controllers\CommonDataController(), 'getShiftProduction']
];

$routes[] = [
	'method' => 'get',
	'route' => '/production-data/{wid:[0-9]+}',
	'handler' => [new \Controllers\CommonDataController(), 'getProductionData']
];

$routes[] = [
	'method' => 'get',
	'route' => '/summary-report',
	'handler' => [new \Controllers\CommonDataController(), 'getSummaryReport']
];

$routes[] = [
	'method' => 'get',
	'route' => '/users-list',
	'handler' => [new \Controllers\CommonDataController(), 'getUsersList']
];

$routes[] = [
	'method' => 'get',
	'route' => '/product-types',
	'handler' => [new \Controllers\CommonDataController(), 'getProductTypes']
];


return $routes;
