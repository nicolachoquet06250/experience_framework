<?php

abstract class Controller extends Base implements IController {
	private $method;
	protected $params;
	/** @var HttpService $http_service */
	protected $http_service;
	/** @var ErrorController $http_error */
	protected $http_error;

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
		else $this->http_error = $this->get_error_controller(404)
									 ->message('La méthode '.get_class($this).'::'.$action.'() n\'existe pas !');
	}

	/**
	 * @return Response
	 */
	abstract protected function index();

	/**
	 * @param null $arg
	 * @return string
	 * @throws ReflectionException
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
		$error_action = '_'.$code;
		return new ErrorController($error_action, []);
	}
}