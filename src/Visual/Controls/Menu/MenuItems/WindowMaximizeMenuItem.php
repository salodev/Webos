<?php

class WindowMaximizeMenuItem extends MenuItem {

	public function  getInitialAttributes() {
		return array(
			'title' => 'Maximizar',
		);
	}

	public function click() {
		$this->getParentWindow()->status = 'maximized';
		$this->getParentByClassName('MenuButton')->close();
	}
}