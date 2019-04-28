<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Webos\Visual\Windows;

use Webos\Visual\Window;

/**
 * Description of DataForm
 *
 * @author salomon
 */
abstract class DataForm extends Window {
	
	public function afterInitialize() {		
		$this->createWindowButton('Save')->onClick(function() {
			$data = $this->getFormData();
			$returnData = $this->saveData($data);
			$this->newData($returnData);
			$message = $this->messageOnSave();
			if ($message) {
				$this->messageWindow($message)->onClose(function() {
					if ($this->closeOnSave()) {
						$this->close();
					}
				});
			} else {
				if ($this->closeOnSave()) {
					$this->close();
				}
			}
		});
		$this->createWindowButton('Close')->closeWindow();
		$this->onReady(function() {
			$data = $this->getData();
			$this->setFormData($data);
		});
	}
	
	abstract public function getData(): array;
	
	abstract public function saveData(array $data = []): array;
	
	abstract public function closeOnSave(): bool;
	
	abstract public function messageOnSave(): string;

}
