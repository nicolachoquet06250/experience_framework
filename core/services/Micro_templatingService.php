<?php

	class Micro_templatingService extends Service implements IMicro_templatingService {
		private $path;

		public function initialize_after_injection() {}

		public function set_path($path) {
			$this->path = $path;
		}

		/**
		 * @param $template
		 * @return false|string
		 * @throws Exception
		 */
		public function get_by_name($template) {
			if(is_file($this->path.'/'.$template.'.html')) {
				return file_get_contents($this->path.'/'.$template.'.html');
			}
			throw new Exception('template `'.$template.'` not found');
		}

		/**
		 * @param $template
		 * @param array $vars
		 * @return false|mixed|string
		 * @throws Exception
		 */
		public function display($template, $vars = []) {
			$template = $this->get_by_name($template);
			foreach ($vars as $var => $value) {
				$template = str_replace('{{'.$var.'}}', $value, $template);
			}
			return $template;
		}
	}