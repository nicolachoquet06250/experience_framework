<?php

namespace core;


use Exception;
use ReflectionClass;
use ReflectionException;

class DbContext extends Base {
	protected $db_sets = [];
	protected $database_name = '';
	protected $prefix;

	/**
	 * DbContext constructor.
	 *
	 * @param string $prefix
	 * @throws ReflectionException
	 * @throws Exception
	 */
	public function __construct($prefix = '') {
		$this->active_depencency_injection();
		$this->prefix = $prefix;
		DependenciesInjection::start(DependenciesInjection::CONTEXT, $this);

		$this->init_database_name();
		$this->init_db_sets();
	}

	/**
	 * @throws \ReflectionException
	 */
	protected function init_db_sets() {
		$ref_class = new ReflectionClass(get_class($this));
		foreach ($ref_class->getProperties() as $property => $detail) {
			if($detail->class !== DbContext::class) {
				$this->db_sets[$detail->getName()] = $detail->getValue();
			}
		}
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
}