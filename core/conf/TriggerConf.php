<?php

namespace core;


class TriggerConf extends Conf {
	use FactisSingleton;
	const BeforeSendEmail = 'BeforeSendEmail';
	const AfterSendEmail = 'AfterSendEmail';

	/** @var TriggerService $triggers */
	protected $triggers;

	/**
	 * TriggerConf constructor.
	 *
	 * @throws \Exception
	 */
	public function __construct() {
		$this->triggers = $this->get_service('trigger');
		$this->init_triggers();
	}

	protected function init_triggers() {
		$this->triggers->register(self::BeforeSendEmail, function (LoggerService $loggerService) {
			$loggerService->add_logger(LoggerService::CONSOLE);
			$loggerService->log('hello je suis avant l\'envoie d\'un email');
		}, 1);

		$this->triggers->register(self::AfterSendEmail, function (LoggerService $loggerService) {
			$loggerService->add_logger(LoggerService::CONSOLE);
			$loggerService->log('hello je suis après l\'envoie d\'un email');
		}, 1);
	}
}