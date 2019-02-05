<?php

class HtmlResponse extends Response {
	protected $header_type = Response::HTML;

	protected function parse_element() {
		if(is_string($this->element)) {
			$this->parsed_element = $this->element;
		}
		elseif (is_array($this->element)) {
			$this->parsed_element = '';
			foreach ($this->element as $element) {
				$this->parsed_element .= $element;
			}
		}
	}
}