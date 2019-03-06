<?php

namespace core;

use Exception;
use ReflectionClass;
use ReflectionException;

class help extends cmd {
	/**
	 * @param string $header
	 * @param string $retour
	 */
	private function write_before_and_after_header($header, $retour) {
		echo '  ';
		for($i = 0; $i < strlen($header)-4; $i++) {
			echo "#";
		}
		echo '  ';
		echo $retour;
	}

	/**
	 * @throws ReflectionException
	 * @throws Exception
	 */
	public function index(OsService $os_service) {
		$external_confs = External_confs::create();
		$retour = $os_service->get_chariot_return();
		$prefix = $sufix = '  #####  ';
		$header = $prefix.'HELP FOR COMMANDS'.$sufix;
		$this->write_before_and_after_header($header, $retour);
		$header_size = strlen($header);
		echo "$header$retour";
		$this->write_before_and_after_header($header, $retour);
		if($this->has_arg('command')) {
			if(is_file($external_confs->get_commands_dir().'/'.$this->get_arg('command').'.php') || is_file($external_confs->get_commands_dir(false).'/'.$this->get_arg('command').'.php')) {
				if(file_exists($external_confs->get_commands_dir().'/'.$this->get_arg('command').'.php')) {
					$namespace = $external_confs->get_git_repo()['directory'].'\\';
					require_once $external_confs->get_commands_dir(false).'/'.$this->get_arg('command').'.php';
					require_once $external_confs->get_commands_dir().'/'.$this->get_arg('command').'.php';
				}
				elseif (file_exists($external_confs->get_commands_dir(false).'/'.$this->get_arg('command').'.php')) {
					require_once $external_confs->get_commands_dir(false).'/'.$this->get_arg('command').'.php';
					$namespace = 'core\\';
				}
				else {
					$namespace = '\\';
				}
				$ref      = new ReflectionClass($namespace.$this->get_arg('command'));
				$methods  = $ref->getMethods();
				$_methods = [];
				foreach ($methods as $method) {
					if ($method->class !== Base::class && $method->class !== cmd::class && $method->isPublic() && $method->getName() !== '__construct') {
						$_methods[] = $method->name;
					}
				}
				$methods = $_methods;
				foreach ($methods as $method) {
					$command_name = $method;
					$rest_size    = $header_size - strlen($prefix.$command_name.$sufix);
					$marge_right  = $marge_left = (int)($rest_size / 2);
					$line         = $prefix;
					for ($i = 0; $i < $marge_left; $i++) {
						$line .= ' ';
					}
					$line .= $command_name;
					for ($i = 0; $i < $marge_right; $i++) {
						$line .= ' ';
					}

					if (strlen($line) + strlen($sufix) < $header_size) {
						$line .= ' ';
					}

					if (strlen($line) + strlen($sufix) === $header_size) {
						$line .= $sufix;
					}
					elseif (strlen($line) + strlen($sufix) === $header_size + 1) {
						$line .= substr($sufix, 0, strlen($sufix) - 3);
					}
					elseif (strlen($line) + strlen($sufix) === $header_size + 2) {
						$line .= substr($sufix, 0, strlen($sufix) - 4);
					}
					elseif (strlen($line) + strlen($sufix) === $header_size + 3) {
						$line .= substr($sufix, 0, strlen($sufix) - 5);
					}
					elseif (strlen($line) + strlen($sufix) === $header_size + 4) {
						$line .= substr($sufix, 0, strlen($sufix) - 6);
					}
					elseif (strlen($line) + strlen($sufix) === $header_size + 5) {
						$line .= substr($sufix, 0, strlen($sufix) - 7);
					}
					elseif (strlen($line) + strlen($sufix) === $header_size + 6) {
						$line .= substr($sufix, 0, strlen($sufix) - 8);
					}
					$line .= $retour;
					echo $line;
				}
			}
			else {
				$command_name = '`'.$this->get_arg('command').'` not found';
				$rest_size    = $header_size - strlen($prefix.$command_name.$sufix);
				$marge_right  = $marge_left = (int)($rest_size / 2);
				$line         = $prefix;
				for ($i = 0; $i < $marge_left; $i++) {
					$line .= ' ';
				}
				$line .= $command_name;
				for ($i = 0; $i < $marge_right; $i++) {
					$line .= ' ';
				}

				if (strlen($line) + strlen($sufix) < $header_size) {
					$line .= ' ';
				}

				if (strlen($line) + strlen($sufix) === $header_size) {
					$line .= $sufix;
				}
				elseif (strlen($line) + strlen($sufix) === $header_size + 1) {
					$line .= substr($sufix, 0, strlen($sufix) - 3);
				}
				elseif (strlen($line) + strlen($sufix) === $header_size + 2) {
					$line .= substr($sufix, 0, strlen($sufix) - 4);
				}
				elseif (strlen($line) + strlen($sufix) === $header_size + 3) {
					$line .= substr($sufix, 0, strlen($sufix) - 5);
				}
				elseif (strlen($line) + strlen($sufix) === $header_size + 4) {
					$line .= substr($sufix, 0, strlen($sufix) - 6);
				}
				elseif (strlen($line) + strlen($sufix) === $header_size + 5) {
					$line .= substr($sufix, 0, strlen($sufix) - 7);
				}
				elseif (strlen($line) + strlen($sufix) === $header_size + 6) {
					$line .= substr($sufix, 0, strlen($sufix) - 8);
				}
				$line .= $retour;
				echo $line;
			}
		}
		else {
			$directory = __DIR__;
			$dir       = opendir($directory);
			while (($elem = readdir($dir)) !== false) {
				if ($elem !== '.' && $elem !== '..') {
					$command_name = explode('.', $elem)[0];
					$rest_size    = $header_size - strlen($prefix.$command_name.$sufix);
					$marge_right  = $marge_left = (int)($rest_size / 2);
					$line         = $prefix;
					for ($i = 0; $i < $marge_left; $i++) {
						$line .= ' ';
					}
					$line .= $command_name;
					for ($i = 0; $i < $marge_right; $i++) {
						$line .= ' ';
					}

					if (strlen($line) + strlen($sufix) < $header_size) {
						$line .= ' ';
					}
					$line .= $prefix;
					$line .= $retour;
					echo $line;
				}
			}
		}
		$this->write_before_and_after_header($header, $retour);
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	public function conf(MysqlConf $mysql_conf) {
		return [
			$mysql_conf->get('host'),
			$mysql_conf->get('user'),
			$mysql_conf->get('password'),
			$mysql_conf->get('database'),
		];
	}

	public function forks() {
		if (function_exists('pcntl_fork')) {
			function execute_task($task_id) {
				echo "Starting task: ${task_id}\n";
				// Simulate doing actual work with sleep().
				$execution_time = rand(5, 10);
				sleep($execution_time);
				echo "Completed task: ${task_id}. Took ${execution_time} seconds.\n";
			}

			$tasks = [
				"fetch_remote_data",
				"post_async_updates",
				"clear_caches",
				"notify_admin",
			];
			foreach ($tasks as $task) {
				$pid = pcntl_fork();
				if ($pid == -1) {
					exit("Error forking...\n");
				}
				else if ($pid == 0) {
					execute_task($task);
					exit();
				}
			}
			while(pcntl_waitpid(0, $status) != -1);
			echo "Do stuff after all parallel execution is complete.\n";
		}
	}
}