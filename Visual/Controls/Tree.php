<?php
namespace Webos\Visual\Controls;

class Tree extends \Webos\Visual\Control {

	// private $_selectedNode = null;

	public function setSelectedNode(TreeNode $treeNode) {
		$this->_selectedNode = $treeNode;
	}

	public function getSelectedNode() {
		return $this->_selectedNode;
	}

	public function initialize() {}
	
	public function getAllowedActions() {
		return array();
	}
	
	public function getAvailableEvents() {
		return array();
	}
	/**
	 * 
	 * @param mixed $title
	 * @param mixed $value
	 * @return TreeNode
	 */
	public function addNode($text, $value = null) {
		return $this->createObject('\Webos\Visual\Controls\TreeNode', [
			'treeControl' => $this,
			'text'  => $text,
			'value' => $value,
		]);
	}
	
	public function render() {
		$html = new \Webos\String('<ul id="__id__" class="TreeControl"__style__ >__content__</ul>');
		$onchange = "__doAction('send',{actionName:'setValue',objectId:this.id, value:this.value});";

		$content = '';
		foreach($this->getChildObjects() as $child) {
			$content .= $child->render();
		}

		$html->replaces(array(
			'__id__'      => $this->getObjectID(),
			'__style__'   => $this->getInlineStyle(true),
			'__content__' => $content,
		));

		return $html;
	}
}