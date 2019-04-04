<?php
/**
 * Settings to be stored in dependency injector
 */

$settings = array(
	'database' => array(
		'adapter' => 'Mysql',	/* Possible Values: Mysql, Postgres, Sqlite */
		'host' => 'localhost',
		'username' => 'misql',
    'password' => 'miliCante',
		'name' => 'fork',
		'charset'   =>'utf8',
		'port' => 3306,
		"options" => array(
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET CHARACTER SET 'utf8'",
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION collation_connection = 'utf8_general_ci'"
        )
	),

    'oauth2' => array(
        'adapter'  => 'Mysql',
        'host'     => 'localhost',
        'port'     => 3306,
        'username' => 'misql',
        'password' => 'miliCante',
        'dbname'   => 'oauth',
				'charset'   =>'utf8',
				"options" => array(
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET CHARACTER SET 'utf8'",
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION collation_connection = 'utf8_general_ci'"
		        )
    )
);


return $settings;
