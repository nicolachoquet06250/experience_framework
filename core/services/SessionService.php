<?php
session_start();

class SessionService extends Service implements ISessionService {

	/**
	 * @throws Exception
	 */
	public function initialize_after_injection() {}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @param null $domain
	 */
	public function set(string $key, $value, $domain = null) {
		$_SESSION[$key] = $value;
	}

	/**
	 * @param string $key
	 * @return mixed|null
	 */
	public function get(string $key) {
		return $this->has_key($key) ? $_SESSION[$key] : null;
	}

	/**
	 * @param null $session_id
	 * @return string|void
	 */
	public function id($session_id = null) {
		if(is_null($session_id)) {
			return session_id();
		}
		session_id($session_id);
	}

	/**
	 * @param null $session_name
	 * @return string|void
	 */
	public function name($session_name = null) {
		if(is_null($session_name)) {
			return session_name();
		}
		session_name($session_name);
	}

 	/**
	 * @param string $key
	 */
	public function remove(string $key) {
		if($this->has_key($key)) {
			unset($_SESSION[$key]);
		}
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function has_key(string $key) {
		return isset($_SESSION[$key]);
	}
}