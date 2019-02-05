<?php

class InstallService extends Service implements IInstallService {
	/** @var Conf $mysql_conf */
	protected $mysql_conf;

	/**
	 * @throws Exception
	 */
	public function initialize_after_injection() {
		$this->mysql_conf = $this->get_conf('mysql');
	}

	/**
	 * @throws Exception
	 */
	public function databases() {
		$this->mysql_conf->remove_property('table-prefix', false, false);
		foreach ($this->get_entities() as $entity_name) {
			$entity = $this->get_entity($entity_name);
			if(!$entity->create_db()) {
				throw new Exception('Une erreur est survenue lors de la création de la table '.$entity_name);
			}
		}
		return true;
	}

	/**
	 * @throws Exception
	 */
	public function test_databases() {
		$conf_options = $this->get_conf('options');
		$db_table_prefix = $conf_options->has_property('db-table-prefix') ? $conf_options->get('db-table-prefix') : 'test_';
		$this->mysql_conf->set('table-prefix', $db_table_prefix, false);
		foreach ($this->get_entities() as $entity_name) {
			$entity = $this->get_entity($entity_name);
			if(!$entity->create_db()) {
				throw new Exception('Une erreur est survenue lors de la création de la table '.$entity_name);
			}
		}
	}

	/**
	 * @throws Exception
	 */
	public function drop_test_databases() {
		$conf_options = $this->get_conf('options');
		$db_table_prefix = $conf_options->has_property('db-table-prefix') ? $conf_options->get('db-table-prefix') : 'test_';
		$this->mysql_conf->set('table-prefix', $db_table_prefix, false);
		foreach ($this->get_entities() as $entity_name) {
			$entity = $this->get_entity($entity_name);
			if(!$entity->remove_table()) {
				throw new Exception('Une erreur est survenue lors de la suppression de la table '.$entity_name);
			}
		}
	}
}