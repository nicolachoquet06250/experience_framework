<?php

use core\Base;
use core\ImageShower;
use core\Setup;

header("Access-Control-Allow-Origin: *");
ini_set('display_errors', 'on');

require_once __DIR__.'/autoload.php';

echo core\Router::create($_SERVER['REQUEST_URI'],
	function (string $controller) {
		if(isset($_GET['debug'])) {
			ini_set('display_errors', 'on');
		}
		(new Base())->get_conf('trigger');
		if(isset($_GET['image'])) (new ImageShower())->display();
		$setup = new Setup($controller);
		$run = $setup->run();
		return $run;
	},
	function (Exception $e) {
		$json_decode = json_decode($e->getMessage());
		if(!is_null($json_decode)) {
			header('Content-Type: application/json');
			exit($json_decode);
		}
		exit($e->getMessage());
	}
);