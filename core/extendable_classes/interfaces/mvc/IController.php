<?php

namespace core;

require_once __DIR__.'/../IRunnable.php';
interface IController extends IRunnable {
	public function index();
}