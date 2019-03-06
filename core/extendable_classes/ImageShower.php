<?php

namespace core;


use Exception;

class ImageShower {
	private $images_ext = [
		'jpg' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'png' => 'image/png',
		'svg' => 'image/svg+xml',
		'gif' => 'image/gif',
	];
	private $path;

	/**
	 * ImageShower constructor.
	 *
	 * @throws Exception
	 */
	public function __construct() {
		$external_confs = External_confs::create();
		if(is_file($external_confs->get_root_dir(false).'/'.$_GET['image'])) {
			$this->path = $external_confs->get_root_dir(false).'/'.$_GET['image'];
		}
		elseif (is_file($external_confs->get_root_dir().'/'.$_GET['image'])) {
			$this->path = $external_confs->get_root_dir().'/'.$_GET['image'];
		}
		else {
			throw new Exception('Image `'.$_GET['image'].'` not fond on server');
		}
	}

	private function header() {
		if(isset($this->images_ext[explode('.', $_GET['image'])[count(explode('.', $_GET['image']))-1]])) {
			header('Content-Type: '.$this->images_ext[explode($_GET['image'], '.')[count(explode($_GET['image'], '.'))-1]]);
			return true;
		}
		else {
			return false;
		}
	}

	public function display() {
		if($this->header()) {
			include $this->path;
		}
	}
}