<?php

namespace core;

interface IThreadable {
	public function get_execution_time();

	public function start();
}