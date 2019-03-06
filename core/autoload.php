<?php

use core\Base;
use core\DependenciesService;
use core\External_confs;
use core\Helpers;

require_once __DIR__.'/extendable_classes/Helpers.php';
require_once __DIR__.'/extendable_classes/autoload.php';
require_once __DIR__.'/mvc/models/BaseModel.php';

$external_conf = External_confs::create();
/** @var DependenciesService $dependencies */
$dependencies = (new Base())->get_service('dependencies');

if(is_file($external_conf->get_vendor_dir().'/autoload.php')) {
	require_once $external_conf->get_vendor_dir().'/autoload.php';
}

foreach ($dependencies->get_dependencies() as $dir => $dependency) {
	$autoload = 'autoload.php';
	$autoload_php = '';
	if(is_array($dependency)) {
		if(isset($dependency['autoloader'])) {
			$autoload = $dependency['autoloader'];
		}
		if(isset($dependency['autoloader_php'])) {
			$autoload_php = $dependency['autoloader_php'];
		}
	}
	if($autoload && file_exists($external_conf->get_git_dependencies_dir().'/'.$dir.'/'.$autoload)) {
		Helpers::load_file($external_conf->get_git_dependencies_dir().'/'.$dir.'/'.$autoload);
	}
	if($autoload_php !== '') {
		eval($autoload_php);
	}
}

Helpers::recursive_loader($external_conf->get_controllers_dir(false));
Helpers::recursive_loader($external_conf->get_controllers_dir());
