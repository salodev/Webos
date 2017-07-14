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
	public function setSelectedNode(TreeNode $treeNode): self {
		$this->_selectedNode = $treeNode;
		return $this;
	}

	/**
	 * 
	 * @return TreeNode;
	 */
	public function getSelectedNode(): TreeNode {
		return $this->_selectedNode;
	}
	
	public function hasSelectedNode(): bool {
		return $this->_selectedNode instanceof TreeNode;
	}
	
	public function getAllowedActions(): array {
		return array();
	}
	
	public function getAvailableEvents(): array {
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
	public function addNode($text, $data = null): TreeNode {
		$newNode = $this->createObject(TreeNode::class, [
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
	public function removeNode(TreeNode $node): self {
		$parent = $node->getParent();
		$parent->removeChild($node);
		if ($parent instanceof TreeNode) {
			$parent->select();
		}
		return $this;
	}
	
	public function render(): string {
		$html = new \Webos\StringChar(
			'<ul id="__id__" class="Tree container"__style__ >' .
				'__content__' . 
			'</ul>'
		);

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
	public function addColumn(string $fieldName, string $label, int $width=100, bool $allowOrder=false, bool $linkable=false, string $align = 'left'): Column {
		// $column = new ColumnDataTable();
		$column = new Column($label, $fieldName);
		$column->width      = $width;
		$column->allowOrder = $allowOrder;
		$column->linkable   = $linkable;
		$column->align      = $align;
		$this->columns->add($column);
		return $column;
	}
	
	public function onNodeToggled(callable $fn) {
		$this->bind('nodeToggled', $fn);
		return $this;
	}
	
	public function onNodeSelected(callable $fn) {
		$this->bind('nodeSelected', $fn);
		return $this;
	}
}