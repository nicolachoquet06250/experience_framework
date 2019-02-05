<?php

class JsonService extends Service implements IJsonService {

	public function initialize_after_injection() {}

	public function encode($object) {
		if (is_object($object)) {
			/** @var Base $object */
			$object = $object->toArrayForJson();
		}
		elseif (is_string($object) || is_numeric($object)) {
			$object = [
				'element' => $object,
			];
		}
		return json_encode($object);
	}

	public function decode(string $json, bool $assoc = false, $depth = 512, $options = 0) {
		return json_decode($json, $assoc, $depth, $options);
	}
}