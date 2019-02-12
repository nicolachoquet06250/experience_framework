<?php

namespace core;

use Exception;
use mvc_framework\core\queues\classes\QueueReceiver;
use mvc_framework\core\queues\classes\QueueSender;
use mvc_framework\core\queues\traits\Queue;
use ReflectionException;

class make extends cmd {

	private static function init_queues() {
		if(ClassFinder::exists(Queue::class)) {
			Queue::$NAMESPACE      = '\\';
			Queue::$RECEIVERS_PATH = __DIR__.'/../queues/receivers';
			Queue::$SENDERS_PATH   = __DIR__.'/../queues/senders';
			Queue::$ELEMENTS_PATH  = __DIR__.'/../queues/elements';
			Queue::$QUEUES_PATH    = __DIR__.'/../queues/queues';
		}
	}

	/**
	 * @throws Exception
	 */
	public function send_in_queue() {
		self::init_queues();
		/** @var QueueSender $queue_email_sender */
		$queue_email_sender = $this->queues_loader()->get_service_queue_sender()->get_queue('email');
		$queue_email_sender->enqueue(
			[
				'to' => 'nicolachoquet06250@gmail.com',
				'from' => 'nicolachoquet06250@gmail.com',
				'object' => 'Salutation',
				'content' => '<b>Hello</b>'
			]
		);
	}

	/**
	 * @throws Exception
	 */
	public function start_queue() {
		self::init_queues();
		/** @var QueueReceiver $queue_email_receiver */
		$queue_email_receiver = $this->queues_loader()->get_service_queue_receiver()->get_queue('email');
		$queue_email_receiver->run();
	}

	/**
	 * @throws ReflectionException
	 */
	public function test(LoggerService $loggerService) {
		$loggerService->TEST = 'TEST';

		$loggerService->add_constant($loggerService->TEST, 4);
		$loggerService->logger_callback($loggerService->TEST,
			function ($message) {
				var_dump($message);
				var_dump('je suis dans mon logger ajouté');
			}, function ($message) {
				var_dump($message);
				var_dump('j\'envoie dans mon logger ajouté');
			}
		);

		$loggerService->set_log_file('test');
		$loggerService->set_email_infos('Nicolas Choquet', 'nicolachoquet06250@gmail.com', 'Un objet');

		$loggerService->add_loggers(LoggerService::CONSOLE, LoggerService::FILE, LoggerService::MAIL, 'TEST');

		$loggerService->log('coucou');
		$loggerService->send();
	}

	public function custom_conf() {
		$custom = json_decode(file_get_contents(__DIR__.'/../../external_confs/custom.json'), true);
		if($this->get_arg('repo') && $this->get_arg('dir')) {
			$custom['root_directory'] = $this->get_arg('dir');
			$custom['git'] = [
				'repository' => $this->get_arg('repo'),
				'directory' => $this->get_arg('dir'),
			];
		}
		file_put_contents(__DIR__.'/../../external_confs/custom.json', json_encode($custom));
	}

	/**
	 * @throws Exception
	 */
	public function migration(mysqlConf $mysqlConf) {
		$db_prefix = '';
		if($mysqlConf->has_property('db_prefix')) {
			$db_prefix = $mysqlConf->get('db_prefix');
		}
		$logs = [];
		foreach ($this->get_contexts() as $context) {
			$_context = $this->get_context($context, $db_prefix);
			if($_context->create_database()) {
				$logs[] = 'The database '.$_context->get_db_name(false).' has been installed with success !';
			}
			foreach ($_context->get_db_sets() as $property => $db_set) {
				if($db_set->create_table()) {
					$logs[] = 'The Table '.$db_set->get_table_name(false).' has been installed with success in database '.$_context->get_db_name(false);
				}
			}
		}
	}
}