<?php
namespace Webos\Visual\Controls\Menu;
use Webos\StringChar;

class ControlItem extends Item {
	
	public function render(): string {
		$selected = '';

		if ($this->selected) {
			$selected = ' selected';
		}


		$html = new StringChar(
			'<tr id="__id__" class="MenuItem__selected__"__disabled__ webos click>' .
				'<td class="icon__icon_class__"></td>' .
				'<td class="text">__content__</td>' .
				'<td class="arrow"></td>' .
			'</tr>'
		);
		$html->replaces(array(
			'__id__'         => $this->getObjectID(),
			'__selected__'   => $selected,
			'__disabled__'   => $this->disabled ? 'disabled="disabled"' : '',
			'__icon_class__' => '',
			'__text__'       => $this->text,
			'__content__'    => $this->getChildObjects()->render(),
		));
		
		return $html;
	}

}