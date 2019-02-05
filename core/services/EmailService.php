<?php

class EmailService extends Service implements IEmailService {
	private $credentials;
	/** @var \PHPMailer\PHPMailer\PHPMailer $mailer */
	private $mailer;

	private $smtp_host = 'smtp.gmail.com';
	private $smtp_post = 587;
	private $debug = 0;
	private $auth = true;
	private $secure = 'tls';

	/**
	 * @throws Exception
	 */
	public function initialize_after_injection() {
		/** @var CredentialsDao $credentials_dao */
		$credentials_dao = $this->get_dao('credentials');
		/** @var CredentialsEntity[]|bool $email_credential */
		$email_credential = $credentials_dao->getBy('type', 'mailing');
		$email_credential = $email_credential ? $email_credential[0] : null;
		if(is_null($email_credential)) {
			throw new Exception('Vous devez enregistrer des credentials pour pouvoir vous connected');
		}
		$this->credentials = [
			'email'    => $email_credential->get('login'),
			'password' => $email_credential->get('password'),
		];
		$this->mailer = new PHPMailer\PHPMailer\PHPMailer(true);
		$this->mailer->IsSMTP();
		$this->mailer->SMTPDebug  = $this->debug;  // debogage: 1 = Erreurs et messages, 2 = messages seulement
		$this->mailer->SMTPAuth   = $this->auth;  // Authentification SMTP active
		$this->mailer->SMTPSecure = $this->secure; // Gmail REQUIERT Le transfert securise

		$this->mailer->Host = $this->smtp_host;
		$this->mailer->Port = $this->smtp_post;

		$this->mailer->Username = $this->credentials['email'];
		$this->mailer->Password = $this->credentials['password'];

	}

	public function html() {
		$this->mailer->isHTML();
		return $this;
	}

	/**
	 * @param $name
	 * @return $this
	 * @throws \PHPMailer\PHPMailer\Exception
	 */
	public function set_from_name($name) {
		$this->mailer->SetFrom($this->mailer->Username, $name);
		return $this;
	}

	/**
	 * @param $file_path
	 * @param string $name
	 * @return $this
	 * @throws \PHPMailer\PHPMailer\Exception
	 */
	public function join_file($file_path, $name = '') {
		$this->mailer->addAttachment($file_path, $name);
		return $this;
	}

	/**
	 * @param string $object
	 * @return $this
	 */
	public function object($object) {
		$this->mailer->Subject = $object;
		return $this;
	}

	/**
	 * @param $message
	 * @return $this
	 */
	public function message($message) {
		$this->mailer->Body = $message;
		return $this;
	}

	/**
	 * @param $email
	 * @param string $name
	 * @return $this
	 */
	public function to($email, $name = '') {
		$this->mailer->addAddress($email, $name);
		return $this;
	}

	/**
	 * @param $email
	 * @param string $name
	 * @return $this
	 */
	public function replayTo($email, $name = '') {
		$this->mailer->addReplyTo($email, $name);
		return $this;
	}

	/**
	 * @param $email
	 * @param string $name
	 * @return $this
	 */
	public function cc($email, $name = '') {
		$this->mailer->addCC($email, $name);
		return $this;
	}

	/**
	 * @param $email
	 * @param string $name
	 * @return $this
	 */
	public function addBcc($email, $name = '') {
		$this->mailer->addBCC($email, $name);
		return $this;
	}

	/**
	 * @param $charset
	 * @return $this
	 */
	public function charset($charset) {
		$this->mailer->CharSet = $charset;
		return $this;
	}

	/**
	 * @return bool
	 * @throws \PHPMailer\PHPMailer\Exception
	 */
	public function send() {
		return $this->mailer->send();
	}

	/**
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 */
	public function get_mailer() {
		return $this->mailer;
	}
}