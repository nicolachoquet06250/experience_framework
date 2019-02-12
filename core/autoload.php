<?php

use core\External_confs;

require_once __DIR__.'/extendable_classes/autoload.php';

require_once __DIR__.'/mvc/models/BaseModel.php';


$external_conf = External_confs::create();

if(is_file($external_conf->get_vendor_dir().'/autoload.php')) {
	require_once $external_conf->get_vendor_dir().'/autoload.php';
}

$dependencies = (new \core\Base())->get_conf('dependencies');
foreach ($dependencies->get_all() as $dir => $dependency) {
	$autoload = 'autoload.php';
	if(is_array($dependency)) {
		if(isset($dependency['autoloader'])) {
			$autoload = $dependency['autoloader'];
		}
	}
	require_once $external_conf->get_git_dependencies_dir().'/'.$dir.'/'.$autoload;
}

if(is_dir($external_conf->get_controllers_dir(false))) {
	$dir = opendir($external_conf->get_controllers_dir(false));
	while (($elem = readdir($dir)) !== false) {
		if ($elem !== '.' && $elem !== '..') {
			if(is_file($external_conf->get_controllers_dir(false).'/'.$elem)) {
				require_once $external_conf->get_controllers_dir(false).'/'.$elem;
			}
			elseif (is_dir($external_conf->get_controllers_dir(false).'/'.$elem)) {
				$_dir = opendir($external_conf->get_controllers_dir(false).'/'.$elem);
				while (($_elem = readdir($_dir)) !== false) {
					if ($_elem !== '.' && $elem !== '..') {
						if(is_file($external_conf->get_controllers_dir(false).'/'.$elem.'/'.$_elem)) {
							require_once $external_conf->get_controllers_dir(false).'/'.$elem.'/'.$_elem;
						}
					}
				}
			}
		}
	}
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
