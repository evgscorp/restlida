<?php

/**
 * Small Micro application to run simple/rest based applications
 *
 * @package Application
 * @author Jete O'Keeffe
 * @version 1.0
 * @link http://docs.phalconphp.com/en/latest/reference/micro.html
 * @example
 	$app = new Micro();
	$app->setConfig('/path/to/config.php');
	$app->setAutoload('/path/to/autoload.php');
	$app->get('/api/looks/1', function() { echo "Hi"; });
	$app->finish(function() { echo "Finished"; });
	$app->run();
 */

namespace Application;

use Interfaces\IRun as IRun;
//use Phalcon\Mvc\Router;

class Micro extends \Phalcon\Mvc\Micro implements IRun {

    /**
     * Pages that doesn't require authentication
     * @var array
     */
    protected $_noAuthPages;

	/**
	 * Constructor of the App
	 */
	public function __construct() {
        $this->_noAuthPages = array();
	}

	/**
	 * Set Dependency Injector with configuration variables
	 *
	 * @throws Exception		on bad database adapter
	 * @param string $file		full path to configuration file
	 */
	public function setConfig($file) {
		if (!file_exists($file)) {
			throw new \Exception('Unable to load configuration file');
		}

		$di = new \Phalcon\DI\FactoryDefault();
		$di->set('config', new \Phalcon\Config(require $file));

		$di->set('db', function() use ($di) {
			$type = strtolower($di->get('config')->database->adapter);
			$creds = array(
				'host' => $di->get('config')->database->host,
				'username' => $di->get('config')->database->username,
				'password' => $di->get('config')->database->password,
				'dbname' => $di->get('config')->database->name
			);

			if ($type == 'mysql') {
				$connection =  new \Phalcon\Db\Adapter\Pdo\Mysql($creds);
			} else if ($type == 'postgres') {
				$connection =  new \Phalcon\Db\Adapter\Pdo\Postgesql($creds);
			} else if ($type == 'sqlite') {
				$connection =  new \Phalcon\Db\Adapter\Pdo\Sqlite($creds);
			} else {
				throw new Exception('Bad Database Adapter');
			}

			return $connection;
		});

        $di['resource'] = function () use ($di) {

            $creds = array(
                'host' => $di->get('config')->oauth2->host,
                'username' => $di->get('config')->oauth2->username,
                'password' => $di->get('config')->oauth2->password,
                'dbname' => $di->get('config')->oauth2->dbname
            );

            $oauthdb = new \Phalcon\Db\Adapter\Pdo\Mysql(
                $creds
            );
            $resource = new \League\OAuth2\Server\Resource(
                new \Oauth2\Server\Storage\Pdo\Mysql\Session($oauthdb)
            );
            $resource->setRequest(new \Oauth2\Server\Storage\Pdo\Mysql\Request());

            return $resource;
        };

        $this->setDI($di);
	}

    public function getOauth2Config($file){
        if (!file_exists($file)) {
            throw new \Exception('Unable to load configuration file');
        }

        $di = new \Phalcon\DI\FactoryDefault();
        $di->set('config', new \Phalcon\Config(require $file));

        return $di->get("config")->oauth2;
    }
	/**
	 * Set namespaces to tranverse through in the autoloader
	 *
	 * @link http://docs.phalconphp.com/en/latest/reference/loader.html
	 * @throws Exception
	 * @param string $file		map of namespace to directories
	 */
	public function setAutoload($file, $dir, $vendorDir) {
		if (!file_exists($file)) {
			throw new \Exception('Unable to load autoloader file');
		}

		// Set dir to be used inside include file
		$namespaces = include $file;

		$loader = new \Phalcon\Loader();
		$loader->registerNamespaces($namespaces)->register();


        /**
         * We're a registering a set of directories taken from the configuration file
         */
        $loader->registerDirs(
            array(
                $vendorDir."/Oauth2/src"
            )
        )->register();

    }

	/**
	 * Set Routes\Handlers for the application
	 *
	 * @throws Exception
	 * @param file			file thats array of routes to load
	 */
	public function setRoutes($file) {
		if (!file_exists($file)) {
			throw new \Exception('Unable to load routes file');
		}
  	$routes = include($file);

		if (!empty($routes)) {
   	foreach($routes as $obj) {

				switch($obj['method']) {
					case 'get':
						//$this->get($obj['route'], $obj['handler']);
           // print_r($obj);
            $this->get($obj['route'], $obj['handler']);
						break;
					case 'post':
						$this->post($obj['route'], $obj['handler']);
						break;
					case 'delete':
						$this->delete($obj['route'], $obj['handler']);
						break;
					case 'put':
						$this->head($obj['route'], $obj['handler']);
						break;
					case 'options':
						$this->options($obj['route'], $obj['handler']);
						break;
					case 'patch':
						$this->patch($obj['route'], $obj['handler']);
						break;
					default:
						break;
				}
			}
   
		 }
 	}

	/**
	 * Set events to be triggered before/after certain stages in Micro App
	 *
	 * @param object $event		events to add
	 */
	public function setEvents(\Phalcon\Events\Manager $events) {
		$this->setEventsManager($events);
	}

    /**
     *
     */
    public function getUnauthenticated() {
        return $this->_noAuthPages;
    }
	/**
	 * Main run block that executes the micro application
	 *
	 */
	public function run() {

		// Handle any routes not found
		$this->notFound(function () {
			$response = new \Phalcon\Http\Response();
			$response->setStatusCode(404, 'Not Found')->sendHeaders();
			$response->setContent('Page doesn\'t exist.');
			$response->send();
		});

		$this->handle();

	}

}
