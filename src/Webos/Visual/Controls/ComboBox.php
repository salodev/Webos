<?php
namespace Webos\Visual\Controls;
class ComboBox extends Field {
	public function initialize() {
		if (!is_array($this->options)) {
			$this->options = array();
		}
	}

	public function __get_value() {
		if (isset($this->_attributes['value'])) {
			return $this->_attributes['value'];
		}
		
		$options = &$this->_attributes['options'];
		if (isset($options) && is_array($options)) {
			foreach($options as $key => $text) {
				$firstValue = $this->assoc ? $key : $text;
				$this->_attributes['value'] = $firstValue;
				return $firstValue;
			}
		}
		return null;
	}
	
	public function setRS(array $rs, string $keyID = 'id', string $keyText = 'text'): self {
		$options = array();
		foreach($rs as $row) {
			$id = $row[$keyID];
			$text = $row[$keyText];
			$options[$id] = $text;
		}
		$this->options = $options;
		$this->assoc = true;
		if (count($rs)) {
			$this->value = $rs[0][$keyID];
		}
		return $this;
	}
	
	public function render(): string {
		$onchange = "__doAction('send',{actionName:'setValue',objectId:this.id, value:this.value});";
		$disabled = $this->disabled ? ' disabled="disabled"' : '';
		$html = '<select id="' . $this->getObjectID() . '" class="ComboFieldControl" onclick="onclick" onchange="'.$onchange.'" '.$this->getInlineStyle(). $disabled . '>';
		$assoc = $this->assoc;
		foreach($this->options as $key => $option) {
			$selected = '';
			$value = $assoc ? $key : $option;
			if ($this->value==$value) {
				$selected = ' selected="selected"';
			}
			$htmlValue = $assoc ? " value=\"{$value}\"": '';
			$html .= "<option{$selected}{$htmlValue}>{$option}</option>";
		}

		$html .='</select>';
		return $html;
	}
}