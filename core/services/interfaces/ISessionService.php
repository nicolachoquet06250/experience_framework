<?php
require_once __DIR__.'/IStorageService.php';
interface ISessionService extends IStorageService {
	public function name($session_name = null);

	public function id($session_id = null);
}