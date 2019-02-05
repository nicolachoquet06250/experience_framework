<?php

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
	 */
	public function index(OsService $os_service) {
		$retour = $os_service->get_chariot_return();
		$prefix = $sufix = '  #####  ';
		$header = $prefix.'HELP FOR COMMANDS'.$sufix;
		$this->write_before_and_after_header($header, $retour);
		$header_size = strlen($header);
		echo "$header$retour";
		if($this->has_arg('command')) {
			$class_path = __DIR__.'/'.$this->get_arg('command').'.php';
			if(is_file($class_path)) {
				require_once $class_path;
				$ref      = new ReflectionClass($this->get_arg('command'));
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
	 * @throws Exception
	 */
	public function create_user_with_role(UserDao $user_dao, RoleEntity $role) {
		Command::create(['install:db', 'prefix=test_']);

		// add user
		$user = $user_dao->create(function(Base $object) {
			$user = $object->get_entity('user');
			return $user->initFromArray(
				[
					'name' => 'Nicolas',
					'surname' => 'Choquet',
					'address' => utf8_decode('1102 ch des primevères, 06250 Mougins'),
					'email' => 'nicolachoquet06250@gmail.com',
					'phone' => '',
					'password' => sha1(sha1('2669NICOLAS21071995')),
					'description' => '',
					'profil_img' => '',
					'premium' => false,
					'active' => true,
					'activate_token' => ''
				]
			);
		});

		// add role linked to the last created user
		$role->initFromArray(
			[
				'role' => 'role_user',
				'user_id' => $user->get('id'),
			]
		);
		$role->save(false);
	}

	/**
	 * @throws Exception
	 */
	public function to_array_for_json(UserEntity $user) {
		$user->initFromArray(
			[
				'name' => 'Nicolas',
				'surname' => 'Choquet',
				'address' => utf8_decode('1102 ch des primevères, 06250 Mougins'),
				'email' => 'nicolachoquet06250@gmail.com',
				'phone' => '',
				'password' => sha1(sha1('2669NICOLAS21071995')),
				'description' => '',
				'profil_img' => '',
				'premium' => false,
				'active' => true,
				'activate_token' => ''
			]
		);
		return $user->toArrayForJson();
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	public function conf(mysqlConf $mysql_conf) {
		return [
			$mysql_conf->get('host'),
			$mysql_conf->get('user'),
			$mysql_conf->get('password'),
			$mysql_conf->get('database'),
		];
	}

	/**
	 * @throws Exception
	 */
	public function get_roles_by_user_id(RoleDao $dao) {
		/** @var RoleEntity $roles */
		$roles = $dao->getBy('user_id', 2);
		/** @var RoleEntity $role */
		foreach ($roles as $i => $role) {
			$roles[$i] = $role->toArrayForJson();
		}
		return $roles;
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

	/**
	 * @throws Exception
	 */
	public function threads() {
		($this->get_thread('main'))->start();
	}

	/**
	 * @throws Exception
	 */
	public function test_user_dao(UserDao $user_dao) {
		$user = $user_dao->getId_Name_Surname_Email_DescriptionByEmailAndPassword($this->get_arg('email'), sha1(sha1($this->get_arg('password'))));
		if(is_array($user)) {
			foreach ($user as $i => $_user)
				$user[$i] = $_user->toArrayForJson();
			return $user;
		}
		else return $user->toArrayForJson();
	}

	/**
	 * @throws Exception
	 */
	public function update_structure(UserDao $user_dao) {
		$user_dao->update_structure();
	}
}