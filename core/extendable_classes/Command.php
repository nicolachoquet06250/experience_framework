<?php

class Command extends Base implements ICommand {
	private static $default_action = 'index';
	private static $default_command = 'help';
	public static function clean_args($args) {
		unset($args[0]);
		$_args = [];
		foreach ($args as $arg) {
			$_args[] = $arg;
		}
		return $_args;
	}

	/**
	 * @param $args
	 * @throws Exception
	 */
	public static function create($args) {
		if(count($args) > 0) {
			if (strstr($args[0], ':')) {
				$args[0] = explode(':', $args[0]);
			}
			else {
				$args[0] = [
					$args[0],
				];
			}
		}
		else
			$args[0] = [
				null
			];
		$controller = (is_null($args[0][0])) ? self::$default_command : $args[0][0];
		$action = isset($args[0][1]) ? $args[0][1] : self::$default_action;
		$args = self::clean_args($args);
		require_once __DIR__.'/../commands/'.$controller.'.php';
		/** @var cmd $cmd */
		$cmd = new $controller($args);
		$result = $cmd->run($action);
		if($result) {
			if(is_array($result)) {
				var_dump($result);
			}
			if(is_string($result)) {
				echo $result."\n";
			}
			if(is_numeric($result)) {
				exit($result);
			}
		}
	}
}