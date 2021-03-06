<?php

namespace core;

use Exception;

class External_confs {
	private static $instance;
	private $conf;

	private static $DEFAULT_ROOT = 'custom';
	private static $DEFAULT_CONTROLLERS = 'controllers';
	private static $DEFAULT_MODELS = 'models';
	private static $DEFAULT_VIEWS = 'views';
	private static $DEFAULT_CONFS = 'confs';
	private static $DEFAULT_ENTITIES = 'entities';
	private static $DEFAULT_SERVICES = 'services';
	private static $DEFAULT_DAO = 'dao';
	private static $DEFAULT_RESPONSES = 'responses';
	private static $DEFAULT_UPLOADS_SITE = 'uploads/site';
	private static $DEFAULT_UPLOADS_PROFIL = 'uploads/profil';
	private static $DEFAULT_UPLOADS_LOADER = 'uploads/loaders';
	private static $DEFAULT_COMMANDS = 'commands';
	private static $DEFAULT_CONTEXTS = 'contexts';

	/**
	 * External_confs constructor.
	 *
	 * @throws Exception
	 */
	private function __construct() {
		$path = __DIR__.'/../../external_confs/custom.json';
		$file = explode('/', $path)[count(explode('/', $path))-1];
		$dir = str_replace('/'.$file, '', $path);
		if(!is_file($path)) {
			if(!is_dir($dir)) {
				mkdir($dir, 0777, true);
			}
			file_put_contents($path, '{
  "root_directory": "custom",
  "git": {
    "repository": "",
    "directory": "custom"
  },
  "mvc": {
    "models": "mvc/models",
    "views": "mvc/views",
    "controllers": "mvc/controllers"
  },
  "services": "services",
  "confs": "conf",
  "entities": "repository/entities",
  "dao": "repository/dao",
  "responses_class": "responses",
  "commands": "commands",
  "uploads": {
    "site_images": "uploads/site",
    "profil_image": "uploads/profil",
    "loaders": "uploads/loaders"
  }
}
');
		}
		$this->conf = json_decode(file_get_contents($path), true);
	}

	/**
	 * @return External_confs
	 * @throws Exception
	 */
	public static function create() {
		if(!defined('__ROOT__')) {
			define('__ROOT__', realpath(__DIR__.'/../..'));
		}
		if(is_null(self::$instance)) {
			self::$instance = new External_confs();
		}
		return self::$instance;
	}

	public function get_root_dir($custom = true) {
		if ($custom) {
			return realpath(__ROOT__.'/'.(isset($this->conf['root_directory']) ? $this->conf['root_directory'] : self::$DEFAULT_ROOT));
		}
		return $this->get_core_dir();
	}

	private function get_core_dir() {
		return realpath(__ROOT__.'/core');
	}

	public function get_git_repo() {
		return $this->conf['git'];
	}

	private function get_interface_dir($active = false) {
		return $active ? '/interfaces' : '';
	}

	public function get_controllers_dir($custom = true, $interface = false) {
		return realpath($this->get_root_dir($custom).'/'.(isset($this->conf['mvc']['controllers']) ? $this->conf['mvc']['controllers'] : self::$DEFAULT_CONTROLLERS)).$this->get_interface_dir($interface);
	}

	public function get_models_dir($custom = true, $interface = false) {
		return realpath($this->get_root_dir($custom).'/'.(isset($this->conf['mvc']['models']) ? $this->conf['mvc']['models'] : self::$DEFAULT_MODELS)).$this->get_interface_dir($interface);
	}

	public function get_default_views_dir($custom = true, $interface = false) {
		return realpath($this->get_root_dir($custom).'/'.(isset($this->conf['mvc']['views']) ? $this->conf['mvc']['views'] : self::$DEFAULT_VIEWS)).$this->get_interface_dir($interface);
	}

	public function get_conf_dir($custom = true, $interface = false) {
		return realpath($this->get_root_dir($custom).'/'.(isset($this->conf['confs']) ? $this->conf['confs'] : self::$DEFAULT_CONFS)).$this->get_interface_dir($interface);
	}

	public function get_services_dir($custom = true, $interface = false) {
		return realpath($this->get_root_dir($custom).'/'.(isset($this->conf['services']) ? $this->conf['services'] : self::$DEFAULT_SERVICES)).$this->get_interface_dir($interface);
	}

	public function get_entities_dir($custom = true, $interface = false) {
		return realpath($this->get_root_dir($custom).'/'.(isset($this->conf['entities']) ? $this->conf['entities'] : self::$DEFAULT_ENTITIES)).$this->get_interface_dir($interface);
	}

	public function get_dao_dir($custom = true, $interface = false) {
		return realpath($this->get_root_dir($custom).'/'.(isset($this->conf['dao']) ? $this->conf['dao'] : self::$DEFAULT_DAO)).$this->get_interface_dir($interface);
	}

	public function get_responses_dir($custom = true, $interface = false) {
		return realpath($this->get_root_dir($custom).'/'.((isset($this->conf['responses_class'])) ? $this->conf['responses_class'] : self::$DEFAULT_RESPONSES)).$this->get_interface_dir($interface);
	}

	public function get_uploads_site_dir($custom = true, $interface = false) {
		return realpath($this->get_root_dir($custom).'/'.((isset($this->conf['uploads']['site_images'])) ? $this->conf['uploads']['site_images'] : self::$DEFAULT_UPLOADS_SITE)).$this->get_interface_dir($interface);
	}

	public function get_uploads_profil_dir($custom = true, $interface = false) {
		return realpath($this->get_root_dir($custom).'/'.((isset($this->conf['uploads']['profil_image'])) ? $this->conf['uploads']['profil_image'] : self::$DEFAULT_UPLOADS_PROFIL)).$this->get_interface_dir($interface);
	}

	public function get_uploads_loaders_dir($custom = true, $interface = false) {
		return realpath($this->get_root_dir($custom).'/'.((isset($this->conf['uploads']['loaders'])) ? $this->conf['uploads']['loaders'] : self::$DEFAULT_UPLOADS_LOADER)).$this->get_interface_dir($interface);
	}

	public function get_vendor_dir($custom = true) {
		return realpath($this->get_root_dir($custom).'/vendor');
	}

	public function get_git_dependencies_dir() {
        if (!realpath(__ROOT__ . '/git_dependencies')) {
            mkdir(__ROOT__ . '/git_dependencies', 0777, true);
        }
		return realpath(__ROOT__.'/git_dependencies');
	}

	public function get_commands_dir($custom = true) {
        $path = $this->get_root_dir($custom) . '/' . ((isset($this->conf['commands'])) ? $this->conf['commands'] : self::$DEFAULT_COMMANDS);
        if (!realpath($path)) {
            mkdir($path, 0777, true);
        }
        return realpath($path);
	}

	public function get_contexts_dir($custom = true) {
		return realpath($this->get_root_dir($custom).'/'.((isset($this->conf['contexts'])) ? $this->conf['contexts'] : self::$DEFAULT_CONTEXTS));
	}

	public function get_external_conf_dir() {
		return realpath(__ROOT__.'/external_confs');
	}
}