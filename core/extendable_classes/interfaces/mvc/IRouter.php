<?php

namespace core;

interface IRouter {
	public static function create(string $uri, callable $callback, callable $catch);
}