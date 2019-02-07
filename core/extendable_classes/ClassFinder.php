<?php


class ClassFinder extends Base {
	public static function exists($class) {
		return class_exists($class);
	}
}