<?php

namespace core;


class ClassFinder extends Base {
	public static function exists($class) {
		return class_exists($class);
	}
}