<?php


namespace core;


interface IAuthService extends IService {

	public function add_permission($permission);

	public function get_login_url($url);

	public function get_response_for_me($access_token, $fields = []);

	public function get_user($access_token, $fields = []);

	public function access_token_exists();

	public function get_access_token(string $token_type = null);

	public function get_long_lived_access_token();

	public function get_oauth_client();

	public function get_error(string $error_type = null);

	public function get_application_id();

	public function get_secret();

	public function get_role();

	public function has_access_to($access_id);

	public static function unset_access_token_from_storage($domain);

	public static function get_access_token_from_storage();

	public static function access_token_exists_from_storage();

	public static function is_logged();
}