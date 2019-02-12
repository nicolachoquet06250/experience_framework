<?php

use core\Command;

require_once __DIR__.'/core/autoload.php';

try {
	(new \core\Base())->get_conf('trigger');
	Command::create(
		Command::clean_args($argv)
	);
}
catch (Exception $e) {
	exit($e->getMessage()."\n");
}
