<?php
namespace core;

	class TokenService extends Service implements ITokenService {
		public function initialize_after_injection() {}

		public function generate_token_for_user() {
			return '';
		}
	}