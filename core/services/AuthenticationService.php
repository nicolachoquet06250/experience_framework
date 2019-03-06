<?php


namespace core;



use Closure;
use custom\UserDao;
use Exception;
use MiladRahimi\Jwt\Authentication\Authentication;
use MiladRahimi\Jwt\Authentication\Subdomains;
use MiladRahimi\Jwt\Claims\JWTClaims;
use MiladRahimi\Jwt\Enums\PublicClaimNames;
use MiladRahimi\Jwt\JwtParser;

class AuthenticationService extends Service implements IAuthenticationService {

	protected $private_key_path = __DIR__.'/../../git_dependencies/jwt/keys/private.pem';
	protected $public_key_path = __DIR__.'/../../git_dependencies/jwt/keys/public.pem';

	/** @var Authentication $auth */
	private $auth;
	/** @var Subdomains $sub_domains */
	private $sub_domains;

	const ERROR = 'error';
	const ERROR_CODE = 'error_code';
	const ERROR_REASON = 'error_reason';
	const ERROR_DESCRIPTION = 'error_description';

	protected $error = false;
	protected $error_code = 0;
	protected $error_reason = null;
	protected $error_description = '';

	protected $permissions = [];

	public static $EXP_FRM_SESSION_PREFIX = 'EXP_FRM_';

	protected static $exp_frm_storage_key = 'access_token';

	/** @var SessionService $sessionService */
	private static $sessionService;
	/** @var CookieService $cookieService */
	private static $cookieService;

	/**
	 * @throws Exception
	 */
	public function initialize_after_injection() {
		$this->auth = new Authentication();
		$this->sub_domains = Subdomains::create()->set_domain($_SERVER['HTTP_HOST']);

		if(is_null(self::$sessionService)) {
			self::$sessionService = $this->get_service('session');
		}
		if(is_null(self::$cookieService)) {
			self::$cookieService = $this->get_service('cookie');
		}
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function authenticated() {
		return self::access_token_exists_from_storage();
	}

	/**
	 * @param null $referer_success
	 * @param null $referer_error
	 * @return bool
	 * @throws Exception
	 */
	public function authenticate($referer_success = null, $referer_error = null) {
		$authenticated = $this->auth->authenticate();
		if($authenticated && !is_null($referer_success)) {
			if(is_object($referer_success) && get_class($referer_success)) {
				/** @var Closure $referer_success */
				$referer_success = $referer_success();
			}
			$this->redirect($referer_success);
		}
		elseif (!$authenticated && !is_null($referer_error)) {
			if(is_object($referer_error) && get_class($referer_error)) {
				/** @var Closure $referer_error */
				$referer_error = $referer_error();
			}
			$this->redirect($referer_error);
		}
		return $authenticated;
	}

	/**
	 * @param string|callable|null $referer
	 * @return bool|Response
	 * @throws Exception
	 */
	public function disconnect($referer = null) {
		$disconnected = $this->auth->disconnect();
		if(!is_null($referer)) {
			if(is_object($referer) && get_class($referer)) {
				/** @var Closure $referer */
				$referer = $referer();
			}
			return $this->redirect($referer);
		}
		return $disconnected;
	}

	/**
	 * @return string|null
	 * @throws Exception
	 */
	public function get_token() {
		if($this->authenticated()) {
			return self::get_access_token_from_storage();
		}
		return $this->auth->get_token();
	}

	/**
	 * @param $claim
	 * @param $value
	 * @return AuthenticationService
	 * @throws Exception
	 */
	public function add_claim($claim, $value) {
		$this->auth->addClaim($claim, $value);
		return $this;
	}

	/**
	 * @param $href
	 * @return Response
	 * @throws Exception
	 */
	public function redirect($href) {
		header('Location: '.$href);
	}

	/**
	 * @return array|array[]
	 * @throws Exception
	 */
	public function get_connected_user() {
		return $this->auth->get_user();
	}

	/**
	 * @return int
	 * @throws Exception
	 */
	public function get_connected_user_id() {
		return (int)$this->auth->get_user_id();
	}

	/**
	 * @param $user
	 * @throws Exception
	 */
	public function set_connected_user($user) {
		$this->auth->set_user($user);
	}


	private static function get_test_user($access_token) {
		return [
			'first_name' => 'Nicolas',
			'last_name' => 'Choquet',
			'id' => 1102,
			'email' => 'nicolachoquet06250@gmail.com',
			'access_token' => $access_token,
		];
	}


	public static function set_storage_key($exp_frm_storage_key) {
		self::$exp_frm_storage_key = $exp_frm_storage_key;
	}

	/**
	 * @throws Exception
	 */
	public static function set_session_access_token() {
		self::$sessionService->set(self::$exp_frm_storage_key, self::get_cookie_access_token());
	}

	/**
	 * @param $access_token
	 * @param $time
	 * @param $domain
	 * @throws Exception
	 */
	public static function set_cookie_access_token($access_token, $time, $domain) {
		self::$cookieService->set(self::$exp_frm_storage_key, $time, $access_token, $domain);
	}

	/**
	 * @return string|null
	 * @throws Exception
	 */
	public static function get_cookie_access_token() {
		return self::$cookieService->get(self::$exp_frm_storage_key);
	}

	/**
	 * @param $permission
	 * @return $this
	 */
	public function add_permission($permission) {
		if(!in_array($permission, $this->permissions)) {
			$this->permissions[] = $permission;
		}
		return $this;
	}

	/**
	 * @param $url
	 * @return string
	 * @throws Exception
	 */
	public function get_login_url($url) {
		/** @var UrlsService $urlsService */
		$urlsService = $this->get_service('urls');
		return $urlsService->set_api_subdomain()->set_controller('auth')->set_action('auth')->set_arg('client_id', base64_encode($this->get_application_id()))->set_arg('response_type', 'code')->set_referer($url)->get();
	}

	/**
	 * @param       $access_token
	 * @param array $fields
	 * @return array
	 * @throws Exception
	 */
	public function get_response_for_me($access_token, $fields = []) {
		if(JWTClaims::create()->isEmpty()) {
			$claims = JWTClaims::create()->init_with_token($access_token);
		}
		else {
			$claims = JWTClaims::create()->get();
		}
		$user_id = $claims[PublicClaimNames::ID];
		$userDao = $this->get_dao('user');
		$me = $userDao->getById($user_id)->toArrayForJson();
		$_me = [];
		foreach ($fields as $field) {
			if(in_array($field, array_keys($me))) {
				$_me[$field] = $me[$field];
			}
		}
		return $_me;
	}

	/**
	 * @param       $access_token
	 * @param array $fields
	 * @return array
	 * @throws Exception
	 */
	public function get_user($access_token, $fields = []) {
		return $this->get_response_for_me($access_token, $fields);
	}

	public function access_token_exists() {
		$token = $this->get_access_token();
		return isset($token);
	}

	public function get_access_token(string $token_type = null) {
		if(self::$sessionService->get(self::$EXP_FRM_SESSION_PREFIX.self::$exp_frm_storage_key)) {
			return self::$sessionService->get(self::$EXP_FRM_SESSION_PREFIX.self::$exp_frm_storage_key);
		}
		elseif (self::$cookieService->get(self::$EXP_FRM_SESSION_PREFIX.self::$exp_frm_storage_key)) {
			return self::$cookieService->get(self::$EXP_FRM_SESSION_PREFIX.self::$exp_frm_storage_key);
		}
		else {
			$php_input             = getallheaders();
			$access_token_received = str_replace('Bearer ', '', $php_input['Authentication']);
			return $access_token_received;
		}
	}

	public function get_long_lived_access_token() {
		// TODO: Implement get_long_lived_access_token() method.
	}

	public function get_oauth_client() {
		// TODO: Implement get_oauth_client() method.
	}

	public function get_error(string $error_type = null) {
		switch ($error_type) {
			case self::ERROR:
				return $this->error;
			case self::ERROR_CODE:
				if($this->error) {
					return $this->error_code;
				}
				break;
			case self::ERROR_REASON:
				if($this->error) {
					return $this->error_reason;
				}
				break;
			case self::ERROR_DESCRIPTION:
				if($this->error) {
					return $this->error_description;
				}
				break;
			default:
				return $this->error ? [
					self::ERROR => $this->error,
					self::ERROR_CODE => $this->error_code,
					self::ERROR_REASON => $this->error_reason,
					self::ERROR_DESCRIPTION => $this->error_description,
				] : false;
		}
		return false;
	}

	public function get_application_id() {
		return file_get_contents($this->public_key_path);
	}

	public function get_secret() {
		return file_get_contents($this->private_key_path);
	}

	/**
	 * @return string|null
	 * @throws Exception
	 */
	public static function get_access_token_from_storage() {
		/** @var SessionService $sessionService */
		$sessionService = self::get_service('session');
		/** @var CookieService $cookieService */
		$cookieService = self::get_service('cookie');

		return $sessionService->get(self::$exp_frm_storage_key) ? $sessionService->get(self::$exp_frm_storage_key) : $cookieService->get(self::$exp_frm_storage_key);
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	public static function access_token_exists_from_storage() {
		/** @var SessionService $sessionService */
		$sessionService = self::get_service('session');
		/** @var CookieService $cookieService */
		$cookieService = self::get_service('cookie');

		return $sessionService->get(self::$exp_frm_storage_key) || $cookieService->get(self::$exp_frm_storage_key);

	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	public static function is_logged() {
		return self::access_token_exists_from_storage();
	}

	public static function unset_access_token_from_storage($domain) {
		self::$cookieService->remove(self::$exp_frm_storage_key, $domain);
		self::$sessionService->remove(self::$exp_frm_storage_key);
	}

	public function get_role() {
		return 'admin';
	}

	public function has_access_to($access_id) {
		return true;
	}
}