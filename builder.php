<?php

require_once __DIR__.'/autoload.php';

try {
	Command::create(
		Command::clean_args($argv)
	);
}
catch (Exception $e) {
	exit($e->getMessage()."\n");
}
