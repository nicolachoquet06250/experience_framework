<?php

namespace core;

use Exception;

class Base implements IBase {
	private static $confs = [];

	private static $queues_loader;

	public static $authentication_key = 'tresterrzegdghgdfdshdfhfshfshfs';

	protected function active_depencency_injection() {
		require_once __DIR__.'/autoload_for_dependencies_injection.php';
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	protected function get_controllers() {
		$external_conf = External_confs::create();
		$directory = $external_conf->get_controllers_dir();
		$dir = opendir($directory);
		$controllers = [];
		while (($elem = readdir($dir)) !== false) {
			if($elem !== '.' && $elem !== '..') {
				$controllers[] = strtolower(str_replace(['\\'.$external_conf->get_git_repo()['directory'].'\\', 'Controller.php'], '', $elem));
			}
		}

		$directory = $external_conf->get_controllers_dir(false);
		$dir = opendir($directory);
		while (($elem = readdir($dir)) !== false) {
			if($elem !== '.' && $elem !== '..') {
				$controllers[] = strtolower(str_replace(['\\core\\', 'Controller.php'], '', $elem));
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
		$external_conf = External_confs::create();
		$model = ucfirst($model).'Model';
		if(file_exists($external_conf->get_models_dir().'/'.$model.'.php')) {
			if(is_file($external_conf->get_models_dir(false).'/'.$model.'.php')) {
				require_once $external_conf->get_models_dir(false).'/'.$model.'.php';
			}
			require_once $external_conf->get_models_dir().'/'.$model.'.php';
			$namespace = '\\'.$external_conf->get_git_repo()['directory'];
			$model = $namespace.'\\'.$model;
			return new $model();
		}
		elseif(file_exists($external_conf->get_models_dir(false).'/'.$model.'.php')) {
			require_once $external_conf->get_models_dir(false).'/'.$model.'.php';
			$namespace = '\\core';
			$model = $namespace.'\\'.$model;
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
		$external_conf = External_confs::create();
		$service = ucfirst($service).'Service';

		if(file_exists($external_conf->get_services_dir().'/'.$service.'.php')) {
			if(is_file($external_conf->get_services_dir(false, true).'/I'.$service.'.php')) {
				require_once $external_conf->get_services_dir(false, true).'/I'.$service.'.php';
			}
			if(is_file($external_conf->get_services_dir(false).'/'.$service.'.php')) {
				require_once $external_conf->get_services_dir(false).'/'.$service.'.php';
			}
			require_once $external_conf->get_services_dir(true, true).'/I'.$service.'.php';
			require_once $external_conf->get_services_dir().'/'.$service.'.php';
			$namespace = '\\'.$external_conf->get_git_repo()['directory'];
			/** @var Service $o_service */
			$service = $namespace.'\\'.$service;
			$o_service = new $service();
			$o_service->initialize_after_injection();
			return $o_service;
		}
		elseif(file_exists($external_conf->get_services_dir(false).'/'.$service.'.php')) {
			require_once $external_conf->get_services_dir(false, true).'/I'.$service.'.php';
			require_once $external_conf->get_services_dir(false).'/'.$service.'.php';
			$namespace = '\\core';
			/** @var Service $o_service */
			$service = $namespace.'\\'.$service;
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
		$external_conf = External_confs::create();
		$repository = ucfirst($repository).'Dao';
		if (file_exists($external_conf->get_dao_dir().'/'.$repository.'.php')) {
			require_once $external_conf->get_dao_dir().'/'.$repository.'.php';
			$namespace = '\\'.$external_conf->get_git_repo()['directory'];
			$repository = $namespace.'\\'.$repository;
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
		$external_conf = External_confs::create();
		$entity = ucfirst($entity).'Entity';
		if(file_exists($external_conf->get_entities_dir().'/'.$entity.'.php')) {
			require_once $external_conf->get_entities_dir().'/'.$entity.'.php';
			$namespace = '\\'.$external_conf->get_git_repo()['directory'];
			$entity = $namespace.'\\'.$entity;
			return new $entity();
		}
		throw new Exception('\'La classe '.$entity.' n\'existe pas !');
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	protected function get_entities() {
		$external_conf = External_confs::create();
		$dir = opendir($external_conf->get_entities_dir());
		$entities = [];
		while (($elem = readdir($dir)) !== false) {
			if($elem !== '.' && $elem !== '..') {
				$entities[] = strtolower(str_replace(['\\'.$external_conf->get_git_repo()['directory'], 'Entity.php'], '', $elem));
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
		$external_conf = External_confs::create();
		$conf = ucfirst($conf).'Conf';
		if(file_exists($external_conf->get_conf_dir().'/'.$conf.'.php')) {
			if(!isset(self::$confs[$conf])) {
				if(is_file($external_conf->get_conf_dir(false).'/'.$conf.'.php')) {
					require_once $external_conf->get_conf_dir(false).'/'.$conf.'.php';
				}
				require_once $external_conf->get_conf_dir().'/'.$conf.'.php';
				$namespace = '\\'.$external_conf->get_git_repo()['directory'];
				$conf = $namespace.'\\'.$conf;
				self::$confs[$conf] = new $conf();
			}
			return self::$confs[$conf];
		}
		elseif(file_exists($external_conf->get_conf_dir(false).'/'.$conf.'.php')) {
			if(!isset(self::$confs[$conf])) {
				require_once $external_conf->get_conf_dir(false).'/'.$conf.'.php';
				$namespace = '\\core';
				$conf = $namespace.'\\'.$conf;
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

	protected function run_callback($function, $arg_list = []) {
		$args = [];
		foreach ($arg_list as $key => $arg) {
			if(is_string($arg)) {
				$args[] = '"'.$arg.'"';
			}
			elseif (is_numeric($arg)) {
				$args[] = $arg;
			}
			elseif (is_array($arg) || is_object($arg)) {
				$args[] = '$arg_list['.$key.']';
			}
		}
		if(is_string($function)) {
			eval($function.'('.implode(', ', $args).');');
		}
		elseif ($function instanceof \Closure) {
			$args = empty($args) ? '' : ', '.implode(', ', $args);
			eval('$function->call($this'.$args.');');
		}
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	protected function get_contexts() {
		$external_conf = External_confs::create();
		$directory = $external_conf->get_contexts_dir();
		$dir = opendir($directory);
		$contexts = [];

		while (($elem = readdir($dir)) !== false) {
			if($elem !== '.' && $elem !== '..') {
				$contexts[] = strtolower(str_replace(['\\'.$external_conf->get_git_repo()['directory'].'\\', 'Context.php'], '', $elem));
			}
		}

		$directory = $external_conf->get_contexts_dir(false);
		$dir = opendir($directory);
		while (($elem = readdir($dir)) !== false) {
			if($elem !== '.' && $elem !== '..') {
				$contexts[] = strtolower(str_replace(['\\core\\', 'Context.php'], '', $elem));
			}
		}
		return $contexts;
	}

	/**
	 * @param $context
	 * @param string $db_prefix
	 * @return DbContext
	 * @throws Exception
	 */
	protected function get_context($context, $db_prefix = '') {
		$external_conf = External_confs::create();
		$context = ucfirst($context).'Context';
		if(is_file($external_conf->get_contexts_dir().'/'.$context.'.php')) {
			require_once $external_conf->get_contexts_dir().'/'.$context.'.php';
			$namespace = $external_conf->get_git_repo()['directory'].'\\';
			$context = $namespace.$context;
			return new $context($db_prefix);
		}
		elseif(is_file($external_conf->get_contexts_dir(false).'/'.$context.'.php')) {
			require_once $external_conf->get_contexts_dir(false).'/'.$context.'.php';
			$namespace = 'core\\';
			$context = $namespace.$context;
			return new $context($db_prefix);
		}
	}
}