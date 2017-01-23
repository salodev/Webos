<?php
namespace Webos\Visual\Controls;

class TreeNode extends \Webos\Visual\Control {

	private $_selectedNode = null;

	public function setSelectedNode($nodeTree) {
		$this->_selectedNode = $nodeTree;
	}

	public function getSelectedNode() {
		return $this->_selectedNode;
	}

	public function __get_selected() {
		$test = $this->getParent()->getSelectedNode();
		if ($test instanceof TreeNode) {
			if ($test->getObjectID() == $this->getObjectID()) {
				return true;
			}
		}

		return false;
	}

	public function __set_selected(TreeNode $nodeTree) {
		$this->getParent()->setSelectedNode($nodeTree);
	}

	public function getAllowedActions() {
		return array(
			'expand',
			'collapse',
			'select',
		);
	}
	
	public function getAvailableEvents() {
		return array();
	}
}