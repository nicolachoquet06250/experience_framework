<?php

namespace core;


use ReflectionClass;
use ReflectionException;

class DependenciesInjection {
	const CONTROLLER = 'controller';
	const COMMAND = 'command';

	const callbacks = [
		self::CONTROLLER => '\core\DependenciesInjection::controller',
		self::COMMAND => '\core\DependenciesInjection::command',
	];

	private static $method_parameters = [];

	/**
	 * @param $type
	 * @param $object
	 * @param $method
	 * @return mixed|Response
	 */
	public static function start($type, $object, $method) {
		$local_method = self::callbacks[$type];
		return $local_method($object, $method);
	}

	/**
	 * @param $object
	 * @param $method
	 * @throws ReflectionException
	 * @throws \Exception
	 */
	private static function common_execution($object, $method) {
		$external_confs = new External_confs(__DIR__.'/../../external_confs/custom.json');
		$ref_class             = new ReflectionClass(get_class($object));
		$ref_method            = $ref_class->getMethod($method);
		$ref_method_parameters = $ref_method->getParameters();

		self::$method_parameters = [];

		$_properties = [];

		$properties = $ref_class->getProperties();

		foreach ($properties as $property) {
			if ($property->isPublic()) {
				preg_match('`@var ([A-Za-z0-9\_]+) \$([A-Za-z0-9\_]+)`', $property->getDocComment(), $matches);
				if (!empty($matches)) {
					$_properties[$matches[2]] = $matches[1];
				}
			}
		}

		foreach ($ref_method_parameters as $ref_method_parameter) {
			$class      = $ref_method_parameter->getClass();
			$class_name = $class->getName();

			$condition_class = $class->getName();
			$condition_obj = new $condition_class();

			if($condition_obj instanceof Service) {
				$class_name          = str_replace('Service', '', $class_name);
				$class_name			 = str_replace(['core\\', $external_confs->get_git_repo()['directory'].'\\'], '', $class_name);
				$class_name          = strtolower($class_name);
				self::$method_parameters[] = '$object->get_service(\''.$class_name.'\')';
			}
			elseif ($condition_obj instanceof Conf) {
				$class_name          = str_replace('Conf', '', $class_name);
				$class_name			 = str_replace(['core\\', $external_confs->get_git_repo()['directory'].'\\'], '', $class_name);
				$class_name          = strtolower($class_name);
				self::$method_parameters[] = '$object->get_conf(\''.$class_name.'\')';
			}
			elseif ($condition_obj instanceof Entity) {
				$class_name          = str_replace('Entity', '', $class_name);
				$class_name			 = str_replace(['core\\', $external_confs->get_git_repo()['directory'].'\\'], '', $class_name);
				$class_name          = strtolower($class_name);
				self::$method_parameters[] = '$object->get_entity(\''.$class_name.'\')';
			}
			elseif ($condition_obj instanceof Model) {
				$class_name          = str_replace('Model', '', $class_name);
				$class_name			 = str_replace(['core\\', $external_confs->get_git_repo()['directory'].'\\'], '', $class_name);
				$class_name          = strtolower($class_name);
				self::$method_parameters[] = '$object->get_model(\''.$class_name.'\')';
			}
			elseif ($condition_obj instanceof Repository) {
				$class_name          = str_replace('Dao', '', $class_name);
				$class_name			 = str_replace(['core\\', $external_confs->get_git_repo()['directory'].'\\'], '', $class_name);
				$class_name          = strtolower($class_name);
				self::$method_parameters[] = '$object->get_dao(\''.$class_name.'\')';
			}
		}

		foreach ($_properties as $property => $_class) {
			if (strstr($_class, 'Service')) {
				$class_name          = str_replace('Service', '', $_class);
				$class_name			 = str_replace(['core\\', $external_confs->get_git_repo()['directory'].'\\'], '', $class_name);
				$class_name          = strtolower($class_name);
				$method_name         = 'get_service';
				$object->$property = $object->$method_name($class_name);
			} elseif (strstr($_class, 'Conf')) {
				$class_name      = str_replace('Conf', '', $_class);
				$class_name			 = str_replace(['core\\', $external_confs->get_git_repo()['directory'].'\\'], '', $class_name);
				$class_name      = strtolower($class_name);
				$method_name     = 'get_conf';
				$object->$property = $object->$method_name($class_name);
			} elseif (strstr($_class, 'Model')) {
				$class_name      = str_replace('Model', '', $_class);
				$class_name			 = str_replace(['core\\', $external_confs->get_git_repo()['directory'].'\\'], '', $class_name);
				$class_name      = strtolower($class_name);
				$method_name     = 'get_model';
				$object->$property = $object->$method_name($class_name);
			} elseif (strstr($_class, 'Dao')) {
				$class_name      = str_replace('Dao', '', $_class);
				$class_name			 = str_replace(['core\\', $external_confs->get_git_repo()['directory'].'\\'], '', $class_name);
				$class_name      = strtolower($class_name);
				$method_name     = 'get_dao';
				$object->$property = $object->$method_name($class_name);
			} elseif (strstr($_class, 'Entity')) {
				$class_name      = str_replace('Entity', '', $_class);
				$class_name			 = str_replace(['core\\', $external_confs->get_git_repo()['directory'].'\\'], '', $class_name);
				$class_name      = strtolower($class_name);
				$method_name     = 'get_entity';
				$object->$property = $object->$method_name($class_name);
			}
		}
	}

	/**
	 * @param $object
	 * @param $method
	 * @return Response
	 * @throws \ReflectionException
	 */
	public static function controller($object, $method) {
		if(get_class($object) !== ErrorController::class) {
			self::common_execution($object, $method);
		}
		else {
			self::$method_parameters = [];
		}
		/** @var Response $response */
		eval('$response = $object->'.$method.'('.implode(', ', self::$method_parameters).');');
		return $response;
	}

	/**
	 * @param $object
	 * @param $method
	 * @return mixed
	 * @throws \ReflectionException
	 */
	public static function command($object, $method) {
		self::common_execution($object, $method);
		/** @var mixed $response */
		eval('$response = $object->'.$method.'('.implode(', ', self::$method_parameters).');');
		return $response;
	}
}