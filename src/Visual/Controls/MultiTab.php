<?php
namespace Webos\Visual\Controls;

use Webos\Visual\Control;
use Webos\StringChar;
use Exception;

class MultiTab extends Control {

	protected $_activeTab;
	public function initialize(array $params = []) {
		$this->_activeTab = null;
	}
	
	public function action_select(array $params = []): void {
		if (!isset($params['index'])) {
			throw new Exception('Missing index param');
		}
		$this->setActiveTab($this->getChildObjects()->item($params['index']));
		$this->modified();
	}
	
	public function action_close(array $params = []): void {
		if (!isset($params['index'])) {
			throw new Exception('Missing index param');
		}
		$childs = $this->getChildObjects();
		$index = $params['index'];
		
		$child = $childs->item($index);
		
		$this->triggerEvent('close', [
			'tab' => $child,
		]);
		
		$child->getParent()->removeChild($child);
		
		$maxIndex = $childs->count()-1;
		if ($params>$maxIndex) {
			$index = $maxIndex;
		}
		if ($index<0) {
			$this->_activeTab = null;
			$this->modified();
			return;
		}

		$this->setActiveTab($this->getChildObjects()->item($index));
		return;
	}
	
	public function onClose(callable $function): self {
		$this->bind('close', $function);
		return $this;
	}


	/**
	 * 
	 * @return bool;
	 */
	public function hasActiveTab(): bool {
		return $this->_activeTab instanceof TabFolder && 
				$this->_childObjects->count() > 0;
	}


	/**
	 * 
	 * @return TabFolder|null;
	 */
	public function getActiveTab(): TabFolder {
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
	public function addTab($title, array $params = []): TabFolder {
		$params['title'] = $title;
		$tab = $this->createObject(TabFolder::class, $params);
		if ($this->getChildObjects()->count()==1) {
			$this->setActiveTab($tab);
		}
		$this->modified();
		return $tab;
	}
	
	public function addClosableTab($title, array $params = []): TabFolder {
		$params['closable'] = true;
		return $this->addTab($title, $params);
	}

	public function setActiveTab(TabFolder $tab): self {
		$this->_activeTab = $tab;
		$tab->triggerEvent('select');
		$this->modified();
		return $this;
	}
	
	public function render(): string {
		$attributes = $this->getAttributes();

		$activeTab = null;
		if ($this->hasActiveTab()) {
			$activeTab = $this->getActiveTab();
		}
	

		$content = '<div class="Tabs">';

		foreach($this->getChildObjects() as $index => $tab) {
			$closeHTML = '';
			if ($tab->closable) {
				$closeHTML = "<span class=\"close\" webos action=\"close\" data-index=\"{$index}\" />";
			}
			$tabHTML = new StringChar("
				<a class=\"tab__SELECTED__\" 
					href=\"#\" webos action=\"select\" 
					data-index=\"{$index}\"
				>
					<span>{$tab->title}</span>
					{$closeHTML}
				</a>
				
			");

			if ($activeTab === $tab) {
				$tabHTML->replace('__SELECTED__', ' selected');
			} else {
				$tabHTML->replace('__SELECTED__', '');
			}

			$content .=$tabHTML;
		}

		$content .= '</div><div class="folder container">';
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