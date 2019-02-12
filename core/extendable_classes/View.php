<?php

namespace core;


use Exception;

class View extends Base {
	protected $directory;
	protected $tpl_ext = '.html';
	protected $content = '';
	public function set_directory(string $directory) {
		$this->directory = $directory;
	}

	/**
	 * @param string $template
	 * @param array $vars
	 * @return false|string
	 * @throws Exception
	 */
	public function display($template = 'index', $vars = []) {
		$external_confs = External_confs::create();

		if(!is_file($this->directory.'/'.$template.$this->tpl_ext)) {
			throw new Exception('View file `'.$this->directory.'/'.$template.' not found');
		}
		$content = null;
		if($this->content === '') {
			$this->content = file_get_contents($this->directory.'/'.$template.$this->tpl_ext);
		}
		else {
			return file_get_contents($this->directory.'/'.$template.$this->tpl_ext);
		}
		preg_match('`@file ([a-zA-Z0-9\.]+)@`', $this->content, $matches);
		if(!empty($matches)) {
			$this->content = str_replace($matches[0], $this->display($matches[1], $vars), $this->content);
		}

		preg_match('`@file core ([a-zA-Z0-9\.]+)@`', $this->content, $matches);
		if(!empty($matches)) {
			$tmp_directory = $this->directory;
			$this->set_directory(str_replace($external_confs->get_git_repo()['directory'].'/', 'core/', $this->directory));
			$this->content = str_replace($matches[0], $this->display($matches[1], $vars), $this->content);
			$this->set_directory($tmp_directory);
		}

		foreach ($vars as $var => $value) {
			$this->content = str_replace('{{$'.$var.'}}', $value, $this->content);
		}
		return $this->content;
	}
}