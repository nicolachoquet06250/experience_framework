<?php
header("Access-Control-Allow-Origin: *");
ini_set('display_errors', 'on');
require_once __DIR__.'/autoload.php';

echo core\Router::create($_SERVER['REQUEST_URI'],
	function (string $controller) {
		(new \core\Base())->get_conf('trigger');
		if(isset($_GET['image'])) (new core\ImageShower())->display();
		if(isset($_GET['debug'])) {
			ini_set('display_errors', 'on');
		}
		$setup = new core\Setup($controller);
		$run = $setup->run();
		return $run;
	},
	function (Exception $e) {
		exit((new core\ErrorController('_500', []))->message($e->getMessage())->display());
	}
);