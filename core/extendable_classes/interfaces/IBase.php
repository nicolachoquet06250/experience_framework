<?php

interface IBase {
	public function get_model(string $model);
	public function get_service(string $service);
	public function get_dao(string $dao);
	public function get_repository(string $repository);
	public function get_entity(string $entity);
	public function get_conf(string $conf);

	public function toArrayForJson($recursive = true);
}