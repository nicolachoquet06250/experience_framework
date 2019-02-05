<?php

use mvc_framework\core\queues\classes\QueueReceiver;
use mvc_framework\core\queues\classes\QueueSender;
use mvc_framework\core\queues\traits\Queue;

class make extends cmd {

	public function __construct($args) {
		parent::__construct($args);
		Queue::$NAMESPACE = '\\';
		Queue::$RECEIVERS_PATH = __DIR__.'/../queues/receivers';
		Queue::$SENDERS_PATH = __DIR__.'/../queues/senders';
		Queue::$ELEMENTS_PATH = __DIR__.'/../queues/elements';
		Queue::$QUEUES_PATH = __DIR__.'/../queues/queues';
	}

	/**
	 * @throws Exception
	 */
	public function send_in_queue() {
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
		/** @var QueueReceiver $queue_email_receiver */
		$queue_email_receiver = $this->queues_loader()->get_service_queue_receiver()->get_queue('email');
		$queue_email_receiver->run();
	}

	/**
	 * @param LoggerService $loggerService
	 * @throws ReflectionException
	 */
	public function test(LoggerService $loggerService) {
		$loggerService->add_constant('TEST', 4);
		$loggerService->logger_callback('TEST', function ($message) {
			var_dump($message);
			var_dump('je suis dans mon logger ajouté');
		}, function ($message) {
			var_dump($message);
			var_dump('j\'envoie dans mon logger ajouté');
		});
		$loggerService->add_loggers(LoggerService::CONSOLE, LoggerService::FILE, LoggerService::MAIL, 'TEST');
		$loggerService->set_email_infos('Nicolas Choquet', 'nicolachoquet06250@gmail.com', 'Un objet');
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