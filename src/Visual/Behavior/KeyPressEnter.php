<?php

namespace Webos\Visual\Behavior;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of KeyPressEnter
 *
 * @author salomon
 */
trait KeyPressEnter {
	//put your code here
	
	public function action_keyPressEnter(array $params = []) {
		$this->triggerEvent('keyPressEnter');
	}
	
	public function onKeyPressEnter(callable $cb, bool $persistent = true, array $contextData = []): self {
		$this->bind('keyPressEnter', $cb, $persistent, $contextData);
		return $this;
	}
}
