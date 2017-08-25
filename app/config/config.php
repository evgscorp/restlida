<?php
/**
 * Settings to be stored in dependency injector
 */

$settings = array(
	'database' => array(
		'adapter' => 'Mysql',	/* Possible Values: Mysql, Postgres, Sqlite */
		'host' => '172.16.130.180',
		'username' => 'misql',
        	'password' => 'miliCante',
		'name' => 'millida',
		'port' => 3306
	),

    'oauth2' => array(
        'adapter'  => 'Mysql',
        'host'     => '172.16.130.180',
        'port'     => 3306,
        'username' => 'misql',
        'password' => 'miliCante',
        'dbname'   => 'oauth2',
    )
);


return $settings;
