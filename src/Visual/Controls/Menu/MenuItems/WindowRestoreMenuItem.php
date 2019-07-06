<?php

class WindowRestoreMenuItem extends MenuItem {

	public function  getInitialAttributes() {
		return [
			'title' => 'Restaurar',
		];
	}

	public function click() {
		$this->getParentWindow()->status = 'normal';
		$this->getParentByClassName('MenuButton')->close();
	}
}