<?php

interface IRouter {
	public static function create(string $uri, callable $callback, callable $catch);
}