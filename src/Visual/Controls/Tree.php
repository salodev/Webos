<?php
namespace Webos\Visual\Controls;

use Webos\Visual\Controls\DataTable\Column;
use Webos\Visual\Control;
use Webos\Visual\DataConsuming;
use Webos\Collection;
use Webos\StringChar;

class Tree extends Control {
	
	use DataConsuming;

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
	
	public function getAllowedActions(): array {
		return array('scroll');
	}
	
	public function getAvailableEvents(): array {
		return [
			'nodeToggled',
			'nodeSelected',
			'contextMenu',
			'scroll',
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
	
	public function setData(array $data, $columnForLabel = 'title'): self {
		foreach($data as $row) {
			$text = $row[$columnForLabel]??'';
			$this->addNode($text, $data);
		}
		return $this;
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
	 * @breakinChange: No parameter.
	 * 
	 * @param string $fieldName
	 * @param string $label
	 * @param string $width
	 * @param bool   $allowOrder
	 * @param bool   $linkable
	 * @param string $align
	 * @return Column
	 */
	public function addColumn($label = '', $fieldName = ''): Column {
		$column = new Column($label, $fieldName);
		$this->columns->add($column);
		return $column;
	}
	
	public function onNodeToggled(callable $fn): self {
		$this->bind('nodeToggled', $fn);
		return $this;
	}
	
	public function onNodeSelected(callable $fn): self {
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