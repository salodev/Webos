<?php
namespace Webos\Visual\Controls;
use \Exception;
use \Webos\Visual\Controls\DataTable\Column;

class Tree extends \Webos\Visual\Control {

	// private $_selectedNode = null;
	
	public function initialize() {
		$this->columns = new \Webos\Collection();
	}

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
		// $this->lastchange = microtime(true);
		// $newNode->select();
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
		$html = new \Webos\String('<ul id="__id__" class="Tree"__style__ >__content__</ul>');
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
	
	/**
	 * 
	 * @param string $fieldName
	 * @param string $label
	 * @param string $width
	 * @param bool   $allowOrder
	 * @param bool   $linkable
	 * @param string $align
	 * @return Column
	 */
	public function addColumn($fieldName, $label, $width='100px', $allowOrder=false, $linkable=false, $align = 'left') {
		// $column = new ColumnDataTable();
		$column = new Column($label, $fieldName);
		$column->width      = $width;
		$column->allowOrder = $allowOrder;
		$column->linkable   = $linkable;
		$column->align      = $align;
		$this->columns->add($column);
		return $column;
	}
}