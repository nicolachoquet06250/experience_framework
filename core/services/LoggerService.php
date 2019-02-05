<?php

	class LoggerService extends Service implements ILoggerService {
		const CONSOLE = 1;
		const FILE = 2;
		const MAIL = 3;

		protected $my_constants = [];

		protected $loggers = [];

		protected $logger_callbacks = [];
		protected $logs = [];

		protected $log_file;
		protected $logs_path = __DIR__.'/../logs';

		protected $email_to;
		protected $email_from_name;
		protected $email_object;

		public function initialize_after_injection() {
			$this->logger_callbacks[self::CONSOLE] = function ($message) {
				var_dump($message, 'Je suis dans la console');
			};

			$this->logger_callbacks[self::FILE] = function ($message) {
				$this->logs[self::FILE][] = $message;
				var_dump($message, 'Je suis dans le fichier '.$this->logs_path.'/'.$this->log_file.'.log');
			};

			$this->logger_callbacks[self::MAIL] = function ($message) {
				$this->logs[self::MAIL][] = $message;
				var_dump($message, 'Je suis dans un email');
			};

			$this->logger_callbacks[self::MAIL.'_send'] = function ($message) {
				var_dump($message, 'J\'envoie un email');
			};

			$this->logger_callbacks[self::FILE.'_send'] = function ($message) {
				var_dump($message, 'J\'envoie dans le fichier '.$this->logs_path.'/'.$this->log_file.'.log');
			};
		}

		public function add_constant($key, $value) {
			$this->my_constants[$key] = $value;
		}

		/**
		 * @param int|string $logger
		 * @param callable $callback
		 * @param callable|null $callback_send
		 * @throws ReflectionException
		 */
		public function logger_callback($logger, $callback, $callback_send = null) {
			$constants = array_merge((new ReflectionClass(get_class($this)))->getConstants(), $this->my_constants);
			if(is_string($logger)) {
				$logger = $this->my_constants[$logger];
			}
			if(in_array($logger, array_values($constants))) {
				$this->logger_callbacks[$logger] = $callback;
				if(!is_null($callback_send)) {
					$this->logger_callbacks[$logger.'_send'] = $callback_send;
				}
			}
		}

		public function set_email_infos($from_name, $to, $object) {
			$this->email_from_name = $from_name;
			$this->email_to = $to;
			$this->email_object = $object;
		}

		public function set_log_file($log_file) {
			if(!is_dir($this->logs_path)) {
				mkdir($this->logs_path, 0777, true);
			}
			$this->log_file = $log_file;
			touch($this->logs_path.'/'.$log_file.'.log');
		}

		/**
		 * @param int|string $type
		 * @throws ReflectionException
		 */
		public function add_logger($type) {
			$constants = array_merge((new ReflectionClass(get_class($this)))->getConstants(), $this->my_constants);
			if(is_string($type)) {
				$type = $this->my_constants[$type];
			}
			if(in_array($type, array_values($constants))) {
				$this->loggers[$type] = true;
				$this->logs[$type] = [];
			}
		}

		/**
		 * @param mixed ...$types
		 * @throws ReflectionException
		 */
		public function add_loggers(...$types) {
			foreach ($types as $type) {
				$this->add_logger($type);
			}
		}

		/**
		 * @param int|string $type
		 * @throws ReflectionException
		 */
		public function disable_logger($type) {
			$constants = array_merge((new ReflectionClass(get_class($this)))->getConstants(), $this->my_constants);
			if(is_string($type)) {
				$type = $this->my_constants[$type];
			}
			if(in_array($type, array_values($constants))) {
				$this->loggers[$type] = false;
			}
		}

		/**
		 * @param mixed ...$types
		 * @throws ReflectionException
		 */
		public function disable_loggers(...$types) {
			foreach ($types as $type) {
				$this->disable_logger($type);
			}
		}

		/**
		 * @return false|string
		 */
		protected function get_log_header() {
			return date("Y-m-d H:i:s");
		}

		/**
		 * @param int|string $logger
		 * @param string $msg
		 */
		protected function add_log($logger, $msg) {
			$this->logger_callbacks[$logger]($msg);
		}

		/**
		 * @param mixed $msg
		 * @param string|null $msg2
		 */
		public function log($msg, $msg2 = null) {
			if(is_array($msg)) {
				foreach ($msg as $item => $value) {
					if(is_string($item)) {
						$this->log($item, $value);
					}
					else {
						$this->log($value);
					}
				}
			}
			elseif (is_object($msg) && $msg instanceof Base) {
				foreach ($this->loggers as $logger => $activate) {
					if($activate) {
						$this->log($msg->toArrayForJson());
					}
				}
			}
			elseif (is_string($msg)) {
				foreach ($this->loggers as $logger => $activate) {
					if($activate) {
						if(!is_null($msg2)) {
							$msg = $msg.' => '.$msg2;
						}
						$this->add_log($logger, $msg);
					}
				}
			}
			elseif (is_numeric($msg)) {
				foreach ($this->loggers as $logger => $activate) {
					if($activate) {
						if(!is_null($msg2)) {
							$msg = $msg.' => '.$msg2;
						}
						$this->add_log($logger, $msg);
					}
				}
			}
		}

		public function send() {
			foreach ($this->logs as $logger => $logs) {
				foreach ($logs as $log) {
					if(isset($this->logger_callbacks[$logger.'_send'])) {
						$this->logger_callbacks[$logger.'_send']($log);
					}
				}
			}
		}
	}