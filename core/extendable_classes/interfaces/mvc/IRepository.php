<?php

namespace core;

interface IRepository {
	public function get_fields($except = []);

	public function create_table();

	public function getAll();

	public function getAllDesc();

	public function getAllAsc();

	public function getBy($field, $value);

	public function getById($id);

	public function save();

	public function create($entity);

	public function deleteFromId($id);

	public function get_columns();

	public function update_structure();

	public function __call($name, $arguments);
}