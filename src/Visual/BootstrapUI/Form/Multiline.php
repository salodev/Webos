<?php

namespace Webos\Visual\BootstrapUI\Form;

class Multiline extends Text {
	public function getTemplate() {
		return "<textarea class=\"form-control\" id=\"control-{$this->getObjectID()}\" rows=\"3\">{$this->value}</textarea>";
	}
}
