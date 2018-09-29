<?php

namespace Webos\Visual\BootstrapUI\Form;
use Webos\Visual\Container;

class Form extends Control {
	public function render(): string {
		return "<form id=\"{$this->getObjectID()}\" class=\"container-fluid\">" . $this->getChildObjects()->render() . '</form>';
	}
}
