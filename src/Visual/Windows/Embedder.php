<?php

namespace Webos\Visual\Windows;

use Webos\Visual\Window;
use Webos\Visual\Controls\TabFolder;

class Embedder extends Window {
	
	/**
	 *
	 * @var \Webos\Visual\Control\MultiTab;
	 */
	public $tabs = null;
	
	public function preInitialize():void {
		parent::preInitialize();
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
	
	public function addTab(string $title, array $params = []): TabFolder {
		return $this->tabs->addTab($title, $params);
	}
	
	public function tabWindow(string $title, string $windowClass, array $params = []): TabFolder {
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
		
		return $tab;
	}	
}