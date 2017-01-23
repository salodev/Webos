<?php
namespace Webos;

class String {
	protected $_string = '';

	public function __construct($string = ''){
		$this->_string = $string;
	}

	public function replace($search, $replace) {
		$this->_string = str_replace($search, $replace, $this->_string);
		return $this;
	}

	public function replaces(array $replaces = array()) {
		foreach($replaces as $search => $replace) {
			$this->_string = str_replace($search, $replace, $this->_string);
		}

		return $this;
	}

	public function toUCase() {
		$this->_string = strotoupper($this->_string);
		return $this;
	}

	public function toLCase() {
		$this->_string = strotoupper($this->_string);
		return $this;
	}

	public function split($separator) {
		return explode($this->_string);
	}

	public function __toString() {
		return $this->_string;
	}
}

function String($string) {
	return new String($string);
}