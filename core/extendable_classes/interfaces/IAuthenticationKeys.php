<?php

namespace core;


interface IAuthenticationKeys extends IBase {
	public function set_base_key(string $base_key): IAuthenticationKeys;
	public function get_actual_public_key() : string ;
	public function get_actual_private_key() : string ;
	public function write_private_key_in_file() : IAuthenticationKeys;
	public function write_public_key_in_file() : void ;
}