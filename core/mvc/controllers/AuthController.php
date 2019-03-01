<?php


namespace core;


use Exception;
use Facebook\Authentication\AccessToken;
use Facebook\Exceptions\FacebookSDKException;
use MiladRahimi\Jwt\Enums\PublicClaimNames;

class AuthController extends Controller {

	private $fb_permissions = [
		'email', 'id',
		'first_name', 'last_name',
	];

	protected $local_permissions = [
		'email', 'id',
		'first_name', 'last_name',
	];

	protected $subject = 'authentication';
	protected $audience = 'experience_framework';
	protected $issuer = 'user_id';
	protected $user_id = 0;
	protected $email;
	protected $passwd;

	// LOGIN WITH PIZZYGO IN CORE

	/**
	 * @return Response
	 * @throws Exception
	 */
	public function index(AuthenticationService $authenticationService = null, CookieService $cookieService = null, SessionService $sessionService = null, UrlsService $urlsService = null): Response {
		if(!AuthenticationService::is_logged()) {
			$urlsService->set_api_subdomain()
						->set_controller($this->get_alias())->set_action('callback');

			$loginUrl = $authenticationService->get_login_url($urlsService->get());

			$urlsService->init_url($loginUrl);

			if($this->email) {
				$urlsService->set_arg('email', $this->email);
			}
			if($this->passwd) {
				$urlsService->set_arg('password', sha1(sha1($this->passwd)));
			}

			return $this->OK(
				[
					'logged' => false,
					'login_url' => $urlsService->get(),
				]
			);
		}

		$access_token = AuthenticationService::get_access_token_from_storage();
		$me = $authenticationService->get_user($access_token, $this->local_permissions);

		$referer_url = $urlsService->set_api_subdomain()->set_controller($this->get_alias())->get();

		$urlsService->set_api_subdomain()->set_controller($this->get_alias())->set_action('logout')->set_referer($referer_url);

		return $this->OK(
			[
				'logged' => true,
				'user' => $me,
				'logout_url' => $urlsService->get()
			]
		);
	}

	/**
	 * @throws Exception
	 */
	public function auth(AuthenticationService $authenticationService, UrlsService $urlsService) {
		$authenticationService
			->add_claim(PublicClaimNames::SUBJECT, $this->subject)
			->add_claim(PublicClaimNames::ID, $this->user_id)
			->add_claim(PublicClaimNames::ISSUER, $this->issuer)
			->add_claim(PublicClaimNames::AUDIENCE, $this->audience);

		$urlsService->init_url($this->get_referer())->set_arg('code', $authenticationService->get_token())->set_arg('user_id', base64_encode(decbin($this->user_id)));

		return $this->PERMANENTLY_REDIRECT($urlsService->get());
	}

	/**
	 * @return ErrorController|JsonResponse|Response
	 * @throws Exception
	 */
	public function callback(AuthenticationService $authenticationService, UrlsService $urlsService) {
		$this->user_id = bindec(base64_decode($this->get('user_id')));
		$authenticationService
			->add_claim(PublicClaimNames::SUBJECT, $this->subject)
			->add_claim(PublicClaimNames::ID, $this->user_id)
			->add_claim(PublicClaimNames::ISSUER, $this->issuer)
			->add_claim(PublicClaimNames::AUDIENCE, $this->audience);


		$access_token = AuthenticationService::is_logged()
			? AuthenticationService::get_access_token_from_storage() : $authenticationService->get_token();

		if (!$access_token) {
			return $authenticationService->get_error() ? $this->NOT_AUTHENTICATED_USER(
				"Error: ".$authenticationService->get_error(AuthenticationService::ERROR)."\n".
				"Error Code: ".$authenticationService->get_error(AuthenticationService::ERROR_CODE)."\n".
				"Error Reason: ".$authenticationService->get_error(AuthenticationService::ERROR_REASON)."\n".
				"Error Description: ".$authenticationService->get_error(AuthenticationService::ERROR_DESCRIPTION)."\n"
			) : $this->BAD_REQUEST('Bad Request');
		}

		AuthenticationService::set_cookie_access_token((string)$access_token, time() + 2592000, '.'.$this->sub_domains->get_main_domain());
		AuthenticationService::set_session_access_token();

		$home_url = $urlsService->set_api_subdomain()->set_controller($this->get_alias())->get();

		$me = $authenticationService->get_user($access_token, $this->local_permissions);

		$referer_url = $urlsService->set_api_subdomain()->set_controller($this->get_alias())->get();
		$urlsService->set_api_subdomain()->set_controller($this->get_alias())->set_action('logout')->set_referer($referer_url);

		return $this->OK(
			[
				'logged' => true,
				'user' => $me,
				'logout_url' => $urlsService->get(),
				'home_url' => $home_url,
			]
		);
	}

	/**
	 * @throws Exception
	 */
	public function logout(UrlsService $urlsService) {
		$this->get_service('authentication');
		AuthenticationService::unset_access_token_from_storage('.'.$this->sub_domains->get_main_domain());

		if(!$this->get_referer()) {
			$this->get('referer', $urlsService->set_api_subdomain()->set_controller($this->get_alias())->get());
		}
		$this->PERMANENTLY_REDIRECT($this->get_referer(), 'Redirect to '.$this->get_referer());
	}

	// LOGIN WITH FACEBOOK IN CORE

	/**
	 * @return ErrorController|JsonResponse|Response
	 * @throws Exception
	 */
	public function fb(FacebookService $facebookService, UrlsService $urlsService): JsonResponse {
		if(!FacebookService::is_logged()) {
			$urlsService->set_api_subdomain()
						->set_controller($this->get_alias())->set_action('fb_callback');

			$loginUrl	 = $facebookService->get_login_url($urlsService->get());

			return $this->OK(
				[
					'logged' => false,
					'login_url' => $loginUrl,
				]
			);
		}

		try {
			$fb_access_token = FacebookService::get_access_token_from_storage();
			$me = $facebookService->get_user($fb_access_token, $this->fb_permissions);
		} catch(FacebookSDKException $e) {
			if(strstr($e->getMessage(), 'Resolving timed out after')) {
				return $this->fb_logout($urlsService);
			}
			return $this->SERVER_ERROR('Facebook SDK returned an error: '.$e->getMessage());
		}

		$referer_url = $urlsService->set_api_subdomain()->set_controller($this->get_alias())->set_action('fb')->get();

		$urlsService->set_api_subdomain()->set_controller($this->get_alias())->set_action('fb_logout')->set_referer($referer_url);

		return $this->OK(
			[
				'logged' => true,
				'user' => $me,
				'logout_url' => $urlsService->get()
			]
		);
	}

	/**
	 * @return ErrorController|JsonResponse|Response
	 * @throws FacebookSDKException
	 * @throws Exception
	 */
	public function fb_callback(FacebookService $facebookService, SessionService $sessionService, UrlsService $urlsService) {
		if(!$this->get('state')) {
			throw new Exception('Cross-site request forgery validation failed. Required param "state" missing from persistent data.');
		}
		$sessionService->set(FacebookService::$FB_SESSION_PREFIX.'state', $this->get('state'));

		$helper = $facebookService->get_helper(FacebookService::REDIRECT_LOGIN);

		if(!FacebookService::is_logged()) {
			try {
				$accessToken = $helper->getAccessToken();
			} catch (FacebookSDKException $e) {
				// When validation fails or other local issues
				return $this->SERVER_ERROR('Facebook SDK returned an error: '.$e->getMessage());
			}
		}
		else {
			$accessToken = new AccessToken(FacebookService::get_access_token_from_storage());
		}

		if (!$accessToken) {
			return $helper->getError() ? $this->NOT_AUTHENTICATED_USER(
				"Error: ".$helper->getError()."\n".
				"Error Code: ".$helper->getErrorCode()."\n".
				"Error Reason: ".$helper->getErrorReason()."\n".
				"Error Description: ".$helper->getErrorDescription()."\n"
			) : $this->BAD_REQUEST('Bad Request');
		}

		// The OAuth 2.0 client handler helps us manage access tokens
		$oAuth2Client = $facebookService->get_oauth_client();

		// Validation (these will throw FacebookSDKException's when they fail)
		FacebookService::validate($accessToken, $facebookService->get_application_id());

		if (!$accessToken->isLongLived()) {
			// Exchanges a short-lived access token for a long-lived one
			try {
				$accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
			} catch (FacebookSDKException $e) {
				return $this->BAD_REQUEST("Error getting long-lived access token: ".$e->getMessage());
			}
		}

		FacebookService::set_cookie_fb_access_token((string)$accessToken, time() + 2592000, '.'.$this->sub_domains->get_main_domain());
		FacebookService::set_session_fb_access_token();

		$home_url = $urlsService->set_api_subdomain()->set_controller($this->get_alias())->set_action('fb')->get();

		$me = $facebookService->get_user($accessToken, $this->fb_permissions);

		$referer_url = $urlsService->set_api_subdomain()->set_controller($this->get_alias())->set_action('fb')->get();
		$urlsService->set_api_subdomain()->set_controller($this->get_alias())->set_action('fb_logout')->set_referer($referer_url);

		return $this->OK(
			[
				'logged' => true,
				'user' => $me,
				'logout_url' => $urlsService->get(),
				'home_url' => $home_url,
			]
		);
	}

	/**
	 * @throws Exception
	 */
	public function fb_logout(UrlsService $urlsService) {
		$this->get_service('facebook');
		FacebookService::unset_access_token_from_storage('.'.$this->sub_domains->get_main_domain());

		if(!$this->get_referer()) {
			$this->get('referer', $urlsService->set_api_subdomain()->set_controller('home')->set_action('login_fb')->get());
		}
		$this->PERMANENTLY_REDIRECT($this->get_referer(), 'Redirect to '.$this->get_referer());
	}

	/**
	 * @throws Exception
	 */
	public function test_if_is_auth(AuthenticationService $authenticationService, FacebookService $facebookService) {
		if(!AuthenticationService::is_logged() && !FacebookService::is_logged()) {
			return $this->NOT_AUTHENTICATED_USER('You are not logged');
		}
		$authService = FacebookService::is_logged() ? $facebookService : $authenticationService;
		$accessToken = FacebookService::is_logged() ? FacebookService::get_access_token_from_storage() : AuthenticationService::get_access_token_from_storage();
		$me = $authService->get_user((string)$accessToken, $this->fb_permissions);
		return $this->OK(
			[
				'logged' => true,
				'user' => $me,
			]
		);
	}
}