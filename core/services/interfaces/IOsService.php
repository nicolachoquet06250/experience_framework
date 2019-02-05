<?php

interface IOsService extends IService {
	public function IAmOnWindowsSystem();

	public function IAmOnUnixSystem();

	public function get_chariot_return();
}