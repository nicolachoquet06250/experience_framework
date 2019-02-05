<?php

interface IResponse {

	const HTML = 'text/html';
	const TEXT = 'plain/text';
	const JSON = 'application/json';

	public static function create($element, $type = self::JSON);

	public function display();
}