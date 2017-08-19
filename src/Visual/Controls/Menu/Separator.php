<?php
namespace Webos\Visual\Controls\Menu;
class Separator extends \Webos\Visual\Control {

	public function render(): string {
		return '<tr class="SeparatorMenuItem"><td colspan="3"><hr /></td></tr>';
	}

}