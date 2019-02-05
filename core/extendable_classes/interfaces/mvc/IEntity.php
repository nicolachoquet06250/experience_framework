<?php

interface IEntity extends IBase {
	public function create_db();

	public function remove_table();

	public function isUpdated();

	public function get_primary_key();

	public function get_fields($except = []);

	public function initFromArray(array $array);

	public function save($exists = true);

	public function delete();

	public function get($prop);

	public function set($prop, $value, $update = false);

	public function get_table_name($for_insert = true);
}