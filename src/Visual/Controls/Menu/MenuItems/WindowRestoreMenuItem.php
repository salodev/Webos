<?php

class WindowRestoreMenuItem extends MenuItem {

	public function  getInitialAttributes() {
		return array(
			'title' => 'Restaurar',
		);
	}

	public function click() {
		$this->getParentWindow()->status = 'normal';
		$this->getParentByClassName('MenuButton')->close();
	}
}