<?php

class OsService extends Service implements IOsService {
	protected $os;
	public function initialize_after_injection() {
		$this->os = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'windows' : 'unix';
	}

	public function IAmOnWindowsSystem() {
		return $this->os === 'windows';
	}

	public function IAmOnUnixSystem() {
		return $this->os === 'unix';
	}

	public function get_chariot_return() {
		return $this->IAmOnUnixSystem() ? "\n" : "\r";
	}

	public function get_chariot_return_2() {
		return $this->IAmOnUnixSystem() ? "\n" : "\n\r";
	}
}