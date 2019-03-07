<?php

namespace core;

use Exception;

class cmd extends Base implements ICmd {
	private $args;
	private $mysql;

	/**
	 * cmd constructor.
	 *
	 * @param $args
	 * @throws Exception
	 */
	public function __construct($args) {
		$this->active_depencency_injection();
		$this->args = $args;
		$this->clean_args();
		/** @var MysqlService $mysql_service */
		$mysql_service = $this->get_service('mysql');
		$this->mysql = $mysql_service->get_connector();
	}

	protected function get_arg($key) {
		$arg = isset($this->args[$key]) ? $this->args[$key] : null;
		if(ctype_digit($arg)) {
			return intval($arg);
		}
		if($arg === 'true' || $arg === 'false') {
			return $arg === 'true' ? true : false;
		}
		return $arg;

	}

	protected function clean_args() {
		$args = [];
		foreach ($this->args as $arg) {
			$args[explode('=', $arg)[0]] = explode('=', $arg)[1];
		}
		$this->args = $args;
	}

	/**
	 * @param $method
	 * @return mixed
	 * @throws Exception
	 */
	public function run($method) {
		if (in_array($method, get_class_methods(get_class($this)))) {
			return DependenciesInjection::start(DependenciesInjection::COMMAND, $this, $method);
		}
		throw new Exception('La commande '.get_class($this).'::'.$method.'() n\'existe pas !!');
	}

	protected function get_mysql() {
		return $this->mysql;
	}

	protected function has_arg($key) {
		return isset($this->args[$key]);
	}
}