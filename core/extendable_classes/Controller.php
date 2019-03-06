<?php

namespace core;

use Exception;
use MiladRahimi\Jwt\Authentication\Subdomains;
use ReflectionClass;

abstract class Controller extends Base implements IController {
	private $method;
	protected $params;
	/** @var HttpService $http_service */
	protected $http_service;
	/** @var ErrorController $http_error */
	protected $http_error;
	protected $external_conf;
	/** @var Subdomains $sub_domains */
	protected $sub_domains;

	const API = 'api';
	const WWW = 'www';

	public function is_https() {
		return $_SERVER['HTTPS'] === 'on';
	}

	protected function get_alias() {
		$ctrl = get_class($this);
		$ctrl = str_replace('\\', '/', $ctrl);
		$ctrl = basename($ctrl);
		$ctrl = str_replace('Controller', '', $ctrl);
		$ctrl = strtolower($ctrl);
		return $ctrl;
	}

	protected function get_action() {
		return $this->method;
	}

	protected function get_referer() {
		return $this->get('referer');
	}

	/**
	 * @param null|string $role
	 * @return bool|ErrorController
	 * @throws Exception
	 */
	private function run_if_authenticated() {
		$role = null;

		$ref = new ReflectionClass(get_class($this));
		if($ref->hasMethod($this->get_action())) {
			$action_doc = $ref->getMethod($this->get_action())->getDocComment();
			$auth_activated = false;

			if (preg_match('`@[A|a]uthenticated(\(([a-zA-Z0-9\_\,\ ]+)\))?`', $action_doc, $matches)) {
				$auth_activated = true;
				if (isset($matches[2])) {
					$role = $matches[2];
					if(preg_match('`\,\ `', $role, $_matches)) {
						$role = explode(', ', $role);
					}
				}
			}

			if (!AuthenticationService::is_logged() && !FacebookService::is_logged() && $auth_activated) {
				$this->http_error = $this->NOT_AUTHENTICATED_USER('You must be authenticated'.(!is_null($role) ? ' on '.(is_array($role) ? implode(' or ', $role) : $role).' role' : ''));
				return false;
			}

			if(AuthenticationService::is_logged()) {
				/** @var AuthenticationService $authService */
				$authService = $this->get_service('authentication');
			}
			elseif(FacebookService::is_logged()) {
				/** @var FacebookService $authService */
				$authService = $this->get_service('facebook');
			}
			if(isset($authService)) {
				if(is_array($role)) {
					foreach ($role as $_role) {
						if ($authService->get_role() === $_role) {
							return true;
						}
					}
				}
				else {
					if ($authService->get_role() === $role) {
						return true;
					}
				}
			}
			if($auth_activated) {
				$this->http_error = $this->NOT_AUTHENTICATED_USER('You must be authenticated'.(!is_null($role) ? ' on '.(is_array($role) ? implode(' or ', $role) : $role).' role' : ''));
				return false;
			}
			return true;
		}
		$this->http_error = $this->PAGE_NOT_FOUND('Controller not found');
		return false;
	}

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
		$this->external_conf = External_confs::create();
		$this->sub_domains = Subdomains::create()->set_domain($_SERVER['HTTP_HOST']);

		if(in_array($action, $class_methods)) {
			$this->method = $action;
			$this->params = $params;

			$this->run_if_authenticated();
		}
		else $this->http_error = $this->PAGE_NOT_FOUND(get_class($this).'::'.$action.'() method not found !');

		$this->after_construct();
	}

	protected function after_construct() {}

	/**
	 * @return Response
	 */
	abstract public function index();

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
	 * @param string|null $value
	 * @return array|string|null
	 */
	protected function get($key = null, $value = null) {
		return $this->http_service->get($key, $value);
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

	private function api_sub_domain() {
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
		$base = 'http'.($this->is_https() ? 's' : '').'://';
		$api_sub_domain = $this->api_sub_domain();
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
			$base .= $domain.($type === self::API ? '' : '/api');
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
	 * @param        $message
	 * @param string $format
	 * @return Response|void
	 * @throws Exception
	 */
	protected function BAD_REQUEST($message, $format = Response::JSON) {
		header('HTTP/1.0 400'.$message);
		return $this->get_response(
			[
				'code' => 400,
				'message' => $message,
			]
		);
	}

	/**
	 * @param        $message
	 * @param string $format
	 * @return Response
	 * @throws Exception
	 */
	protected function FORBIDDEN($message, $format = Response::JSON) {
		header('HTTP/1.0 403'.$message);
		return $this->get_response(
			[
				'code' => 403,
				'message' => $message,
			]
		);
	}

	/**
	 * @param        $message
	 * @param string $format
	 * @return Response
	 * @throws Exception
	 */
	protected function NOT_AUTHENTICATED_USER($message, $format = Response::JSON) {
		header('HTTP/1.0 401'.$message);
		return $this->get_response(
			[
				'code' => 401,
				'message' => $message,
			]
		);
	}

	/**
	 * @param        $message
	 * @param string $format
	 * @return Response
	 * @throws Exception
	 */
	protected function PAGE_NOT_FOUND($message, $format = Response::JSON) {
		header('HTTP/1.0 404'.$message);
		return $this->get_response(
			[
				'code' => 404,
				'message' => $message,
			]
		);
	}

	/**
	 * @param        $message
	 * @param string $format
	 * @return Response
	 * @throws Exception
	 */
	protected function INTERNAL_ERROR($message, $format = Response::JSON) {
		header('HTTP/1.0 500'.$message);
		return $this->get_response(
			[
				'code' => 500,
				'message' => $message,
			]
		);
	}

	/**
	 * @param        $message
	 * @param string $format
	 * @return Response
	 * @throws Exception
	 */
	protected function SERVER_ERROR($message, $format = Response::JSON) {
		header('HTTP/1.0 503'.$message);
		return $this->get_response(
			[
				'code' => 503,
				'message' => $message,
			]
		);
	}

	/**
	 * @param        $message
	 * @param string $format
	 * @return Response
	 * @throws Exception
	 */
	protected function SERVER_NOT_RESPOND($message, $format = Response::JSON) {
		header('HTTP/1.0 504'.$message);
		return $this->get_response(
			[
				'code' => 504,
				'message' => $message,
			]
		);
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