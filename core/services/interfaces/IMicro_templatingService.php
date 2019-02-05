<?php
	interface IMicro_templatingService extends IService {
		public function set_path($path);

		/**
		 * @param $template
		 * @return false|string
		 * @throws Exception
		 */
		public function get_by_name($template);

		/**
		 * @param $template
		 * @param array $vars
		 * @return false|mixed|string
		 * @throws Exception
		 */
		public function display($template, $vars = []);
	}