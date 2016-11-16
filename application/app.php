<?php

session_start();

require __DIR__ . '/../vendor/autoload.php';

$app = new \Slim\App([

	'settings' => [

		'displayErrorDetails' => true,

		'db' => [
			'driver' => 'mysql',
			'host' => 'localhost',
			'database' => 'db_preguntados',
			'username' => 'admin',
			'password' => 'admindta',
			'charset' => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix' => '',
		]
	]

]);


$container = $app->getContainer();

$capsule = new \Illuminate\Database\Capsule\Manager;

$capsule->addConnection($container['settings']['db']);

$capsule->setAsGlobal();

$capsule->bootEloquent();

$container['db'] = function($container) use ($capsule){
	return $capsule;
};

$container['MainController'] = function($container){
	return new \Routes\Controller\MainController($container);
};

/*$container['csrf'] = function($container){
	return new \Slim\Csrf\Guard;
};

$app->add($container->csrf);*/

require __DIR__ . '/../routes/routes.php';