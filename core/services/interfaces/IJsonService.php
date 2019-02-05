<?php

interface IJsonService extends IService {
	public function encode($object);

	public function decode(string $json, bool $assoc = false, $depth = 512, $options = 0);
}