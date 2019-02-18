<?php


namespace core;



use custom\UserEntity;
use MiladRahimi\Jwt\Authentication\Authentication;
use MiladRahimi\Jwt\Authentication\Subdomains;

class AuthenticationService extends Service implements IAuthenticationService {
	/** @var Authentication $auth */
	private $auth;
	private $sub_domains;

	public function initialize_after_injection() {
		$this->auth = new Authentication();
		$this->sub_domains = Subdomains::create();
		$this->sub_domains->set_domain($_SERVER['HTTP_HOST']);
	}

	/**
	 * @return bool
	 */
	public function authenticated() {
		return $this->auth->authenticated();
	}

	/**
	 * @param null $referer_success
	 * @param null $referer_error
	 * @return bool
	 * @throws \Exception
	 */
	public function authenticate($referer_success = null, $referer_error = null) {
		$authenticated = $this->auth->authenticate();
		if($authenticated && !is_null($referer_success)) {
			if(is_object($referer_success) && get_class($referer_success)) {
				/** @var \Closure $referer_success */
				$referer_success = $referer_success();
			}
			$this->redirect($referer_success);
		}
		elseif (!$authenticated && !is_null($referer_error)) {
			if(is_object($referer_error) && get_class($referer_error)) {
				/** @var \Closure $referer_error */
				$referer_error = $referer_error();
			}
			$this->redirect($referer_error);
		}
		return $authenticated;
	}

	/**
	 * @param string|callable|null $referer
	 * @return bool|Response
	 * @throws \Exception
	 */
	public function disconnect($referer = null) {
		$disconnected = $this->auth->disconnect();
		if(!is_null($referer)) {
			if(is_object($referer) && get_class($referer)) {
				/** @var \Closure $referer */
				$referer = $referer();
			}
			return $this->redirect($referer);
		}
		return $disconnected;
	}

	/**
	 * @return mixed|string|null
	 * @throws \Exception
	 */
	public function get_token() {
		if(!$this->authenticated()) {
			return null;
		}
		return $this->auth->get_token();
	}

	/**
	 * @param $claim
	 * @param $value
	 * @return AuthenticationService
	 * @throws \Exception
	 */
	public function add_claim($claim, $value) {
		$this->auth->addClaim($claim, $value);
		return $this;
	}

	/**
	 * @param $href
	 * @return Response
	 * @throws \Exception
	 */
	public function redirect($href) {
		header('Location: '.$href);
	}

	/**
	 * @return array|array[]
	 * @throws \Exception
	 */
	public function get_connected_user() {
		return $this->auth->get_user();
	}

	/**
	 * @return int
	 * @throws \Exception
	 */
	public function get_connected_user_id() {
		return (int)$this->auth->get_user_id();
	}

	/**
	 * @param $user
	 * @throws \Exception
	 */
	public function set_connected_user($user) {
		$this->auth->set_user($user);
	}
}