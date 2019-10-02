<?php
namespace Webos\Visual\Controls;

use Webos\Visual\Control;
use Webos\Visual\DataConsuming;
use Webos\StringChar;
use Exception;

class TreeNode extends Control {
	
	use DataConsuming;
	
	/**
	 * 
	 * @param mixed $title
	 * @param mixed $value
	 * @return TreeNode
	 */
	public function addNode(string $text, array $data = []): self {
		return $this->createObject(TreeNode::class, [
			'treeControl' => $this->treeControl,
			'text'        => $text,
			'data'        => $data,
		]);
	}
	
	public function setData(array $data, $columnForLabel = 'title'): self {
		foreach($data as $row) {
			$text = $row[$columnForLabel]??'';
			$this->addNode($text, $data);
		}
		return $this;
	}
	
	public function addData(array $data = []) {
		throw new Exception('Not allowed');
	}

	public function __get_selected() {
		if (!$this->treeControl->hasSelectedNode()) {
			return false;
		}
		$test = $this->treeControl->getSelectedNode();
		if ($test===$this) {
			return true;
		}

		return false;
	}

	public function __set_selected(TreeNode $nodeTree) {
		throw new Exception('Read only property');
	}
	
	/**
	 * 
	 * @return $this
	 */
	public function action_toggle(): void {
		$expanded = $this->expanded;
		if ($expanded) {
			$this->expanded = false;
		} else {
			$this->expanded = true;
			$this->triggerEvent('expanded', ['node' => $this]);
		}
		$this->treeControl->triggerEvent('nodeToggled',['node'=>$this]);
		$this->select();
	}
	
	/**
	 * 
	 * @return $this
	 */
	public function action_click(): void {
		$this->treeControl->setSelectedNode($this);
		$this->treeControl->triggerEvent('nodeSelected',['node'=>$this]);
		$this->triggerEvent('click', ['node' => $this]);
	}
	
	/**
	 * 
	 * @return $this
	 */
	public function select(): self {
		$this->treeControl->setSelectedNode($this);
		$this->treeControl->triggerEvent('nodeSelected',['node'=>$this]);
		$this->triggerEvent('click', ['node' => $this]);
		return $this;
	}
	
	public function action_select(): void {
		$this->select();
	}
	
	public function action_contextMenu(array $params = []): void {
		if (empty($params['top']) || empty($params['left'])) {
			return;
		}
		if ($this->treeControl->hasListenerFor('contextMenu')) {
			$this->treeControl->setSelectedNode($this);
			$menu = $this->getParentWindow()->createContextMenu($params['top'], $params['left']);
			$this->treeControl->triggerEvent('contextMenu', [
				'menu' => $menu,
				'node' => $this
			]);
		}
	}
	
	public function onContextMenu(callable $cb, bool $persistent = true, array $contextData = []): self {
		$this->bind('contextMenu', $cb, $persistent, $contextData);
		return $this;
	}
	
	public function onExpand(callable $cb, $persistent = true): self {
		$this->bind('expanded', $cb, $persistent);
		return $this;
	}
	
	public function render(): string {

		$html = new StringChar(
			'<li id="__id__" class="TreeNode"__style__ webos contextmenu click="select" stop-propagation>' .
				'<div class="row __selected__">' . 
					// ($this->hasChilds===false ? '' :
					'<span class="toggle __toggleClass__" __onclickToggle__></span>' .
					// ).
					'<div class="title no-break">__text__</div>'.
					'__columns__' .
				'</div>'.
				'<ul class="container">__content__</ul>'.
			'</li>'
		);

		$onclickToggle = 'webos click="toggle"';
		if ($this->hasChilds === false) {
			$onclickToggle = '';
		}
		
		if ($this->hasChild !== false) {
			
		}
		$content  = '';
		$expand   = " + ";
		$collapse = " - ";
		if ($this->expanded) {
			$content = $this->getChildObjects()->render();
		}
		
		$toggleClass = 'none';
		// if ($this->getChildObjects()->length()) {
			$toggleClass = $this->expanded ? 'collapse' : 'expand';
		// }
			
		if ($this->hasChilds === false) {
			$toggleClass = 'none';
		}

		$html->replaces([
			'__id__'            => $this->getObjectID(),
			'__style__'         => $this->getInlineStyle(true),
			'__toggleClass__'   => $toggleClass,
			'__selected__'      => $this->selected ? " selected" : '',
			'__text__'          => $this->text,
			'__content__'       => $content,
			'__onclickToggle__' => $onclickToggle,
			'__columns__'       => $this->_renderColumns(),
		])->replace('__id__', $this->getObjectID());

		return $html;
	}
	
	private function _renderColumns(): string {
		$html = '';
		$width = 0;
		foreach($this->treeControl->columns as $column) {
			$width += $column->width/1;
			$data = $this->data;
			$value = $data[$column->fieldName] ?? '&nbsp;';
			$styles = $this->getAsStyles([
				'width' => $column->width,
				'text-align' => $column->align,
			]);
			$html .= '<div class="column" style="'.$styles.'">' . $value . '</div>';
		}
		if ($width) {
			$html = '<div class="columns" style="width:'.$width.'px">' . $html . '</div>';
		}
		return $html;
	}
}