<?php
namespace core;

use mysqli;

class MysqlService extends Service implements IMysqlService {
	/** @var null|mysqli $mysql */
	private static $mysql = null;
	/** @var Conf $conf_mysql */
	private $conf_mysql;

	/**
	 * @throws \Exception
	 */
	public function initialize_after_injection() {
		if(is_null(self::$mysql)) {
			$this->conf_mysql = $this->get_conf('mysql');
			self::$mysql = new mysqli(
				$this->conf_mysql->get('host'),
				$this->conf_mysql->get('user'),
				$this->conf_mysql->get('password')
			);
			self::$mysql->select_db($this->conf_mysql->get('database'));
		}
	}

	public function get_connector() {
		return self::$mysql;
	}

	public function change_db($database) {
		return self::$mysql->select_db($database);
	}
}