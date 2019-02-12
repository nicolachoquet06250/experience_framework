<?php

namespace core;

class MysqlConf extends Conf {
	public function __construct() {
		$cnf = file_get_contents(__DIR__.'/../../external_confs/mysql.json');
		$this->conf = json_decode($cnf, true);
	}
}