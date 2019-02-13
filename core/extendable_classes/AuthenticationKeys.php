<?php

namespace core;


class AuthenticationKeys extends Base implements IAuthenticationKeys {
	use Singleton;

	private $nb_inter_letters = 5;
	private $letters = [
		'a', 'b', 'c', 'd', 'e', 'f',
		'g', 'h', 'i', 'j', 'k', 'l',
		'm', 'n', 'o', 'p', 'q', 'r',
		's', 't', 'u', 'v', 'w', 'x',
		'y', 'z'
	];

	protected $base_key;
	protected $private_key;

	/**
	 * @param string $base_key
	 * @return $this
	 */
	public function set_base_key(string $base_key) : IAuthenticationKeys {
		$this->base_key = $base_key;
		return $this;
	}

	/**
	 * @return string
	 */
	protected function generate_random_public_key() : string {
		$base_key = str_split($this->base_key);
		$tmp_array = [];
		foreach ($base_key as $letter) {
			$tmp_array[] = $letter;
			for($i = 0; $i < $this->nb_inter_letters; $i++) {
				$tmp_array[] = $this->letters[rand(0, count($this->letters)-1)];
			}
		}
		$final_key = implode('', $tmp_array);
		$final_key = sha1($final_key);
		if($final_key !== $this->private_key) {
			return $final_key;
		}
		return $this->generate_random_public_key();
	}

	/**
	 * @return string
	 */
	protected function generate_random_private_key() : string {
		$base_key = str_split($this->base_key);
		$tmp_array = [];
		foreach ($base_key as $letter) {
			$tmp_array[] = $letter;
			for($i = 0; $i < $this->nb_inter_letters; $i++) {
				$tmp_array[] = $this->letters[rand(0, count($this->letters)-1)];
			}
		}
		$final_key = implode('', $tmp_array);
		$final_key = sha1($final_key);
		return $final_key;
	}

	/**
	 * @return false|string
	 * @throws \Exception
	 */
	public function get_actual_public_key() : string {
		$external_confs = External_confs::create();
		if(is_file($external_confs->get_external_conf_dir().'/keys/pbkey.pme')) {
			return file_get_contents($external_confs->get_external_conf_dir().'/keys/pbkey.pme');
		}
		throw new \Exception('public key not found');
	}

	/**
	 * @return false|string
	 * @throws \Exception
	 */
	public function get_actual_private_key() : string {
		$external_confs = External_confs::create();
		if(is_file($external_confs->get_external_conf_dir().'/keys/pvkey.pme')) {
			return file_get_contents($external_confs->get_external_conf_dir().'/keys/pvkey.pme');
		}
		throw new \Exception('private key not found');
	}

	/**
	 * @return AuthenticationKeys
	 * @throws \Exception
	 */
	public function write_private_key_in_file() : IAuthenticationKeys {
		$external_confs = External_confs::create();
		if(!is_dir($external_confs->get_external_conf_dir().'/keys')) {
			mkdir($external_confs->get_external_conf_dir().'/keys', 0777, true);
		}
		file_put_contents($external_confs->get_external_conf_dir().'/keys/pvkey.pme', $this->generate_random_private_key());
		return $this;
	}

	/**
	 * @throws \Exception
	 */
	public function write_public_key_in_file() : void {
		$external_confs = External_confs::create();
		if(!is_dir($external_confs->get_external_conf_dir().'/keys')) {
			mkdir($external_confs->get_external_conf_dir().'/keys', 0777, true);
		}
		file_put_contents($external_confs->get_external_conf_dir().'/keys/pbkey.pme', $this->generate_random_public_key());
	}
}