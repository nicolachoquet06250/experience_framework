<?php

interface IConf {
	public function get($key);

	public function set($key, $value, $locked = true);

	public function remove_property($key, $throw_except = true, $locked = true);

	public function has_property($key);
}