<?php
namespace core;

use Exception;

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
		$contexts = $this->get_contexts();
		foreach ($contexts as $context) {
			/** @var DbContext $context */
			$context = $this->get_context($context);
			$context->create_database();
			foreach ($context->get_db_sets() as $db_set) {
				$db_set->create_table();
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

		$contexts = $this->get_contexts();
		foreach ($contexts as $context) {
			/** @var DbContext $context */
			$context = $this->get_context($context);
			$context->set_db_prefix($db_table_prefix);
			$context->create_database();
			foreach ($context->get_db_sets() as $db_set) {
				$db_set->create_table();
			}
		}
		return true;
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