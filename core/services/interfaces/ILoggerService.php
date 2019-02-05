<?php
	interface ILoggerService extends IService {
		public function set_email_infos($from_name, $to, $object);
		public function set_log_file($log_file);
		public function add_logger($type);
		public function add_loggers(...$types);
		public function disable_logger($type);
		public function disable_loggers(...$types);
		public function log($msg, $msg2 = null);
		public function send();
	}