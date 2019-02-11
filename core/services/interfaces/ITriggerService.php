<?php

namespace core;


interface ITriggerService extends IService {
	public function trig($trigger_name, ...$arg_list);

	public function register($trigger_name, $function, $priority = 1);

	public function get_trigger_data($trigger_name);

	public function flush();
}