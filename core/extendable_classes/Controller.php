<?php

namespace core;

use Exception;

abstract class Controller extends Base implements IController {
	private $method;
	protected $params;
	/** @var HttpService $http_service */
	protected $http_service;
	/** @var ErrorController $http_error */
	protected $http_error;

	const API = 'api';
	const WWW = 'www';

	/**
	 * Controller constructor.
	 *
	 * @param $action
	 * @param $params
	 * @throws Exception
	 */
	public function __construct($action, $params) {
		$this->active_depencency_injection();
		$current_class = get_class($this);
		$class_methods = get_class_methods($current_class);
		$this->http_service = $this->get_service('http');

		if(in_array($action, $class_methods)) {
			$this->method = $action;
			$this->params = $params;
		}
		else $this->http_error = $this->PAGE_NOT_FOUND(get_class($this).'::'.$action.'() method not found !');
	}

	/**
	 * @return Response
	 */
	abstract protected function index();

	/**
	 * @param null $arg
	 * @return string
	 * @throws Exception
	 */
	public function run($arg = null) {
		$method = $this->method;
		if($this->http_error) {
			return $this->http_error->display();
		}
		return DependenciesInjection::start(DependenciesInjection::CONTROLLER, $this, $method)->display();
	}

	/**
	 * @param string $key
	 * @return array|string|null
	 */
	protected function get($key = null) {
		return $this->http_service->get($key);
	}

	/**
	 * @param string $key
	 * @return array|string|null
	 */
	protected function post($key = null) {
		return $this->http_service->post($key);
	}

	/**
	 * @param string $key
	 * @return array|string|null
	 */
	protected function files($key = null) {
		return $this->http_service->files($key);
	}

	/**
	 * @param int $code
	 * @return ErrorController
	 * @throws Exception
	 */
	protected function get_error_controller(int $code) {
		return new ErrorController('_'.$code, []);
	}

	private function api_sudomain() {
		$domain = $_SERVER['HTTP_HOST'];
		$domain = explode('.', $domain);
		if(count($domain) === 3) {
			$subdomain = $domain[0];
			$domain    = $domain[1].'.'.$domain[2];
		}
		else {
			$subdomain = null;
			$domain = $domain[0].'.'.$domain[1];
		}
		if($subdomain === 'api' || $subdomain === 'ws') {
			return [$subdomain, $domain];
		}
		return [$domain];
	}

	protected function get_base_url($type = self::API) {
		$base = 'http://';
		$api_sub_domain = $this->api_sudomain();
		if(count($api_sub_domain) === 2) {
			list(, $domain) = $api_sub_domain;
			if($type === self::WWW) {
				$base .= self::WWW;
			}
			else {
				$base .= self::API;
			}
			$base .= '.'.$domain;
		}
		else {
			list($domain) = $api_sub_domain;
			$base .= $domain.($type === self::API ? '/api' : '');
		}
		return $base;
	}

	protected function get_base_url_api() {
		return $this->get_base_url(self::API);
	}

	protected function get_base_url_www() {
		return $this->get_base_url(self::WWW);
	}

	/**
	 * @param $message
	 * @param string $return_type
	 * @return Response
	 * @throws Exception
	 */
	protected function OK($message, $return_type = Response::JSON) {
		return $this->get_response($message, $return_type);
	}

	/**
	 * @param $message
	 * @return ErrorController
	 * @throws Exception
	 */
	protected function FORBIDDEN($message) {
		return $this->get_error_controller(403)->message($message);
	}

	/**
	 * @param $message
	 * @return ErrorController
	 * @throws Exception
	 */
	protected function NOT_AUTHENTICATED_USER($message) {
		return $this->get_error_controller(401)->message($message);
	}

	/**
	 * @param $message
	 * @return ErrorController
	 * @throws Exception
	 */
	protected function PAGE_NOT_FOUND($message) {
		return $this->get_error_controller(404)->message($message);
	}

	/**
	 * @param $message
	 * @return ErrorController
	 * @throws Exception
	 */
	protected function INTERNAL_ERROR($message) {
		return $this->get_error_controller(500)->message($message);
	}

	/**
	 * @param $message
	 * @return ErrorController
	 * @throws Exception
	 */
	protected function SERVER_ERROR($message) {
		return $this->get_error_controller(503)->message($message);
	}

	/**
	 * @param $message
	 * @return ErrorController
	 * @throws Exception
	 */
	protected function SERVER_NOT_RESPOND($message) {
		return $this->get_error_controller(504)->message($message);
	}

	/**
	 * @param string $url
	 * @param string $message
	 */
	protected function PERMANENTLY_REDIRECT($url, $message = 'Move Permanently') {
		header("Status: 301 ".$message, false, 301);
		header("Location: ".$url);
	}

	/**
	 * @param string $url
	 * @param string $message
	 */
	protected function TEMPORARY_REDIRECT($url, $message = 'Move Permanently') {
		header("Status: 302 ".$message, false, 302);
		header("Location: ".$url);
	}
}