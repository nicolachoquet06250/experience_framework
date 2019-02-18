<?php

namespace core;

use Exception;

class install extends cmd {
	private $dummy_data = [
		'address' => [
			[
				'id' => 1,
				'address' => "155 ch des combes\n06400, toto",
				'user_id' => 2,
				'type_id' => 1
			],
			[
				'id' => 2,
				'address' => "155 ch des combes\n06400, toto",
				'user_id' => 2,
				'type_id' => 2
			],
		],
		'address_type' => [
			['id' => 1, 'type' => 'De livraison'],
			['id' => 2, 'type' => 'Personnelle'],
			['id' => 3, 'type' => 'Professionnelle']
		],
		'email' => [
			[
				'id' => 1,
				'email' => 'nicolas.choquet@campusid.eu',
				'user_id' => 1
			],
			[
				'id' => 2,
				'email' => 'nicolas.choquet@doctissimo.fr',
				'user_id' => 1
			],
			[
				'id' => 3,
				'email' => 'yann.choquet@sap.fr',
				'user_id' => 2
			]
		],
		'end_status' => [
			[
				'id' => 1,
				'name' => 'En attente'
			],
			[
				'id' => 2,
				'name' => 'En cours'
			],
			[
				'id' => 3,
				'name' => 'Terminé'
			],
			[
				'id' => 4,
				'name' => 'Livré'
			]
		],
		'order' => [
			[
				'id' => 1,
				'comment' => '',
				'user_id' => 2,
				'address_id' => 3,
				'status_id' => 1,
				'end_status' => 4
			],
			[
				'id' => 2,
				'comment' => '',
				'user_id' => 2,
				'address_id' => 3,
				'status_id' => 1,
				'end_status' => 3
			],
			[
				'id' => 3,
				'comment' => '',
				'user_id' => 2,
				'address_id' => 3,
				'status_id' => 1,
				'end_status' => 2
			]
		],
		'order_product' => [
			[
				'id' => 1,
				'product_id' => 1,
				'variant_id' => 1,
				'order_id' => 1
			],
			[
				'id' => 2,
				'product_id' => 2,
				'variant_id' => 3,
				'order_id' => 1
			],
			[
				'id' => 3,
				'product_id' => 3,
				'variant_id' => 6,
				'order_id' => 1
			],
			[
				'id' => 4,
				'product_id' => 1,
				'variant_id' => 1,
				'order_id' => 2
			],
			[
				'id' => 5,
				'product_id' => 2,
				'variant_id' => 3,
				'order_id' => 2
			],
			[
				'id' => 6,
				'product_id' => 3,
				'variant_id' => 6,
				'order_id' => 2
			],
			[
				'id' => 7,
				'product_id' => 1,
				'variant_id' => 1,
				'order_id' => 3
			],
			[
				'id' => 8,
				'product_id' => 2,
				'variant_id' => 3,
				'order_id' => 3
			],
			[
				'id' => 9,
				'product_id' => 3,
				'variant_id' => 6,
				'order_id' => 3
			]
		],
		'order_status' => [
			[
				'id' => 1,
				'name' => 'À livrer'
			],
			[
				'id' => 2,
				'name' => 'À emporter'
			]
		],
		'phone' => [
			[
				'id' => 1,
				'phone' => '0644676545',
				'user_id' => 2
			]
		],
		'product' => [
			[
				'id' => 1,
				'name' => 'San pélégrino',
				'category_id' => 1,
				'comment' => '',
				'image' => '/uploads/boisson.jpg',
				'image_alt' => 'Boisson San Pélégrino',
				'background_dark' => true
			],
			[
				'id' => 2,
				'name' => 'Coca Cola',
				'category_id' => 1,
				'comment' => '',
				'image' => '/uploads/boisson.jpg',
				'image_alt' => 'Boisson Coca Cola',
				'background_dark' => true
			],
			[
				'id' => 3,
				'name' => 'Fanta Orange',
				'category_id' => 1,
				'comment' => '',
				'image' => '/uploads/boisson.jpg',
				'image_alt' => 'Boisson Fanta Orange',
				'background_dark' => true
			],
			[
				'id' => 4,
				'name' => 'Ice The',
				'category_id' => 1,
				'comment' => '',
				'image' => '/uploads/boisson.jpg',
				'image_alt' => 'Boisson Ice The',
				'background_dark' => true
			],
			[
				'id' => 5,
				'name' => '4 Fromages',
				'category_id' => 2,
				'comment' => '',
				'image' => '/uploads/quarter_pizza.jpg',
				'image_alt' => 'Pizza 4 Fromages',
				'background_dark' => true
			],
			[
				'id' => 6,
				'name' => 'Margeritte',
				'category_id' => 2,
				'comment' => '',
				'image' => '/uploads/quarter_pizza.jpg',
				'image_alt' => 'Pizza Margeritte',
				'background_dark' => true
			],
			[
				'id' => 7,
				'name' => '4 Saisons',
				'category_id' => 2,
				'comment' => '',
				'image' => '/uploads/quarter_pizza.jpg',
				'image_alt' => 'Pizza 4 Saisons',
				'background_dark' => true
			],
			[
				'id' => 8,
				'name' => 'Napolitaine',
				'category_id' => 2,
				'comment' => '',
				'image' => '/uploads/quarter_pizza.jpg',
				'image_alt' => 'Pizza Napolitaine',
				'background_dark' => true
			],
			[
				'id' => 9,
				'name' => 'Le grec',
				'category_id' => 3,
				'comment' => '',
				'image' => '/uploads/sandwich.jpg',
				'image_alt' => 'Sandwich grec',
				'background_dark' => false
			],
			[
				'id' => 10,
				'name' => 'L\'italien',
				'category_id' => 3,
				'comment' => '',
				'image' => '/uploads/sandwich.jpg',
				'image_alt' => 'Sandwich italien',
				'background_dark' => false
			],
			[
				'id' => 11,
				'name' => 'Club jambon',
				'category_id' => 3,
				'comment' => '',
				'image' => '/uploads/sandwich.jpg',
				'image_alt' => 'Sandwich club jambon',
				'background_dark' => false
			],
			[
				'id' => 12,
				'name' => 'Club raclette',
				'category_id' => 3,
				'comment' => '',
				'image' => '/uploads/sandwich.jpg',
				'image_alt' => 'Club raclette',
				'background_dark' => false
			]
		],
		'product_category' => [
			[
				'id' => 1,
				'name' => 'Boissons',
				'user_id' => 1
			],
			[
				'id' => 2,
				'name' => 'Pizzas',
				'user_id' => 1
			],
			[
				'id' => 3,
				'name' => 'Sandwichs',
				'user_id' => 1
			]
		],
		'role' => [
			[
				'id' => 1,
				'role' => 'role_vendor',
				'user_id' => 1
			],
			[
				'id' => 2,
				'role' => 'role_customer',
				'user_id' => 2
			]
		],
		'user' => [
			[
				'id' => 1,
				'name' => 'Nicolas',
				'surname' => 'Choquet',
				'email' => 'nicolachoquet06250@gmail.com',
				'phone' => '0763207630',
				'address' => "1102 ch de l espagnol\n06110, Le cannet",
				'password' => '2669NICOLAS2107',
				'description' => 'Je m\'appel Nicolas Choquet et c\'est moi qui ai développé ce site.',
				'profil_img' => '/uploads/ma_photo_de_profile.jpg',
				'premium' => false,
				'active' => true,
				'activate_token' => ''
			],
			[
				'id' => 2,
				'name' => 'Yann',
				'surname' => 'Choquet',
				'email' => 'yannchoquet@gmail.com',
				'phone' => '0625564568',
				'address' => "105 av Francis tonner\n06110, Le cannet",
				'password' => '1204YANN2107',
				'description' => 'No comment',
				'profil_img' => '/uploads/photo_de_profile_yann.jpg',
				'premium' => false,
				'active' => true,
				'activate_token' => ''
			]
		],
		'variant' => [
			[
				'id' => 1,
				'name' => '30 cl',
				'category_id' => 1,
				'price' => 2.50
			],
			[
				'id' => 2,
				'name' => '50 cl',
				'category_id' => 1,
				'price' => 5.50
			],
			[
				'id' => 3,
				'name' => '30 cm',
				'category_id' => 2,
				'price' => 12.50
			],
			[
				'id' => 4,
				'name' => '50 cm',
				'category_id' => 2,
				'price' => 15.50
			],
			[
				'id' => 5,
				'name' => 'demi baguette',
				'category_id' => 3,
				'price' => 10.50
			],
			[
				'id' => 6,
				'name' => 'baguette',
				'category_id' => 3,
				'price' => 12.50
			],
		],
	];
	private $htaccess = "Options +FollowSymlinks
RewriteEngine On

RewriteRule ^external_confs/*$                            core/index.php
RewriteRule ^git_dependencies/*$                          core/index.php
RewriteRule ^vendor/*$                                    core/index.php

RewriteRule ^([a-zA-Z0-9\_]+)$                            core/index.php?controller=$1 [L]
RewriteRule ^([a-zA-Z0-9\_]+)/([a-zA-Z0-9\_]+)$           core/index.php?controller=$1&action=$2 [L]
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
		$install_service->test_databases();
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
	 * @@translate dummy = factise
	 * @throws Exception
	 */
	public function dummy_data() {
		$entities = $this->get_entities();
		foreach ($entities as $entity) {
			if(isset($this->dummy_data[$entity])) {
				$id = 1;
				foreach ($this->dummy_data[$entity] as $entity_array) {
					$entity_object = $this->get_entity($entity);
					if(isset($entity_array['password'])) {
						$entity_array['password'] = sha1(sha1($entity_array['password']));
					}
					$entity_object->initFromArray($entity_array);
					if(!$entity_object->save(false)) {
						throw new Exception('L\'entité '.$entity.' avec l\'id '.$id.' n\'à pas pu être entregistré !!');
					}
					$id++;
				}
			}
		}
		return 'Les données ont biens été installés !!';
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
	public function update_db_structure() {
		foreach ($this->get_entities() as $entity) {
			$this->get_dao($entity)->update_structure();
		}
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
}