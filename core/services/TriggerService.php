<?php

namespace core;


class TriggerService extends Service implements ITriggerService {
	/** @var Trigger $trigger_manager */
	private $trigger_manager;

	public function initialize_after_injection() {
		$this->trigger_manager = Trigger::create();
	}

	public function trig($trigger_name, ...$arg_list) {
		$args = [];
		foreach ($arg_list as $key => $arg) {
			if(is_string($arg)) {
				$args[] = '"'.$arg.'"';
			}
			elseif (is_numeric($arg)) {
				$args[] = $arg;
			}
			elseif (is_array($arg) || is_object($arg)) {
				$args[] = '$arg_list['.$key.']';
			}
		}
		$response = null;
		eval('$response = $this->trigger_manager->trig($trigger_name, '.implode(', ', $args).');');
		return $response;
	}

	public function register($trigger_name, $function, $priority = 1) {
		return $this->trigger_manager->register($trigger_name, $function, $priority);
	}

	public function get_trigger_data($trigger_name) {
		return $this->trigger_manager->get_trigger_data($trigger_name);
	}

	public function flush() {
		$this->trigger_manager->flush();
	}
}