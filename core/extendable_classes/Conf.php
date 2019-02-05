<?php

class Conf extends Base implements IConf {
	protected $conf;
	public function get($key) {
		return $this->has_property($key) ? $this->conf[$key] : null;
	}

	/**
	 * @param $key
	 * @param $value
	 * @param bool $locked
	 * @throws Exception
	 */
	public function set($key, $value, $locked = true) {
		if($locked) {
			throw new Exception('Vous ne pouvez pas setter la propriété '.$key.' !!');
		}
		$this->conf[$key] = $value;
	}

	public function get_all() {
		return $this->conf;
	}

	/**
	 * @param $key
	 * @param bool $throw_except
	 * @param bool $locked
	 * @return bool
	 * @throws Exception
	 */
	public function remove_property($key, $throw_except = true, $locked = true) {
		if($locked) {
			if($throw_except) {
				throw new Exception('Vous ne pouvez pas supprimer la propriété '.$key.' !!');
			}
			return false;
		}
		if(!$this->has_property($key)) {
			if($throw_except)
				throw new Exception('La propriété que vous esseyez de supprimer n\'existe pas !');
			else
				return false;
		}
		unset($this->conf[$key]);
		return true;
	}

	public function has_property($key) {
		return isset($this->conf[$key]);
	}
}