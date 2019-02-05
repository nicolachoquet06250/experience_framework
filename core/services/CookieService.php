<?php


class CookieService extends Service implements ICookieSession {

	public function initialize_after_injection() {

	}

	public function set(string $key, $value, $domain = '') {
		setcookie($key, $value, 0, '', $domain);
	}

	public function get(string $key) {
		return $this->has_key($key) ? $_COOKIE[$key] : null;
	}

	public function remove(string $key) {
		if($this->has_key($key)) {
			unset($_SESSION[$key]);
		}
	}

	public function has_key(string $key) {
		return isset($_COOKIE[$key]);
	}
}
