<?php
	interface ITokenService extends IService {
		public function generate_token_for_user(UserEntity $user);
	}