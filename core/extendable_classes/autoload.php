<?php

require_once __DIR__.'/interfaces/IBase.php';
require_once __DIR__.'/Base.php';

if(is_dir(__DIR__.'/interfaces')) {
	$dir = opendir(__DIR__.'/interfaces');
	while (($elem = readdir($dir)) !== false) {
		if ($elem !== '.' && $elem !== '..') {
			if(is_file(__DIR__.'/interfaces/'.$elem)) {
				require_once __DIR__.'/interfaces/'.$elem;
			}
			elseif (is_dir(__DIR__.'/interfaces/'.$elem)) {
				$_dir = opendir(__DIR__.'/interfaces/'.$elem);
				while (($_elem = readdir($_dir)) !== false) {
					if ($_elem !== '.' && $elem !== '..') {
						if(is_file(__DIR__.'/interfaces/'.$elem.'/'.$_elem)) {
							require_once __DIR__.'/interfaces/'.$elem.'/'.$_elem;
						}
					}
				}
			}
		}
	}
}

$dir = opendir(__DIR__);
while (($elem = readdir($dir)) !== false) {
	if($elem !== '.' && $elem !== '..' && $elem !== 'autoload.php') {
		if(is_file(__DIR__.'/'.$elem)) {
			require_once __DIR__.'/'.$elem;
		}
	}
}