<?php

namespace Webos\Visual\Controls;

use Webos\Visual\Control;
use Exception;
use Webos\StringChar;

class Icon extends Control {
	public function initialize(array $params = array()) {
		$icon = $this->icon;
		if (empty($icon)) {
			throw new Exception('Missing \'icon\' parameter for Icon Object');
		}
	}
	
	public function render(): string {
		$html = new StringChar('<div class="__class__ __icon__"></div>');
		$html->replaces([
			'__class__' => StringChar(self::class)->replace('\\',' '),
			'__icon__'  => $this->icon,
		]);
		return $html;
	}
}