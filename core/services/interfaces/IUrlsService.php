<?php


namespace core;


interface IUrlsService extends IService {
	public function set_api_subdomain() : IUrlsService;

	/**
	 * @param string $type
	 * @return $this
	 */
	public function set_www_subdomain(string $type = Controller::API) : IUrlsService;

	/**
	 * @param string $uri
	 * @return $this
	 */
	public function set_uri(string $uri) : IUrlsService;

	/**
	 * @param string $arg
	 * @param int|string $value
	 * @return $this
	 */
	public function set_arg(string $arg, $value) : IUrlsService;

	/**
	 * @param string $controller
	 * @return $this
	 */
	public function set_controller(string $controller) : IUrlsService;

	/**
	 * @param string $action
	 * @return $this
	 */
	public function set_action(string $action) : IUrlsService;

	/**
	 * @param string $referer
	 * @return $this
	 */
	public function set_referer(string $referer) : IUrlsService;

	/**
	 * @return string
	 */
	public function get() : string;

	public function init_url($url);
}