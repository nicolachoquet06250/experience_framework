<?php

namespace core;


trait FactisSingleton {

	/** @return $this */
	public static function create() {
			$class = self::class;
			return new $class();
	}
}