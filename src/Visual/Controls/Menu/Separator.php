<?php
namespace Webos\Visual\Controls\Menu;

use Webos\Visual\Control;

class Separator extends Control {

	public function render(): string {
		return '<tr class="SeparatorMenuItem"><td colspan="3"><hr /></td></tr>';
	}

}