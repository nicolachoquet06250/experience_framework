<?php

require_once __DIR__.'/core/autoload.php';

$external_conf = new External_confs(__DIR__.'/external_confs/custom.json');

if(is_file($external_conf->get_vendor_dir().'/autoload.php')) {
	require_once $external_conf->get_vendor_dir().'/autoload.php';
}

if(is_file($external_conf->get_git_dependencies_dir().'/autoload.php')) {
	require_once $external_conf->get_git_dependencies_dir().'/autoload.php';
}

if(is_dir($external_conf->get_controllers_dir())) {
	$dir = opendir($external_conf->get_controllers_dir());
	while (($elem = readdir($dir)) !== false) {
		if ($elem !== '.' && $elem !== '..') {
			if(is_file($external_conf->get_controllers_dir().'/'.$elem)) {
				require_once $external_conf->get_controllers_dir().'/'.$elem;
			}
			elseif (is_dir($external_conf->get_controllers_dir().'/'.$elem)) {
				$_dir = opendir($external_conf->get_controllers_dir().'/'.$elem);
				while (($_elem = readdir($_dir)) !== false) {
					if ($_elem !== '.' && $elem !== '..') {
						if(is_file($external_conf->get_controllers_dir().'/'.$elem.'/'.$_elem)) {
							require_once $external_conf->get_controllers_dir().'/'.$elem.'/'.$_elem;
						}
					}
				}
			}
		}
	}
}
