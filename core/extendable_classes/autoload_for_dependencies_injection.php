<?php

use core\External_confs;

require_once __DIR__.'/External_confs.php';

spl_autoload_register(function ($class) {
	$_class = $class;
	$class = explode('\\', $class)[count(explode('\\', $class))-1];

	$external_conf = External_confs::create();

	$charge = [
		'model' => 'models',
		'service' => 'services',
		'dao' => 'dao',
		'entity' => 'entities',
		'conf' => 'conf',
		'context' => 'context',
	];

	foreach (array_keys($charge) as $class_type) {
		if(strstr($class, ucfirst($class_type))) {
			$method = 'get_'.$charge[$class_type].'_dir';
			if($external_conf->$method(false)) {
				if (is_file($external_conf->$method(false, true).'/I'.$class.'.php')) {
					require_once $external_conf->$method(false, true).'/I'.$class.'.php';
				}
				require_once $external_conf->$method(false).'/'.$class.'.php';
			}

			if(is_file($external_conf->$method().'/'.$class.'.php')) {
				if (is_file($external_conf->$method(true, true).'/I'.$class.'.php')) {
					require_once $external_conf->$method(true, true).'/I'.$class.'.php';
				}
				require_once $external_conf->$method().'/'.$class.'.php';
			}
			return;
		}
	}
});