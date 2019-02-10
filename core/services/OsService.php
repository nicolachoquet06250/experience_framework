<?php
namespace core;

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

	public function git_path() {
		return $this->IAmOnUnixSystem() ? 'git' : '"c:\Program Files\Git\bin\git.exe"';
	}

	public function php_path() {
		return $this->IAmOnUnixSystem() ? 'composer' : 'c:\Wamp64\bin\php\php.exe';
	}

	public function composer($phar) {
		return $phar ? $this->php_path().' composer.phar ' : 'composer';
	}
}