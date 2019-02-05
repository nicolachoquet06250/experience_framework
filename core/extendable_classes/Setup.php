<?php

class Setup extends Base implements ISetup {
	private $controller;
	/** @var JsonService $json_service */
	private $json_service;

	/**
	 * Setup constructor.
	 *
	 * @param $controller
	 * @throws Exception
	 */
	public function __construct($controller) {
		// Si la classe existe
		$external_confs = new External_confs(__DIR__.'/../../external_confs/custom.json');
		if(is_file($external_confs->get_controllers_dir().'/'.ucfirst($controller).'Controller.php')) {
			require_once $external_confs->get_controllers_dir().'/'.ucfirst($controller).'Controller.php';
		}
		elseif(is_file($external_confs->get_controllers_dir(false).'/'.ucfirst($controller).'Controller.php')) {
			require_once $external_confs->get_controllers_dir(false).'/'.ucfirst($controller).'Controller.php';
		}
		if(class_exists(ucfirst($controller).'Controller')) {
			// Je valorise ma propriété avec le paramètre
		 	$this->controller = $controller;
		}
		else {
			throw new Exception('Le controlleur '.$controller.' n\'existe pas !');
		}
		$this->json_service = $this->get_service('json');
	}

	/**
	 * @param null $arg
	 * @return false|string
	 */
	public function run($arg = null) {
		$controller = ucfirst($this->controller).'Controller';
		if(isset($_GET['action'])) {
			$action = $_GET['action'];
			// Supprime de la mémoire les paramètres GET pour récupérer uniquement
			// les paramètres voulus et ne pas avoir le nom de mon controlleur et de ma méthode.
			unset($_GET['action']);
		}
		else {
			$action = 'index';
		}
		unset($_GET['controller']);
		$parzms = $_GET;

		/** @var Controller $ctrl */
		$ctrl = new $controller($action, $parzms);
		$run = $ctrl->run();
		return $run;
	}
}