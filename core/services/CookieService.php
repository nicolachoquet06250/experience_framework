<?php
namespace core;


class CookieService extends Service implements ICookieService {

	public function initialize_after_injection() {

	}

	public function set(string $key, $expire, $value, $domain = '') {
		setcookie($key, $value, $expire, '/', $domain);
		$_COOKIE[$key] = $value;
	}

	public function get(string $key) {
		return $this->has_key($key) ? $_COOKIE[$key] : null;
	}

	public function remove(string $key, $domain = '') {
		if($this->has_key($key)) {
			setcookie($key, '', 1, '/', $domain);
			unset($_COOKIE[$key]);
		}
	}

	public function has_key(string $key) {
		return isset($_COOKIE[$key]);
	}
}
