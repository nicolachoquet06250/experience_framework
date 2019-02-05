<?php

class GitService extends Service {
	public function initialize_after_injection() {}

	/**
	 * @throws Exception
	 */
	public function git_path() {
		/** @var OsService $os_service */
		$os_service = $this->get_service('os');
		return $os_service->IAmOnUnixSystem() ? 'git' : '"c:\Program Files\Git\bin\git.exe"';
	}
}