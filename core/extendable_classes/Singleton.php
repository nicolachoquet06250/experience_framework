<?php

namespace core;


trait Singleton {
	private static $instance = null;

	private function __construct() {}

	/** @return $this */
	public static function create() {
		if(is_null(self::$instance)) {
			$class = self::class;
			self::$instance = new $class();
		}
		return self::$instance;
	}
}