<?php
namespace core;

interface IMysqlService extends IService {
	public function get_connector();
}