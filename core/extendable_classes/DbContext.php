<?php

namespace core;


use Exception;
use mysqli;
use mysqli_result;
use ReflectionClass;
use ReflectionException;

class DbContext extends Base {
	protected $db_sets = [];
	protected $database_name = '';
	protected $db_name = '';
	protected $prefix;
	/** @var mysqli $mysql */
	protected $mysql;

	/**
	 * DbContext constructor.
	 *
	 * @param string $prefix
	 * @throws ReflectionException
	 * @throws Exception
	 */
	public function __construct($prefix = '') {
		$external_conf = External_confs::create();
		$this->active_depencency_injection();
		$this->prefix = $prefix;
		$this->db_name = strtolower(str_replace(['core\\', $external_conf->get_git_repo()['directory'].'\\', 'Context'], '', get_class($this)));
		DependenciesInjection::start(DependenciesInjection::CONTEXT, $this);

		$this->init_database_name();
		$this->init_db_sets();
		/** @var MysqlService $mysql_service */
		$mysql_service = $this->get_service('mysql');
		$mysql_service->change_db($this->db_name);
		$this->mysql = $mysql_service->get_connector();
	}

	/**
	 * @throws ReflectionException
	 */
	protected function init_db_sets() {
		$ref_class = new ReflectionClass(get_class($this));
		foreach ($ref_class->getProperties() as $property => $detail) {
			if($detail->class !== DbContext::class) {
				$prop = $detail->getName();
				if(!is_null($this->$prop)) {
					$this->db_sets[$detail->getName()] = $this->$prop;
				}
			}
		}
	}

	/**
	 * @param $prefix
	 * @throws Exception
	 */
	protected function set_table_prefix($prefix) {
		$this->get_conf('mysql')->set('table-prefix', $prefix, false);
	}

	/**
	 * @throws Exception
	 */
	protected function init_database_name() {
		$external_confs = External_confs::create();
		$this->database_name = str_replace(['Context', 'core\\', $external_confs->get_git_repo()['directory'].'\\'], '', get_class($this));
		$this->database_name = strtolower($this->database_name);
	}

	public function __get($name) {
		if(isset($this->db_sets[$name])) {
			return $this->db_sets[$name];
		}
		elseif (isset($this->$name)) {
			return $this->$name;
		}
		else {
			return null;
		}
	}

	public function set_db_prefix($prefix) {
		$this->prefix = $prefix;
	}

	/**
	 * @return Repository[]
	 */
	public function get_db_sets() {
		return $this->db_sets;
	}

	/**
	 * @param $db_set
	 * @return Repository
	 */
	public function get_db_set($db_set) {
		return $this->$db_set;
	}

	/**
	 * @return bool|mysqli_result
	 * @throws Exception
	 */
	public function create_database() {
		return $this->mysql->query('CREATE DATABASE IF NOT EXISTS '.$this->get_db_name());
	}

	/**
	 * @param bool $for_include
	 * @return string
	 * @throws Exception
	 */
	public function get_db_name($for_include = true) {
		$name = '';
		if($for_include) {
			$name .= '`';
		}

		if ($this->prefix !== '') {
			$name .= $this->prefix.'_';
		}

		$name .= $this->db_name;

		if($for_include) {
			$name .= '`';
		}

		return $name;
	}
}