<?php


namespace core;

require_once __DIR__.'/IAuthService.php';

interface IFacebookService extends IAuthService {
	public static function set_fb_storage_key($fb_storage_key);

	public static function set_session_fb_access_token();

	public static function set_cookie_fb_access_token($access_token, $time, $domain);

	public static function get_cookie_fb_access_token();

	public function get_helper($helper_type);
}