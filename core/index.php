<?php
header("Access-Control-Allow-Origin: *");
require_once __DIR__.'/autoload.php';

if(isset($_GET['debug'])) {
	ini_set('display_errors', 'on');
}
echo core\Router::create($_SERVER['REQUEST_URI'],
	function (string $controller) {
		(new \core\Base())->get_conf('trigger');
		if(isset($_GET['image'])) (new core\ImageShower())->display();
		$setup = new core\Setup($controller);
		$run = $setup->run();
		return $run;
	},
	function (Exception $e) {
		exit((new core\ErrorController('_500', []))->message($e->getMessage())->display());
	}
);