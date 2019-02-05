<?php

interface IEmailService extends IService {
	public function set_from_name($name);

	public function join_file($file_path, $name = '');

	public function object($object);

	public function message($message);

	public function to($email, $name = '');

	public function replayTo($email, $name = '');

	public function cc($email, $name = '');

	public function addBcc($email, $name = '');

	public function charset($charset);

	public function send();

	public function get_mailer();
}