<?php

namespace Webos\Stream;
use Exception;

class Handler {
	
	private $_list = [];
	
	public function addContent(Content $content) {
		$this->_list[] = $content;
	}
	
	public function clear() {
		$this->_list = [];
	}
	
	public function getLastAndRemove(): Content {
		if (!count($this->_list)) {
			throw new Exception('No Content for steam');
		}
		return array_shift($this->_list);
	}
	
	public function getStoreFolder() {
		
	}
}