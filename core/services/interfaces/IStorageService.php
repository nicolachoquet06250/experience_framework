<?php

interface IStorageService extends IService {
	public function set(string $key, $value, $domain);

	public function get(string $key);

	public function remove(string $key);

	public function has_key(string $key);
}