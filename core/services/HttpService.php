<?php

class HttpService extends Service implements IHttpService {
	protected $get;
	protected $post;
	protected $files;
	protected $response_header;
	protected $session;
	protected $server;

	/**
	 * @throws Exception
	 */
	public function initialize_after_injection() {
		$this->get = $_GET;
		$this->post = $_POST;
		$this->files = $_FILES;
		$this->response_header = isset($http_response_header) ? $http_response_header : null;
		$this->session = isset($_SESSION) ? $_SESSION : null;
		$this->server = isset($_SERVER) ? $_SERVER : null;
	}

	/**
	 * @param null|string $key
	 * @return array|null|string
	 */
	public function get($key = null) {
		if(is_null($key)) {
			return $this->get;
		}
		return isset($this->get[$key]) && $this->get[$key] !== '' ? $this->get[$key] : null;
	}

	/**
	 * @param null|string $key
	 * @return array|null|string
	 */
	public function post($key = null) {
		if(is_null($key)) {
			return $this->post;
		}
		return isset($this->post[$key]) && $this->post[$key] !== '' ? $this->post[$key] : null;
	}

	/**
	 * @param null|string $key
	 * @return array|null|string
	 */
	public function files($key = null) {
		if(is_null($key)) {
			return $this->files;
		}
		return isset($this->files[$key]) ? $this->files[$key] : null;
	}

	public function response_header($key, $value = null) {
		if(!is_null($value)) $this->response_header[$key] = $value;
		return isset($this->response_header[$key]) ? $this->response_header[$key] : null;
	}

	/**
	 * @param null $key
	 * @param null $value
	 * @return mixed|null
	 * @throws Exception
	 */
	public function session($key = null, $value = null) {
		if(!is_null($value)) $this->get_service('session')->set($key, $value);
		if(is_null($key)) {
			return $this->session;
		}
		return $this->get_service('session')->get($key);
	}

	public function server($key = null) {
		if(is_null($key)) {
			return $this->server;
		}
		return isset($this->server[$key]) ? $this->server[$key] : null;
	}
}