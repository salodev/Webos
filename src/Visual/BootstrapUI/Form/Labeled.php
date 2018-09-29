<?php

namespace Webos\Visual\BootstrapUI\Form;
use Webos\StringChar;

abstract class Labeled extends Control {
	
	public function render(): string {
		$id = $this->getObjectID();
		$html = '<div class="form-group" id ="{$id}">';
		if ($this->label) {
			$html .= "<label for=\"control-{$id}\">{$this->label}</label>";
		}
		$html .= $this->getTemplate();
		if ($this->helpText) {
			$html .= "<small id=\"{$id}-help\" class=\"form-text text-muted\">{$this->helpText}</small>";
		}
		$html .= "</div>";
		
		return $html;
	}
	
	abstract public function getTemplate(): string;
	
	public function setLabel(string $text): self {
		$this->label = $text;
		return $this;
	}
}
