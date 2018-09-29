<?php

namespace Webos\Visual\BootstrapUI\Form;
use Webos\StringChar;
use Webos\Visual\Controls\Field;

class Control extends Field {
	protected $attrType = 'text';
	
	public function render(): string {
		$id = $this->getObjectID();
		$html = '<div class="form-group">';
		if ($this->label) {
			$html .= "<label for=\"{$id}\">{$this->label}</label>";
		}
		$html .= "<input type=\"{$this->attrType}\" class=\"form-control\" id=\"{$id}\" aria-describedby=\"{$id}-help\" placeholder=\"{$placeholder}\">";
		if ($this->label) {
			$html .= "<small id=\"{$id}-help\" class=\"form-text text-muted\">{$helpText}</small>";
		}
		$html .= "</div>";
		
		return $html;
	}
}
