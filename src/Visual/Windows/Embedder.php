<?php

namespace Webos\Visual\Windows;
use Webos\Visual\Window;

class Embedder extends Window {
	
	public $tabs = null;
	
	public function initialize(array $params = []) {
		$this->tabs = $this->createTabsFolder();
		
		$this->onNewData(function() {
			$collection = $this->tabs->getChildObjects();
			foreach($collection as $object) {
				if (!$object->isActive()) {
					$object->removeChilds();
					$object->_initialized = false;
				}
			}
		});
	}
	
	public function tabWindow(string $title, string $windowClass, array $params = []) {
		$tab = $this->tabs->addTab($title);
		$tab->onEmbedded(function($data) {
			$data['window']->onNewData(function() {
				$this->newData();
			});
			$data['window']->onClose(function() {
				$this->close();
			});
		});
		$tab->embedWindowOnSelect($windowClass, $params);
	}	
}