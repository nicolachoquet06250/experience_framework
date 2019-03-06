<?php
namespace core;

	interface ITokenService extends IService {
		public function generate_token_for_user();
	}