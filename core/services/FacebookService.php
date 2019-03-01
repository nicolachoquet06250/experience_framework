<?php


namespace core;


use custom\UserDao;
use custom\UserEntity;
use Exception;
use Facebook\Authentication\AccessToken;
use Facebook\Authentication\OAuth2Client;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Facebook\FacebookResponse;
use Facebook\GraphNodes\GraphUser;
use Facebook\Helpers\FacebookCanvasHelper;
use Facebook\Helpers\FacebookJavaScriptHelper;
use Facebook\Helpers\FacebookPageTabHelper;
use Facebook\Helpers\FacebookRedirectLoginHelper;

class FacebookService extends Service implements IFacebookService {
	/** @var Facebook $fb */
	protected $fb;
	protected $permissions = [];
	protected $app_id;
	protected $secret;

	const REDIRECT_LOGIN = 'RedirectLogin';
	const JAVASCRIPT	 = 'JavaScript';
	const PAGE_TAB		 = 'PageTab';
	const CANVAS		 = 'Canvas';

	const TOKEN_TYPE = [
		'expiration'		=> 'getExpiresAt',
		'expired'			=> 'isExpired',
		'app_secret_proof'	=> 'getAppSecretProof',
		'value'				=> 'getValue',
		'is_app_token'		=> 'isAppAccessToken',
		'is_long_lived'		=> 'isLongLived',
	];

	const ERROR = 'error';
	const ERROR_CODE = 'error_code';
	const ERROR_REASON = 'error_reason';
	const ERROR_DESCRIPTION = 'error_description';

	public static $FB_SESSION_PREFIX = 'FBRLH_';

	protected static $fb_storage_key = 'fb_access_token';

	public static function set_fb_storage_key($fb_storage_key) {
		self::$fb_storage_key = $fb_storage_key;
	}

	/**
	 * @param $access_token
	 * @throws Exception
	 */
	public static function set_session_fb_access_token() {
		/** @var SessionService $sessionService */
		$sessionService = self::get_service('session');
		$sessionService->set(self::$fb_storage_key, self::get_cookie_fb_access_token());
	}

	/**
	 * @param $domain
	 * @throws Exception
	 */
	public static function unset_access_token_from_storage($domain) {
		/** @var CookieService $cookieService */
		$cookieService = self::get_service('cookie');
		/** @var SessionService $sessionService */
		$sessionService = self::get_service('session');
		$cookieService->remove('fb_access_token', $domain);
		$sessionService->remove('fb_access_token');
	}

	/**
	 * @param $access_token
	 * @param $time
	 * @param $domain
	 * @throws Exception
	 */
	public static function set_cookie_fb_access_token($access_token, $time, $domain) {
		/** @var CookieService $cookieService */
		$cookieService = self::get_service('cookie');
		$cookieService->set(self::$fb_storage_key, $time, $access_token, $domain);
	}

	/**
	 * @return string|null
	 * @throws Exception
	 */
	public static function get_cookie_fb_access_token() {
		/** @var CookieService $cookieService */
		$cookieService = self::get_service('cookie');
		return $cookieService->get(self::$fb_storage_key);
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
		return $sessionService->get('fb_access_token') ? $sessionService->get('fb_access_token') : $cookieService->get('fb_access_token');
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

		return $sessionService->get(self::$fb_storage_key) || $cookieService->get(self::$fb_storage_key);
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	public static function is_logged() {
		return self::access_token_exists_from_storage();
	}

	/**
	 * @param $accessToken
	 * @param $app_id
	 * @throws FacebookSDKException
	 * @throws Exception
	 */
	public static function validate($accessToken, $app_id) {
		/** @var FacebookService $fb */
		$fb = self::get_service('facebook');
		$tokenMetaData = $fb->get_oauth_client()->debugToken($accessToken);
		$tokenMetaData->validateAppId($app_id);
		$tokenMetaData->validateExpiration();
	}

	/**
	 * @throws FacebookSDKException
	 * @throws Exception
	 */
	public function initialize_after_injection() {
		$external_conf = External_confs::create();

		if(!is_file($external_conf->get_external_conf_dir().'/fb/app_key.txt')
		   || !is_file($external_conf->get_external_conf_dir().'/fb/secret.txt')) {
			throw new Exception('files app_key.txt and secret.txt not found');
		}

		$this->app_id = file_get_contents($external_conf->get_external_conf_dir().'/fb/app_key.txt');
		$this->secret = file_get_contents($external_conf->get_external_conf_dir().'/fb/secret.txt');

		$this->fb =  new Facebook(
			[
				'app_id' => $this->app_id,
				'app_secret' => $this->secret,
				'default_graph_version' => 'v2.10',
			]
		);

		$this->add_permission('email');
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
	 */
	public function get_login_url($url) {
		return $this->fb->getRedirectLoginHelper()->getLoginUrl($url, $this->permissions);
	}

	/**
	 * @param $access_token
	 * @param array $fields
	 * @return FacebookResponse
	 * @throws FacebookSDKException
	 */
	public function get_response_for_me($access_token, $fields = []) {
		return $this->fb->get('/me'.(empty($fields) ? '' : '?fields=').implode(',', $fields), $access_token);
	}

	/**
	 * @param       $access_token
	 * @param array $fields
	 * @return array|bool
	 * @throws FacebookSDKException
	 * @throws Exception
	 */
	public function get_user($access_token, $fields = []) {
		$user = $this->get_response_for_me($access_token, $fields)->getGraphUser();
		$user = (array)$user;
		$user = array_values($user)[0];
		/** @var UserDao $userDao */
		$userDao = $this->get_dao('user');
		$_user = $userDao->getBy('email', $user['email']);
		if($_user) {
			/** @var UserEntity $_user */
			$_user = $_user[0];
			if(!$_user->get('fb_id')) {
				$_user->set('fb_id', $user['id']);
				$_user->save();
			}
		}
		return $_user;
	}

	/**
	 * @param $helper_type
	 * @return FacebookRedirectLoginHelper|FacebookJavaScriptHelper|FacebookPageTabHelper|FacebookCanvasHelper
	 */
	public function get_helper($helper_type) {
		$method = 'get'.$helper_type.'Helper';
		return $this->fb->$method();
	}

	/**
	 * @return bool|AccessToken|int|string
	 * @throws FacebookSDKException
	 */
	public function access_token_exists() {
		$token = $this->get_access_token();
		return isset($token);
	}

	/**
	 * @param string|null $token_type
	 * @return int|string|bool|AccessToken
	 * @throws FacebookSDKException
	 */
	public function get_access_token(string $token_type = null) {
		$method = $token_type;
		$access_token = $this->get_helper(self::REDIRECT_LOGIN)->getAccessToken();
		if(is_null($method)) {
			return $access_token;
		}
		return $this->get_helper(self::REDIRECT_LOGIN)->getAccessToken()->$method();
	}

	/**
	 * @return AccessToken
	 * @throws FacebookSDKException
	 */
	public function get_long_lived_access_token() {
		return $this->get_oauth_client()->getLongLivedAccessToken($this->get_access_token());
	}

	/**
	 * @return OAuth2Client
	 */
	public function get_oauth_client() {
		return $this->fb->getOAuth2Client();
	}

	/**
	 * @param string|null $error_type
	 * @return array|mixed
	 */
	public function get_error(string $error_type = null) {
		$error = [];
		$helper = $this->get_helper(self::REDIRECT_LOGIN);
		if ($helper->getError()) {
			$error['error'] = $helper->getError();
			$error['error_code'] = $helper->getErrorCode();
			$error['error_reason'] = $helper->getErrorReason();
			$error['error_description'] = $helper->getErrorDescription();
		} else {
			$error['error'] = $helper->getError();
			$error['error_description'] = 'Bad Request';
		}
		return is_null($error_type) ? $error : $error[$error_type];
	}

	/**
	 * @return string
	 */
	public function get_application_id() {
		return $this->app_id;
	}

	/**
	 * @return string
	 */
	public function get_secret() {
		return $this->secret;
	}

	public function get_role() {
		return 'user';
	}

	public function has_access_to($access_id) {
		return true;
	}
}