<?php

class MysqlService extends Service implements IMysqlService {
	private static $mysql = null;

	/**
	 * @throws Exception
	 */
	public function initialize_after_injection() {
		if(is_null(self::$mysql)) {
			$conf_mysql = $this->get_conf('mysql');
			self::$mysql = new mysqli(
				$conf_mysql->get('host'),
				$conf_mysql->get('user'),
				$conf_mysql->get('password'),
				$conf_mysql->get('database')
			);
		}
	}

	public function get_connector() {
		return self::$mysql;
	}
}