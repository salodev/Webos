<?php
namespace Webos\Visual\Controls;

class Tree extends \Webos\Visual\Control {

	// private $_selectedNode = null;

	/**
	 * 
	 * @param \Webos\Visual\Controls\TreeNode $treeNode
	 * @return $this
	 */
	public function setSelectedNode(TreeNode $treeNode) {
		$this->_selectedNode = $treeNode;
		return $this;
	}

	/**
	 * 
	 * @return TreeNode;
	 */
	public function getSelectedNode() {
		return $this->_selectedNode;
	}

	public function initialize() {}
	
	public function getAllowedActions() {
		return array();
	}
	
	public function getAvailableEvents() {
		return [
			'nodeToggled',
			'nodeSelected',
		];
	}
	/**
	 * 
	 * @param mixed $title
	 * @param mixed $value
	 * @return TreeNode
	 */
	public function addNode($text, $data = null) {
		$newNode = $this->createObject('\Webos\Visual\Controls\TreeNode', [
			'treeControl' => $this,
			'text'        => $text,
			'data'        => $data,
		]);
		$newNode->select();
		return $newNode;
	}
	
	/**
	 * 
	 * @param \Webos\Visual\Controls\TreeNode $node
	 * @return $this
	 */
	public function removeNode(TreeNode $node) {
		$parent = $node->getParent();
		$parent->removeChild($node);
		if ($parent instanceof TreeNode) {
			$parent->select();
		}
		return $this;
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