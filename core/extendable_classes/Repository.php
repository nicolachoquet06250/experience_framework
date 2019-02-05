<?php

class Repository extends Base implements IRepository {
	private $entity_class;
	private $mysql;
	protected $table_name;
	private $result = [];
	protected $mysql_conf;

	/**
	 * Repository constructor.
	 *
	 * @throws Exception
	 */
	public function __construct() {
		$this->entity_class = get_class($this);
		$this->entity_class = explode("\\", $this->entity_class)[count(explode("\\", $this->entity_class))-1];
		$this->entity_class = str_replace(
			[
				'Repository',
				'Dao'
			], 'Entity', $this->entity_class);
		$this->table_name = strtolower(str_replace('Entity', '', $this->entity_class));
		require_once __DIR__.'/../entities/'.$this->entity_class.'.php';
		/** @var MysqlService $mysql_service */
		$this->mysql_conf = $this->get_conf('mysql');
		$mysql_service = $this->get_service('mysql');
		$this->mysql = $mysql_service->get_connector();
	}

	/**
	 * @param array $except
	 * @return array
	 * @throws Exception
	 */
	public function get_fields($except = []) {
		return $this->get_entity($this->table_name)
					->get_fields($except);
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function create_db() {
		return $this->get_entity($this->table_name)
					->create_db();
	}

	/**
	 * @return mysqli
	 */
	protected function get_mysql() {
		return $this->mysql;
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	public function getAll() {
		$query = $this->get_mysql()->query('SELECT * FROM '.$this->get_table_name());
		$entities = [];
		$entity_class = $this->entity_class;
		while ($entity = $query->fetch_assoc()) {
			/** @var Entity $_entity */
			$_entity = new $entity_class();
			foreach ($entity as $key => $value) {
				$_entity->set($key, $value);
			}
			$entities[] = $_entity;
		}
		$this->result = $entities;
		return $entities;
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	public function getAllDesc() {
		$query = $this->get_mysql()->query('SELECT * FROM '.$this->get_table_name().' ORDER BY `id` DESC');
		$entities = [];
		$entity_class = $this->entity_class;
		while ($entity = $query->fetch_assoc()) {
			/** @var Entity $_entity */
			$_entity = new $entity_class();
			foreach ($entity as $key => $value) {
				$_entity->set($key, $value);
			}
			$entities[] = $_entity;
		}
		$this->result = $entities;
		return $entities;
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	public function getAllAsc() {
		$query = $this->get_mysql()->query('SELECT * FROM '.$this->get_table_name().' ORDER BY `id` ASC');
		$entities = [];
		$entity_class = $this->entity_class;
		while ($entity = $query->fetch_assoc()) {
			/** @var Entity $_entity */
			$_entity = new $entity_class();
			foreach ($entity as $key => $value) {
				$_entity->set($key, $value);
			}
			$entities[] = $_entity;
		}
		$this->result = $entities;
		return $entities;
	}

	/**
	 * @param $field
	 * @param $value
	 * @return array|bool
	 * @throws Exception
	 */
	public function getBy($field, $value) {
		if($value instanceof Entity) {
			$value = $value->get($field);
		}
		$query = $this->get_mysql()
					  ->query('SELECT * FROM '.$this->get_table_name().' WHERE `'.$field.'`='
							  .(gettype($value) === 'string' ? '"'.$value.'"' : $value));
		$entities = [];
		$entity_class = $this->entity_class;
		if($query) {
			while ($entity = $query->fetch_assoc()) {
				/** @var Entity $_entity */
				$_entity = new $entity_class();
				foreach ($entity as $key => $value) {
					$_entity->set($key, $value);
				}
				$entities[] = $_entity;
			}
			$this->result = $entities;
			return $entities;
		}
		return false;
	}

	/**
	 * @param $id
	 * @return Entity|bool
	 * @throws Exception
	 */
	public function getById($id) {
		$result = $this->getBy('id', $id);
		return $result ? $result[0] : $result;
	}

	/**
	 * @throws Exception
	 */
	public function save() {
		/** @var Entity $entity */
		foreach ($this->result as $entity) {
			if($entity->isUpdated()) {
				$entity->save();
			}
		}
	}

	/** @param Entity|callable $entity
	 * @return bool|Entity
	 * @throws Exception
	 */
	public function create($entity) {
		if(get_class($entity) === 'Closure') {
			$entity = $entity(new Base());
		}
		return $entity->save(false);
	}

	/**
	 * @param $id
	 * @return bool
	 * @throws Exception
	 */
	public function deleteFromId($id) {
		$this->getAll();
		$nb = count($this->result);
		/** @var Entity $entity */
		foreach ($this->result as $entity) {
			if($entity->get('id') === $id) {
				$entity->delete();
			}
		}
		$new_nb = count($this->result);
		if($nb === $new_nb) {
			return false;
		}
		return true;
	}

	/**
	 * @param bool $for_insert
	 * @return string
	 */
	protected function get_table_name($for_insert = true) {
		$prefix = '';
		if($this->mysql_conf->has_property('table-prefix')) {
			$prefix = $this->mysql_conf->get('table-prefix');
		}
		$table_name = '';
		if($for_insert) {
			$table_name .= '`';
		}
		$table_name .= $prefix.$this->table_name;
		if($for_insert) {
			$table_name .= '`';
		}
		return $table_name;
	}

	/**
	 * @return array
	 */
	public function get_columns() {
		$query = $this->get_mysql()->query('SHOW COLUMNS FROM '.$this->get_table_name());
		$columns = [];
		while (list($field, $type, $null, $key, $default, $extra) = $query->fetch_array()) {
			$columns[$field] = [
				'type' => $type,
				'null' => $null,
				'key' => $key,
				'default' => $default,
				'extra' => $extra,
			];
		}
		return $columns;
	}

	/**
	 * @throws Exception
	 */
	public function update_structure() {
		// in database
		$columns_names = array_keys($this->get_columns());
		$columns = $this->get_columns();
		// in entity
		$properties = $this->get_entity($this->table_name)->get_fields();

		$to_add = [];
		foreach ($properties as $property_name => $property_detail) {
			if(!in_array($property_name, $columns_names)) {
				$to_add[] = $property_name;
			}
		}

		$request_add = 'ALTER TABLE '.$this->get_table_name();
		$max = count($to_add);
		$i = 0;
		$last_item = $columns_names[count($columns)-1];
		foreach ($to_add as $item) {
			$detail = $this->get_fields()[$item];
			if(!isset($details['in_table'])) {
				$request_add .= ' ADD `'.$item.'` ';
				$size = null;
				if ($detail['type'] === 'int' || $detail['type'] === 'float') $size = 11;
				elseif (isset($detail['sql']['type']) && isset($detail['sql']['size'])) $size = $detail['sql']['size'];
				$request_add .= strtoupper((isset($detail['sql']['type']) ? $detail['sql']['type'] : $detail['type'])).($size ? '('.$size.')' : '').' '.
							(!$detail['sql']['nullable'] ? 'NOT NULL' : '');
				if (isset($details['key']) && $detail['key'] === 'primary') {
					$request_add .= 'AUTO_INCREMENT PRIMARY KEY';
				}
				$request_add .= ' AFTER `'.$last_item.'`';
				$last_item = $item;
				$i++;
				if ($i < $max) {
					$request_add .= ',';
				}
			}
		}
		$this->get_mysql()->query($request_add);

//		$request_update = 'ALTER TABLE '.$this->get_table_name();
//
//		$to_update = [];
//		foreach ($properties as $property_name => $property_detail) {
//			$columns_detail = $columns[$property_name];
//
//			$nullable = !(isset($columns_detail['null']) && $columns_detail['null'] === 'NO');
//			$primary = isset($columns_detail['key']) && $columns_detail['key'] === 'PRI';
//			if(strstr($columns_detail['type'], 'int')) {
//				$type = $type_sql = 'int';
//				$size = (int)str_replace(['int(', ')'], '', $columns_detail['type']);
//			}
//			elseif (strstr($columns_detail['type'], 'varchar')) {
//				$type = 'string';
//				$type_sql = 'varchar';
//				$size = (int)str_replace(['varchar(', ')'], '', $columns_detail['type']);
//			}
//			elseif (strstr($columns_detail['type'], 'text')) {
//				$type = 'string';
//				$type_sql = 'text';
//				$size = null;
//			}
//			elseif (strstr($columns_detail['type'], 'tinyint')) {
//				$type = 'bool';
//				$type_sql = 'boolean';
//				$size = null;
//			}
//			else {
//				$type = null;
//				$type_sql = null;
//				$size = null;
//			}
//
//			if($nullable !== $property_detail['sql']['nullable'] || $primary !== (isset($property_detail['primary']) && $property_detail['primary'])
//			   || $type_sql !== $property_detail['sql']['type'] || $size !== $property_detail['sql']['size']
//			   || $type !== $property_detail['type']) {
//				$to_update[$property_name] = [
//					'type' => $type,
//					'primary' => $primary,
//					'sql' => [
//						'type' => $type_sql,
//						'size' => $size,
//						'nullable' => $nullable,
//					]
//				];
//			}
//		}
//		var_dump($to_update);

	}

	/**
	 * @param string $column
	 * @return bool
	 */
	private function column_exists(string $column) {
		$columns = $this->get_columns();
		foreach($columns as $field) {
			if($column === $field)
				return true;
		}
		return false;
	}

	public function __call($name, $arguments) {
		$regexs = [
			'`getBy([A-Z][a-z0-9\_]+)(And|Or)([A-Z][a-z0-9\_]+)+`' => function($matches, &$arguments) {
				$array = [];
				$_matches = [];
				unset($matches[0]);
				foreach ($matches as $item) {
					$_matches[] = $item;
				}
				$matches = $_matches;

				$i = 0;
				$j = 0;
				foreach ($matches as $match) {
					if($match !== 'And' && $match !== 'Or') {
						$array[strtolower($match)] = [
							'value' => $arguments[$i],
							'operator' => (isset($matches[$j+1]) && ($matches[$j+1] === 'And' || $matches[$j+1] === 'Or') ? strtoupper($matches[$j+1]) : null),
						];
						$i++;
					}
					$j++;
				}
				$request = 'SELECT * FROM '.$this->get_table_name().' WHERE ';
				foreach ($array as $field => $detail) {
					$request .= $field.'='.(is_string($detail['value']) ? '"'.$detail['value'].'"' : $detail['value']).' ';
					if(!is_null($detail['operator'])) {
						$request .= $detail['operator'].' ';
					}
				}
				$query = $this->get_mysql()->query($request);
				$result = [];
				while ($data = $query->fetch_assoc()) {
					$local = [];
					foreach ($this->get_entity($this->table_name)->get_fields() as $field => $detail) {
						$local[$field] = $data[$field];
					}
					$result[] = $this->get_entity($this->table_name)
									 ->initFromArray($local);
				}
				if(count($result) === 1) {
					$result = $result[0];
				}
				elseif (count($result) === 0) {
					return false;
				}
				return $result;
			},
			'`getBy([A-Z][a-z0-9\_]+)`' => function($matches, &$arguments) {
				if($this->column_exists(strtolower($matches[1]))) {
					$request = 'SELECT * FROM '.$this->get_table_name().' WHERE `'.strtolower($matches[1]).'`="'.$arguments[0].'"';
					$query = $this->get_mysql()->query($request);
					$result = [];
					while ($data = $query->fetch_assoc()) {
						$local = [];
						foreach ($this->get_entity($this->table_name)->get_fields() as $field => $detail) {
							$local[$field] = $data[$field];
						}
						$result[] = $this->get_entity($this->table_name)
										 ->initFromArray($local);
					}
					if(count($result) === 1) {
						$result = $result[0];
					}
					elseif (count($result) === 0) {
						return false;
					}
					return $result;
				}
				return [];
			},
			'`get([A-Za-z0-9\_]+)By([A-Z][a-z0-9\_]+)(And|Or)([A-Z][a-z0-9\_]+)+`' => function($matches, &$arguments) {

				$_matches = [];
				unset($matches[0]);
				foreach ($matches as $item) {
					$_matches[] = $item;
				}
				$matches = $_matches;

				$matches[0] = strtolower($matches[0]);
				$fields_selected = explode('_', $matches[0]);
				$_field_selected = [];
				foreach ($fields_selected as $i => $field_selected) {
					if($this->column_exists($field_selected))
						$_field_selected[] = $field_selected;
				}
				$fields_selected = $_field_selected;

				$_matches = [];
				unset($matches[0]);
				foreach ($matches as $item) {
					$_matches[] = $item;
				}
				$matches = $_matches;

				$array = [];
				$i = 0;
				$j = 0;
				foreach ($matches as $match) {
					if($match !== 'And' && $match !== 'Or') {
						$array[strtolower($match)] = [
							'value' => $arguments[$i],
							'operator' => (isset($matches[$j+1]) && ($matches[$j+1] === 'And' || $matches[$j+1] === 'Or') ? strtoupper($matches[$j+1]) : null),
						];
						$i++;
					}
					$j++;
				}
				$request = 'SELECT `'.implode('`, `', $fields_selected).'` FROM '.$this->get_table_name().' WHERE ';
				foreach ($array as $field => $detail) {
					$request .= $field.'='.(is_string($detail['value']) ? '"'.$detail['value'].'"' : $detail['value']).' ';
					if(!is_null($detail['operator'])) {
						$request .= $detail['operator'].' ';
					}
				}

				$query = $this->get_mysql()->query($request);
				$result = [];
				while ($data = $query->fetch_assoc()) {
					$local = [];
					foreach ($this->get_entity($this->table_name)->get_fields() as $field => $detail) {
						if(isset($data[$field])) {
							$local[$field] = $data[$field];
						}
					}
					if(count($local) < $this->get_entity($this->table_name)->get_fields()) {
						$entity = $this->get_entity($this->table_name);
						foreach ($local as $field => $value) {
							$entity->set($field, $value);
						}
					}
					else {
						$entity = $this->get_entity($this->table_name)
										 ->initFromArray($local);
					}
					$result[] = $entity;
				}
				if(count($result) === 1) {
					$result = $result[0];
				}
				elseif (count($result) === 0) {
					return false;
				}
				return $result;
			},
			'`delete([A-Za-z0-9\_]+)Where([A-Za-z0-9\_]+)`' => function($matches, &$arguments) {
				// TODO: create method code here
			},
			'`update([A-Za-z0-9\_]+)Where([A-Za-z0-9\_]+)`' => function($matches, &$arguments) {
				// TODO: create method code here
			},
		];

		$exists = false;
		foreach ($regexs as $regex => $callback) {
			preg_match($regex, $name, $matches);
			if(empty($matches)) {
				continue;
			}
			$exists = [
				'callback' => $callback,
				'matches' => $matches,
			];
			break;
		}
		if($exists) return $exists['callback']($exists['matches'], $arguments);
		else if(in_array($name, get_class_methods(get_class($this)))) {
			$params = '';
			$i = 0;
			foreach ($arguments as $argument) {
				if(is_string($argument)) {
					$params .= '"'.$argument.'"';
				}
				elseif (is_numeric($argument)) {
					$params .= $argument;
				}
				elseif (is_object($argument)) {
					$params .= '$arguments['.$i.']';
				}
				$i++;
			}
			return eval("$this->$name($params);");
		}
		return [];
	}
}