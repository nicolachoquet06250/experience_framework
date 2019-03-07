<?php

namespace core;

use Exception;

class install extends cmd {
	private $htaccess = "Options +FollowSymlinks
RewriteEngine On

RewriteRule ^$                                            core/index.php?controller=
RewriteRule ^external_confs/*$                            core/index.php
RewriteRule ^git_dependencies/*$                          core/index.php
RewriteRule ^vendor/*$                                    core/index.php

RewriteRule ^([a-zA-Z0-9\_]+)[\/]?$                       core/index.php?controller=$1 [L]
RewriteRule ^([a-zA-Z0-9\_]+)/([a-zA-Z0-9\_]+)[\/]?$      core/index.php?controller=$1&action=$2 [L]
RewriteRule ^([a-zA-Z0-9\_\/]+\.jpg|jpeg|png|gif|svg)$    core/index.php?image=$1 [L]
";
	/**
	 * @throws Exception
	 */
	public function test_db(InstallService $install_service) {
		if(!is_null($this->get_arg('prefix'))) {
			$prefix = $this->get_arg('prefix');
			$this->get_conf('mysql')->set('table-prefix', $prefix, false);
		}
		if($install_service->test_databases()) {
			return 'L\'installation de la base de donnée s\'est effectuée avec succes !!';
		}
		return '';
	}

	/**
	 * @throws Exception
	 * @return string
	 */
	public function db(InstallService $install_service) {
		if($install_service->databases()) {
			return 'L\'installation de la base de donnée s\'est effectuée avec succes !!';
		}
		return '';
	}

	/**
	 * @throws Exception
	 */
	public function drop_test_db(InstallService $install_service) {
		if(!is_null($this->get_arg('prefix'))) {
			$prefix = $this->get_arg('prefix');
			$this->get_conf('mysql')->set('table-prefix', $prefix, false);
		}
		$install_service->drop_test_databases();
	}

	/**
	 * @throws Exception
	 */
	public function update_db_structure(LoggerService $logger) {
		$logger->add_logger(LoggerService::CONSOLE);
		foreach ($this->get_contexts() as $context) {
			$context = $this->get_context($context);
			foreach ($context->get_db_sets() as $db_set) {
				$updates = $db_set->update_structure();

				$logger->log($updates['nb_add'] === -1 ? 'Une erreur est survenue lors de l\'ajout des champs dans la tables '.$db_set->get_table_name(false)
								 : $updates['nb_add'].' champs ont été ajoutés dans la table '.$db_set->get_table_name(false));

				$logger->log($updates['nb_delete'] === -1 ? 'Une erreur est survenue lors de la suppression des champs dans la tables '.$db_set->get_table_name(false)
								 : $updates['nb_delete'].' champs ont été supprimés dans la table '.$db_set->get_table_name(false));
			}
		}
		$logger->send();
	}

	/**
	 * @throws Exception
	 */
	public function dependencies(DependenciesService $dependencies, OsService $osService) {
		AuthenticationKeys::create()
						  ->set_base_key(self::$authentication_key)
						  ->write_private_key_in_file()
						  ->write_public_key_in_file();

		if(!is_dir(__DIR__.'/../../external_confs')) {
			throw new Exception('Veuillez créer un fichier de configuration custom.json. Pour celà, lancez la commande `php builder.php make:custom_conf`');
		}
		$external_conf = External_confs::create();
		if(!is_dir(__DIR__.'/../../'.$external_conf->get_git_repo()['directory'])) {
			echo 'Installation du repository git `'.$external_conf->get_git_repo()['repository'].'`'."\n";
			exec($osService->git_path().' clone '.$external_conf->get_git_repo()['repository'].' '.__DIR__.'/../../'.$external_conf->get_git_repo()['directory']);
		}
		else {
			echo 'Mise à jour du repository git `'.$external_conf->get_git_repo()['directory'].'`'."\n";
			exec('cd '.__DIR__.'/../../'.$external_conf->get_git_repo()['directory'].' && '.$osService->git_path().' pull', $output);
			echo implode("\n", $output)."\n";
		}
		foreach ($dependencies->get_dependencies() as $dir => $dependency) {
			$_composer = false;
			if(is_array($dependency)) {
				if(isset($dependency['composer'])) {
					$_composer = $dependency['composer'];
				}
				$dependency = $dependency['repository'];
			}
			if(!is_dir(__DIR__.'/../../git_dependencies/'.$dir)) {
				echo 'Installation du repository git `'.$dir.'`'."\n";
				exec($osService->git_path().' clone '.$dependency.' '.$external_conf->get_git_dependencies_dir().'/'.$dir);
				if($_composer) {
					exec('cd '.$external_conf->get_git_dependencies_dir().'/'.$dir.' && '.$osService->composer(false).' install');
				}
			}
			else {
				echo 'Mise à jour du repository git `'.$dir.'`'."\n";
				exec('cd '.__DIR__.'/../../git_dependencies/'.$dir.' && '.$osService->git_path().' pull', $output);
				echo implode("\n", $output)."\n";
				if($_composer) {
					if (is_dir($external_conf->get_git_dependencies_dir().'/'.$dir.'/vendor')) {
						exec('cd '.$external_conf->get_git_dependencies_dir().'/'.$dir.' && '.$osService->composer(false).' update');
					}
					else {
						exec('cd '.$external_conf->get_git_dependencies_dir().'/'.$dir.' && '.$osService->composer(false).' install');
					}
				}
			}
		}
		$composer_core = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);
		if(is_file(__DIR__.'/../../'.$external_conf->get_git_repo()['directory'].'/composer.json')) {
			$composer_custom = json_decode(file_get_contents(__DIR__.'/../../'.$external_conf->get_git_repo()['directory'].'/composer.json'), true);
		}
		else {
			$composer_custom = [];
		}
		$composer['name'] = isset($composer_custom['name']) ? $composer_custom['name'] : $composer_core['name'];
		$composer['description'] = isset($composer_custom['description']) ? $composer_custom['description'] : $composer_core['description'];
		$composer['description'] = isset($composer_custom['description']) ? $composer_custom['description'] : $composer_core['description'];
		$composer['authors'] = isset($composer_custom['authors']) ? array_merge($composer_core['authors'], $composer_custom['authors']) : $composer_core['authors'];
		$composer['require'] = isset($composer_custom['require']) ? array_merge($composer_core['require'], $composer_custom['require']) : $composer_core['require'];
		$composer['require-dev'] = isset($composer_custom['require-dev']) ? array_merge($composer_core['require-dev'], $composer_custom['require-dev']) : $composer_core['require-dev'];
		if(empty($composer['require-dev'])) {
			unset($composer['require-dev']);
		}
		if(!is_file(__DIR__.'/../../composer.json') || (file_get_contents(__DIR__.'/../../composer.json') === json_encode($composer) && is_file(__DIR__.'/../../composer.lock'))) {
			file_put_contents(__DIR__.'/../../composer.json', json_encode($composer));
		}

		$command = (is_file(__DIR__.'/../../composer.phar') ? $osService->composer(true) : $osService->composer(false)).' ';
		$command .= is_file(__DIR__.'/../../composer.lock') ? 'update' : 'install';
		exec($command);

		if(!is_file(__DIR__.'/../../.htaccess') || file_get_contents(__DIR__.'/../../.htaccess') !== $this->htaccess) {
			file_put_contents(__DIR__.'/../../.htaccess', $this->htaccess);
		}
		if(!is_dir(__DIR__.'/../../logs')) {
			mkdir(__DIR__.'/../../logs', 0777, true);
		}
	}

	public function custom_repository(OsService $osService) {
		if($this->has_arg('exists') && (is_int($this->get_arg('exists')) || is_bool($this->get_arg('exists'))) && $this->get_arg('exists')) {
			if($this->has_arg('repository') && $this->has_arg('directory')) {
				$directory = $this->get_arg('directory');
				$repository = $this->get_arg('repository');
				exec($osService->git_path().' clone '.$repository.' '.__ROOT__.'/'.$directory);
			}
		}
		else {
			if($this->has_arg('directory')) {
				$directory = $this->get_arg('directory');
				$repository = $this->get_arg('repository');
				$commit_message = $this->has_arg('message') ? $this->get_arg('message') : 'initialize custom repo with experience_framework commands';
				exec('cd '.__ROOT__.'/'.$directory.
					 ' && git init && git remote add origin '.$repository.
					 ' && git pull origin master && git add . && git commit -m"'.$commit_message
					 .'" && git push origin master');
			}
		}
	}
}