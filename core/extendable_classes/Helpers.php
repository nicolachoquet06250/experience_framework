<?php
namespace core;


class Helpers {

	public static function recursive_loader($directory, $exclude_directories = [], $exclude_files = []) {
        if (is_dir($directory)) {
            $dir = new \DirectoryIterator($directory);
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
        if (is_dir($directory)) {
            $dir = new \DirectoryIterator($directory);
			foreach ($dir as $elem) {
				if(!$elem->isDot()) {
					if($elem->isFile() && !in_array($elem->getBasename(), $exclude_files)) {
						self::load_file($elem->getPath().'/'.$elem->getBasename());
					}
				}
			}
		}
	}

	public static function var_dump(...$mixed) {
		$string = '';
		foreach ($mixed as $elem) {
			ob_start();
			var_dump($elem);
			$string .= ob_get_contents();
			ob_clean();
		}
		return $string;
	}

}