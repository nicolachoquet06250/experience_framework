<?php

class BaseModel extends Model {
	/** @var mysqli  */
	private $mysql;

	/**
	 * BaseModel constructor.
	 *
	 * @throws Exception
	 */
	public function __construct() {
		/** @var MysqlService $mysql_service */
		$mysql_service = $this->get_service('mysql');
		$this->mysql = $mysql_service->get_connector();
	}

	protected function get_mysql() {
		return $this->mysql;
	}
}