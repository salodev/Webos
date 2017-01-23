<?php

class WindowRestoreMenuItem extends MenuItem {

	public function  getInitialAttributes() {
		return array(
			'title' => 'Restaurar',
		);
	}

	public function press() {
		$this->getParentWindow()->status = 'normal';
		$this->getParentByClassName('MenuButton')->close();
	}
}