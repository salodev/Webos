<?php
namespace Webos\Visual\Controls;

use Webos\Visual\Control;
use Webos\StringChar;
use Exception;

class MultiTab extends Control {

	protected $_activeTab;
	public function initialize() {
		$this->_activeTab = null;
	}
	
	public function select(array $params = []) {
		if (!isset($params['index'])) {
			throw new Exception('Missing index param');
		}
		$this->setActiveTab($this->getChildObjects()->item($params['index']));
	}

	/**
	 * 
	 * @return TabFolder|null;
	 */
	public function getActiveTab() {
		if (!$this->_childObjects->count()) return null;

		if (!($this->_activeTab instanceof TabFolder)) {
			$this->_activeTab = $this->_childObjects->getFirstObject();
		}

		return $this->_activeTab;
	}
	
	/**
	 * 
	 * @param type $title
	 * @return TabFolder
	 */
	public function addTab($title): TabFolder {
		return $this->createObject(TabFolder::class, array(
			'title' => $title,
		));
	}

	public function setActiveTab(TabFolder $tab) {
		$this->_activeTab = $tab;
		$this->modified();
	}
	
	public function getAllowedActions(): array {
		return array_merge(parent::getAllowedActions(), ['select']);
	}
	
	public function render(): string {
		$attributes = $this->getAttributes();

		$activeTab = $this->getActiveTab();
		$tabs = array();

		$content = '<div class="Tabs">';

		foreach($this->getChildObjects() as $index => $tab) {
			$tabHTML = new StringChar(
				'<a class="tab__SELECTED__" href="#" webos action="select" data-index="' . $index . '">' . $tab->title . '</a>'
			);

			if ($activeTab === $tab) {
				$tabHTML->replace('__SELECTED__', ' selected');
			} else {
				$tabHTML->replace('__SELECTED__', '');
			}

			$content .=$tabHTML;
		}

		$content .= '</div><div class="folder">';
		if ($activeTab) {
			$content .= $activeTab->render();
		}
		$content .= '</div>';

		$html = new StringChar(
			'<div class="MultiTab" id="__OBJECTID__" style="__STYLE__">__CONTENT__</div>'
		);

		return $html
			->replace('__OBJECTID__', $this->getObjectID())
			->replace('__STYLE__', $this->getAsStyles($attributes))
			->replace('__CONTENT__', $content);
	}

}