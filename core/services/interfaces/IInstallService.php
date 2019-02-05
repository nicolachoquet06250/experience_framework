<?php

interface IInstallService extends IService {
	public function databases();

	public function test_databases();

	public function drop_test_databases();
}