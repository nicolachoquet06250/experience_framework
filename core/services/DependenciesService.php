<?php

	class DependenciesService extends Service implements IDependenciesService {
		private $git_dependencies = [];

		/**
		 * @throws Exception
		 */
		public function initialize_after_injection() {
			/** @var DependenciesConf $dependencies_conf */
			$dependencies_conf = $this->get_conf('dependencies');
			$this->git_dependencies = $dependencies_conf->get_all();
		}

		public function get_dependencies() {
			return $this->git_dependencies;
		}

		public function get_dependency_url($key) {
			return $this->git_dependencies[$key];
		}

		public function has_dependency($key) {
			return isset($this->git_dependencies[$key]);
		}
	}