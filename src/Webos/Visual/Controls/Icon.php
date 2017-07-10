<?php

namespace Webos\Visual\Controls;

class Icon extends \Webos\Visual\Control {
	public function initialize(array $params = array()) {
		$icon = $this->icon;
		if (empty($icon)) {
			throw new \Exception('Missing \'icon\' parameter for Icon Object');
		}
	}
	
	public function render(): string {
		$html = new \Webos\StringChar('<div class="__class__ __icon__"></div>');
		$html->replaces([
			'__class__' => \Webos\StringChar(self::class)->replace('\\',' '),
			'__icon__'  => $this->icon,
		]);
		return $html;
	}
}