<?php

spl_autoload_register(function ($class) {
	$charge = [
		'model' => __DIR__.'/../mvc/models/',
		'service' => __DIR__.'/../services/',
		'dao' => __DIR__.'/../dao/',
		'entity' => __DIR__.'/../entities/',
		'conf' => __DIR__.'/../conf',
	];
	foreach (array_keys($charge) as $class_type) {
		if(strstr($class, ucfirst($class_type))) {
			if(is_file(realpath($charge[$class_type]).'/interfaces/I'.$class.'.php')) {
				require_once realpath($charge[$class_type]).'/interfaces/I'.$class.'.php';
			}
			require_once realpath($charge[$class_type]).'/'.$class.'.php';
			break;
		}
	}
});