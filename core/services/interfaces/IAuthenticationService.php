<?php


namespace core;


interface IAuthenticationService extends IService {
	public function authenticated();
	public function authenticate($referer_success = null, $referer_error = null);
	public function disconnect($referer = null);
	public function get_token();
	public function add_claim($claim, $value);
	public function redirect($href);
}