<?php


namespace core;

require_once __DIR__.'/IAuthService.php';


interface IAuthenticationService extends IAuthService {
	public function authenticated();
	public function authenticate($referer_success = null, $referer_error = null);
	public function disconnect($referer = null);
	public function get_token();
	public function add_claim($claim, $value);
	public function redirect($href);

	public static function set_storage_key($fb_storage_key);

	public static function set_session_access_token();

	public static function set_cookie_access_token($access_token, $time, $domain);

	public static function get_cookie_access_token();
}