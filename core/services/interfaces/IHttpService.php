<?php
namespace core;

interface IHttpService extends IService {
	public function get($key = null, $value = null);

	public function post($key);

	public function files($key);

	public function response_header($key, $value = null);

	public function session($key = null, $value = null);

	public function server($key = null);
}