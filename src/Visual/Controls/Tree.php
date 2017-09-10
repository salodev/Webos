<?php
namespace Webos\Visual\Controls;

use Webos\Visual\Controls\DataTable\Column;
use Webos\Visual\Control;
use Webos\Collection;
use Webos\StringChar;

class Tree extends Control {

	// private $_selectedNode = null;
	
	public function initialize() {
		$this->columns = new Collection();
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
	
	public function scroll(array $params = array()) {
		//echo "hola";
		$this->scrollTop  = $params['top' ] ?? 0;
		$this->scrollLeft = $params['left'] ?? 0;
	}
	
	public function getAllowedActions(): array {
		return array('scroll');
	}
	
	public function getAvailableEvents(): array {
		return [
			'nodeToggled',
			'nodeSelected',
			'contextMenu',
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
	
	public function render(): string {
		$scrollTop  = $this->scrollTop  ?? 0;
		$scrollLeft = $this->scrollLeft ?? 0;
		$html = new StringChar(
			'<ul id="__id__" '.
				'class="Tree container" '.
				'__style__ ' .
				"webos set-scroll-values=\"{$scrollTop},{$scrollLeft}\" " .
			'>' .
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
}