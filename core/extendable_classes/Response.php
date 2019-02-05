<?php

class Response extends Base implements IResponse {
	protected $element;
	protected $header_type = self::JSON;
	protected $parsed_element;

	/**
	 * @param $element
	 * @param $type
	 * @return Response
	 * @throws Exception
	 */
	public static function create($element, $type = self::JSON) {
		$external_conf = new External_confs(__DIR__.'/../../external_confs/custom.json');
		$response_class = ucfirst(explode('/', $type)[1]).'Response';
		if(is_file(realpath($external_conf->get_responses_dir().'/'.$response_class.'.php'))) {
			require_once realpath($external_conf->get_responses_dir().'/'.$response_class.'.php');
			/** @var Response $response */
			$response = new $response_class($element);
			return $response;
		}
		elseif(is_file(realpath($external_conf->get_responses_dir(false).'/'.$response_class.'.php'))) {
			require_once realpath($external_conf->get_responses_dir(false).'/'.$response_class.'.php');
			/** @var Response $response */
			$response = new $response_class($element);
			return $response;
		}
		throw new Exception('Nous n\'avons pu créer de réponse !!');
	}

	public function __construct($element) {
		$this->element($element);
		$this->header();
	}

	protected function element($element) {
		$this->element = $element;
	}

	protected function header() {
		header('Content-Type: '.$this->header_type.';charset=UTF-8');
	}

	/**
	 * @throws Exception
	 */
	protected function parse_element() {
		/** @var JsonService $json_service */
		$json_service = $this->get_service('json');
		$element = null;
		if(is_array($this->element)) {
			$element = [];
			foreach ($this->element as $key => $elem) {
				if(is_object($elem)) {
					/** @var Base $elem */
					$element[$key] = $elem->toArrayForJson();
				}
				if (is_string($elem) || is_numeric($elem) || is_array($elem) || is_bool($elem)) {
					$element[$key] = $elem;
				}
			}
		}
		elseif (is_string($this->element)) {
			$element = [
				'message' => $this->element
			];
		}
		elseif (is_numeric($this->element)) {
			$element = [
				'code' => $this->element
			];
		}
		elseif (is_object($this->element)) {
			/** @var Base $_element */
			$_element = $this->element;
			$element = $_element->toArrayForJson();
		}
		$this->parsed_element = $json_service->encode($element);
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */
	public function display() {
		$this->parse_element();
		return $this->parsed_element;
	}
}