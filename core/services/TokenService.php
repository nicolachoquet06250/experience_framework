<?php

	class TokenService extends Service implements ITokenService {
		public function initialize_after_injection() {}

		public function generate_token_for_user(UserEntity $user) {
			return sha1(sha1(sha1(md5($user->get('email').$user->get('name').$user->get('surname')))));
		}
	}