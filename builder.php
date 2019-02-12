<?php

use core\Command;

require_once __DIR__.'/core/autoload.php';

try {
	if(in_array('debug', $argv)) {
		ini_set('display_errors', 'on');
	}
	$t_arg = [];
	foreach ($argv as $i => $arg) {
		if($arg !== 'debug') {
			$t_arg[] = $arg;
		}
	}
	$argv = $t_arg;
	(new \core\Base())->get_conf('trigger');
	Command::create(
		Command::clean_args($argv)
	);
}
catch (Exception $e) {
	exit($e->getMessage()."\n");
}
