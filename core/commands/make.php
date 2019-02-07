<?php

use mvc_framework\core\queues\classes\QueueReceiver;
use mvc_framework\core\queues\classes\QueueSender;
use mvc_framework\core\queues\traits\Queue;

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
		if(!is_dir(__DIR__.'/../../external_conf')) {
			mkdir(__DIR__.'/../../external_conf');
		}
		file_put_contents(__DIR__.'/../../external_conf/custom.json', json_encode(
			[
				"root_directory" => "custom",
  				"mvc" => [
					"models" => "mvc/models",
					"views" => "mvc/views",
					"controllers" => "mvc/controllers"
				],
				"services" => "services",
				"confs" => "conf",
				"entities" => "repository/entities",
				"dao" => "repository/dao",
				"responses_class" => "responses",
				"uploads" => [
					"site_images" => "uploads/site",
					"profil_image" => "uploads/profil",
					"loaders" => "uploads/loaders"
				]
			]
		));
	}
}