<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Webos\Visual\Controls;

use Webos\Visual\Control;
use Webos\StringChar;

/**
 * Description of Link
 *
 * @author salomon
 */
class Link extends Control {
	//put your code here
	
	public function getRequiredParams(): array {
		return array_merge(parent::getRequiredParams(), ['url']);
	}
	
	public function render(): string {
		return (new StringChar("<a href=\"{$this->url}\" __STYLE__ >{$this->text}</a>"))
			->replaces([
				'__STYLE__' => $this->getInlineStyle(true),
			]);
	}
}
