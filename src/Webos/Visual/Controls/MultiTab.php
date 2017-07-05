<?php
namespace Webos\Visual\Controls;

class MultiTab extends \Webos\Visual\Control {

	protected $_activeTab;
	public function initialize() {
		$this->_activeTab = null;
	}

	public function  getInitialAttributes() {
		return array(
			'top'    => '5px',
			'bottom' => '5px',
			'left'   => '5px',
			'right'  => '5px',
		);
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
	public function createTab($title) {
		return $this->createObject(__NAMESPACE__ . '\TabFolder', array(
			'title' => $title,
		));
	}

	public function setActiveTab(TabFolder $tab) {
		$this->_activeTab = $tab;
		$this->modified();
	}
	
	public function render() {
		$attributes = $this->getAttributes();

		$activeTab = $this->getActiveTab();
		$tabs = array();

		$content = '<div class="Tabs">';

		foreach($this->getChildObjects() as $tab) {
			$tabHTML = new \Webos\String(
				'<a class="tab__SELECTED__" href="#" onclick="' .
				"__doAction('send', {actionName:'select', objectId:'" . $tab->getObjectID() ."'}); return false;" .
				'">' . $tab->title . '</a>'
			);

			if ($activeTab->getObjectID() == $tab->getObjectID()) {
				$tabHTML->replace('__SELECTED__', ' selected');
			} else {
				$tabHTML->replace('__SELECTED__', '');
			}

			$content .=$tabHTML;
		}

		$content .= '</div><div class="folder">';
		if ($activeTab) {
			$content .= $this->renderer->renderObject($activeTab);
		}
		$content .= '</div>';

		$html = new String(
			'<div class="MultiTab" id="__OBJECTID__" style="__STYLE__">__CONTENT__</div>'
		);

		return $html
			->replace('__OBJECTID__', $this->getObjectID())
			->replace('__STYLE__', $this->getAsStyles($attributes))
			->replace('__CONTENT__', $content);
	}

}