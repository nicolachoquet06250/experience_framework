<?php

namespace core;

class Trigger extends Base {
	private $items;
	private static $instance = null;

	/**
	 * @return Trigger
	 */
	public static function create() {
		if(is_null(self::$instance)) {
			self::$instance = new Trigger();
		}
		return Trigger::$instance;
	}

	private function __construct() {
		$this->items = [];
	}

	public function trig($trigger_name, ...$arg_list) {
		if(!isset($this->items[$trigger_name])) {
			return false;
		}

		foreach ($this->items[$trigger_name] as $functions) {
			foreach ($functions as $function) {
				$this->run_callback($function, $arg_list);
			}
		}
		return true;
	}

	public function register($trigger_name, $function, $priority) {
		if (!isset($this->items[$trigger_name][$priority])) {
			$this->items[$trigger_name][$priority] = [];
		}
		$this->items[$trigger_name][$priority][] = $function;
		sort($this->items[$trigger_name]);
		return true;
	}

	public function get_trigger_data($trigger_name) {
		return isset($this->items[$trigger_name]) ? $this->items[$trigger_name] : [];
	}

	public function flush() {
		$this->items = [];
	}
}