<?php

use core\Command;

require_once __DIR__.'/core/autoload.php';

try {
	Command::create(
		Command::clean_args($argv)
	);
}
catch (Exception $e) {
	exit($e->getMessage()."\n");
}
