<?php
namespace core;


class Helpers {

	public static function recursive_loader($directory, $exclude_directories = [], $exclude_files = []) {
		$dir = new \DirectoryIterator($directory);
		if($dir->isDir()) {
			foreach ($dir as $elem) {
				if(!$elem->isDot()) {
					if (($elem->isDir() && !in_array($elem->getBasename(), $exclude_directories)) || ($elem->isDir() && empty($exclude_directories))) {
						file_exists($elem->getPath().'/'.$elem->getBasename().'/autoload.php') ? self::load_file($elem->getPath().'/'.$elem->getBasename().'/autoload.php')
							: self::recursive_loader($elem->getPath().'/'.$elem->getBasename(), $exclude_directories, $exclude_files);
					}
					elseif(($elem->isFile() && !in_array($elem->getBasename(), $exclude_files)) || ($elem->isFile() && empty($exclude_files))) {
						self::load_file($elem->getPath().'/'.$elem->getBasename());
					}
				}
			}
		}
	}

	public static function load_file($file) {
		require_once $file;
	}

	public static function load_directory($directory, $exclude_files = []) {
		$dir = new \DirectoryIterator($directory);
		if($dir->isDir()) {
			foreach ($dir as $elem) {
				if(!$elem->isDot()) {
					if($elem->isFile() && !in_array($elem->getBasename(), $exclude_files)) {
						self::load_file($elem->getPath().'/'.$elem->getBasename());
					}
				}
			}
		}
	}

}