<?php

class Base implements IBase {
	private static $confs = [];

	private static $queues_loader;

	protected function active_depencency_injection() {
		require_once __DIR__.'/autoload_for_dependencies_injection.php';
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	protected function get_controllers() {
		$external_conf = new External_confs(__DIR__.'/../../external_confs/custom.json');
		$directory = $external_conf->get_controllers_dir();
		$dir = opendir($directory);
		$controllers = [];
		while (($elem = readdir($dir)) !== false) {
			if($elem !== '.' && $elem !== '..') {
				$controllers[] = strtolower(str_replace('Controller.php', '', $elem));
			}
		}

		$directory = $external_conf->get_controllers_dir(false);
		$dir = opendir($directory);
		while (($elem = readdir($dir)) !== false) {
			if($elem !== '.' && $elem !== '..') {
				$controllers[] = strtolower(str_replace('Controller.php', '', $elem));
			}
		}
		return $controllers;
	}

	/**
	 * @param $model
	 * @return Model
	 * @throws Exception
	 */
	public function get_model(string $model) {
		$external_conf = new External_confs(__DIR__.'/../../external_confs/custom.json');
		$model = ucfirst($model).'Model';
		if(file_exists($external_conf->get_models_dir().'/'.$model.'.php')) {
			require_once $external_conf->get_models_dir().'/'.$model.'.php';
			return new $model();
		}
		elseif(file_exists($external_conf->get_models_dir(false).'/'.$model.'.php')) {
			require_once $external_conf->get_models_dir(false).'/'.$model.'.php';
			return new $model();
		}
		else {
			throw new Exception('La classe '.$model.' n\'existe pas !');
		}
	}

	/**
	 * @param $service
	 * @return Service
	 * @throws Exception
	 */
	public function get_service(string $service) {
		$external_conf = new External_confs(__DIR__.'/../../external_confs/custom.json');
		$service = ucfirst($service).'Service';
		if(file_exists($external_conf->get_services_dir().'/'.$service.'.php')) {
			require_once $external_conf->get_services_dir(true, true).'/I'.$service.'.php';
			require_once $external_conf->get_services_dir().'/'.$service.'.php';
			/** @var Service $o_service */
			$o_service = new $service();
			$o_service->initialize_after_injection();
			return $o_service;
		}
		elseif(file_exists($external_conf->get_services_dir(false).'/'.$service.'.php')) {
			require_once $external_conf->get_services_dir(false, true).'/I'.$service.'.php';
			require_once $external_conf->get_services_dir(false).'/'.$service.'.php';
			/** @var Service $o_service */
			$o_service = new $service();
			$o_service->initialize_after_injection();
			return $o_service;
		}
		else {
			throw new Exception('La classe '.$service.' n\'existe pas !');
		}
	}

	/**
	 * @param $dao
	 * @return Repository
	 * @throws Exception
	 */
	public function get_dao(string $dao) {
		return $this->get_repository($dao);
	}

	/**
	 * @param $repository
	 * @return Repository
	 * @throws Exception
	 */
	public function get_repository(string $repository) {
		$external_conf = new External_confs(__DIR__.'/../../external_confs/custom.json');
		$repository = ucfirst($repository).'Dao';
		if (file_exists($external_conf->get_dao_dir().'/'.$repository.'.php')) {
			require_once $external_conf->get_dao_dir().'/'.$repository.'.php';
			return new $repository();
		}
		throw new Exception('La classe '.$repository.' n\'existe pas !');
	}

	/**
	 * @param string $entity
	 * @return Entity
	 * @throws Exception
	 */
	public function get_entity(string $entity) {
		$external_conf = new External_confs(__DIR__.'/../../external_confs/custom.json');
		$entity = ucfirst($entity).'Entity';
		if(file_exists($external_conf->get_entities_dir().'/'.$entity.'.php')) {
			require_once $external_conf->get_entities_dir().'/'.$entity.'.php';
			return new $entity();
		}
		throw new Exception('\'La classe '.$entity.' n\'existe pas !');
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	protected function get_entities() {
		$external_conf = new External_confs(__DIR__.'/../../external_confs/custom.json');
		$dir = opendir($external_conf->get_entities_dir());
		$entities = [];
		while (($elem = readdir($dir)) !== false) {
			if($elem !== '.' && $elem !== '..') {
				$entities[] = strtolower(str_replace('Entity.php', '', $elem));
			}
		}
		return $entities;
	}

	/**
	 * @param string $conf
	 * @return Conf
	 * @throws Exception
	 */
	public function get_conf(string $conf) {
		$external_conf = new External_confs(__DIR__.'/../../external_confs/custom.json');
		$conf = ucfirst($conf).'Conf';
		if(file_exists($external_conf->get_conf_dir().'/'.$conf.'.php')) {
			if(!isset(self::$confs[$conf])) {
				require_once $external_conf->get_conf_dir().'/'.$conf.'.php';
				self::$confs[$conf] = new $conf();
			}
			return self::$confs[$conf];
		}
		elseif(file_exists($external_conf->get_conf_dir(false).'/'.$conf.'.php')) {
			if(!isset(self::$confs[$conf])) {
				require_once $external_conf->get_conf_dir(false).'/'.$conf.'.php';
				self::$confs[$conf] = new $conf();
			}
			return self::$confs[$conf];
		}
		throw new Exception('\'La classe '.$conf.' n\'existe pas !');
	}

	/**
	 * @param string $type
	 * @param mixed $object
	 * @return Response
	 * @throws Exception
	 */
	protected function get_response($object, $type = Response::JSON) {
		return Response::create($object, $type);
	}

	public function toArrayForJson($recursive = true) {
		return [];
	}

	/**
	 * @return \mvc_framework\core\queues\ModuleLoader
	 * @throws Exception
	 */
	public function queues_loader() {
		if(!class_exists(\mvc_framework\core\queues\ModuleLoader::class)) {
			throw new Exception('plugin `queues` not found');
		}
		if(is_null(self::$queues_loader)) {
			self::$queues_loader = new \mvc_framework\core\queues\ModuleLoader();
		}
		return self::$queues_loader;
	}
}