<?php


namespace core;


use MiladRahimi\Jwt\Authentication\Subdomains;

class UrlsService extends Service implements IUrlsService {

	protected $base;
	/** @var Subdomains $sub_domain */
	protected $sub_domain;
	protected $uri		  = null;
	protected $controller = null;
	protected $action 	  = null;
	protected $protocol;
	protected $args;

	public function initialize_after_injection() {
		$this->sub_domain = Subdomains::create();
		$this->sub_domain->set_domain($_SERVER['HTTP_HOST']);
		$this->set_protocol();
	}

	public function set_protocol($force_https = false) {
		if($force_https) {
			$this->protocol = 'https://';
			return $this;
		}
		$this->protocol = 'http'.($_SERVER['HTTPS'] === 'on' ? 's' : '').'://';
		return $this;
	}

	/**
	 * @return $this
	 */
	public function set_api_subdomain() : IUrlsService {
		$this->base = 'api.'.$this->sub_domain->get_main_domain().'/';
		return $this;
	}

	/**
	 * @param string $type
	 * @return $this
	 */
	public function set_www_subdomain(string $type = Controller::API) : IUrlsService {
		$this->base = 'www.'.$this->sub_domain->get_main_domain();
		if($type === Controller::API) {
			$this->base .= '/api/';
		}
		return $this;
	}

	/**
	 * @param string $uri
	 * @return $this
	 */
	public function set_uri(string $uri) : IUrlsService {
		$this->uri = $uri;
		return $this;
	}

	/**
	 * @param string $arg
	 * @param int|string $value
	 * @return $this
	 */
	public function set_arg(string $arg, $value) : IUrlsService {
		$this->args[$arg] = $value;
		return $this;
	}

	/**
	 * @param string $controller
	 * @return $this
	 */
	public function set_controller(string $controller) : IUrlsService {
		$this->controller = $controller;
		return $this;
	}

	/**
	 * @param string $action
	 * @return $this
	 */
	public function set_action(string $action) : IUrlsService {
		$this->action = $action;
		return $this;
	}

	/**
	 * @param string $referer
	 * @return $this
	 */
	public function set_referer(string $referer) : IUrlsService {
		return $this->set_arg('referer', htmlspecialchars($referer));
	}

	/**
	 * @return string
	 */
	public function get() : string {
		$url = $this->protocol;
		$url .= $this->base;
		if ($this->uri) {
			$url .= $this->uri;
		}
		elseif ($this->controller) {
			$url .= $this->action ? $this->controller.'/'.$this->action : $this->controller;
		}

		if (!empty($this->args)) {
			$url .= '?';
			$count = 0;
			foreach ($this->args as $arg => $val) {
				if($count > 0) {
					$url .= '&';
				}
				$url .= $arg.'='.$val;
				$count++;
			}
		}
		return $url;
	}

	/**
	 * @param $url
	 * @return $this|bool
	 */
	public function init_url($url) {
		if(preg_match('`(http(s)?):\/\/([a-zA-Z0-9\_\.\-]+\/)([a-zA-Z0-9\_]+)[\/]?([a-zA-Z0-9\_]+)?\/?\??([^\ ]+)?`', $url, $matches)) {
			if(isset($matches[2])) {
				$this->set_protocol(true);
			}
			$this->base = $matches[3];
			$this->set_controller($matches[4]);
			if(isset($matches[5])) {
				$this->set_action($matches[5]);
			}
			if(isset($matches[6])) {
				$args = explode('&', $matches[6]);
				foreach ($args as $arg) {
					$arg = explode('=', $arg);
					$this->set_arg($arg[0], $arg[1]);
				}
			}
			return $this;
		}
		return false;
	}
}