<?php
	interface IDependenciesService extends IService {
		public function get_dependencies();
		public function get_dependency_url($key);
		public function has_dependency($key);
	}