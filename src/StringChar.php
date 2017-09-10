<?php
namespace Webos;

class StringChar {
	protected $_string = '';

	public function __construct($string = ''){
		$this->_string = $string;
	}

	public function replace($search, $replace): self {
		$this->_string = str_replace($search, $replace, $this->_string);
		return $this;
	}

	public function replaces(array $replaces = array()): self {
		foreach($replaces as $search => $replace) {
			$this->_string = str_replace($search, $replace, $this->_string);
		}

		return $this;
	}

	public function toUCase(): self {
		$this->_string = strotoupper($this->_string);
		return $this;
	}

	public function toLCase(): self {
		$this->_string = strotoupper($this->_string);
		return $this;
	}

	public function split($separator): string {
		return explode($separator, $this->_string);
	}
	
	public function concat($string): self {
		$this->_string .= $string;
		return $this;
	}
	
	public function lPad($padString, $padLength): self {
		$this->_string = str_pad($this->_string, $padLength, $padString, STR_PAD_LEFT);
		return $this;
	}
	
	public function rPad($padString, $padLength): self {
		$this->_string = str_pad($this->_string, $padLength, $padString, STR_PAD_RIGHT);
		return $this;
	}
	
	public function formatAsNumber($decimals = 2, $glueDec = '.', $glueThousands = ''): self {
		$this->_string = number_format($this->_string, $decimals, $glueDec, $glueThousands);
		return $this;
	}

	public function __toString(): string {
		return $this->_string;
	}
	
	/**
	 * 
	 * @param string $var
	 * @return \Webos\String
	 */
	static public function Create($var = null): self {
		return new StringChar($var);
	}
}

function StringChar($string) {
	return new StringChar($string);
}