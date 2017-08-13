<?php
namespace Webos\Visual\Controls;

class TreeNode extends \Webos\Visual\Control {
	
	/**
	 * 
	 * @param mixed $title
	 * @param mixed $value
	 * @return TreeNode
	 */
	public function addNode(string $text, array $data = []): TreeNode {
		return $this->createObject(TreeNode::class, [
			'treeControl' => $this->treeControl,
			'text'        => $text,
			'data'        => $data,
		]);
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
		throw new \Exception('Read only property');
	}

	public function getAllowedActions(): array {
		return array(
			'expand',
			'collapse',
			'toggle',
			'select',
			'click',
			'contextMenu',
		);
	}
	
	/**
	 * 
	 * @return $this
	 */
	public function toggle() {
		$expanded = $this->expanded;
		if ($expanded) {
			$this->expanded = false;
		} else {
			$this->expanded = true;
		}
		$this->treeControl->triggerEvent('nodeToggled',['node'=>$this]);
		$this->select();
		return $this;
	}
	
	/**
	 * 
	 * @return $this
	 */
	public function click() {
		$this->treeControl->setSelectedNode($this);
		$this->treeControl->triggerEvent('nodeSelected',['node'=>$this]);
		return $this;
	}
	
	/**
	 * 
	 * @return $this
	 */
	public function select() {
		$this->treeControl->setSelectedNode($this);
		$this->treeControl->triggerEvent('nodeSelected',['node'=>$this]);
		return $this;
	}
	
	public function contextMenu($params) {
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
	
	public function getAvailableEvents(): array {
		return ['contextMenu'];
	}
	
	public function render(): string {

		$html = new \Webos\StringChar(
			'<li id="__id__" class="TreeNode"__style__ onclick="__onclick__" webos contextmenu>' .
				'<div class="row __selected__">' . 
					// ($this->hasChilds===false ? '' :
					'<span class="toggle __toggleClass__" onclick="__onclickToggle__"></span>' .
					// ).
					'<div class="title">__text__</div>'.
					'__columns__' .
				'</div>'.
				'<ul class="container">__content__</ul>'.
			'</li>'
		);
		$onclick = "__doAction('send',{actionName:'select',objectId:this.id});event.cancelBubble=true;event.stopPropagation();";
		$ondblclick = "__doAction('send',{actionName:'doubleclick',objectId:this.id});event.cancelBubble=true;event.stopPropagation();";

		$onclickToggle = "__doAction('send',{actionName:'toggle',objectId:'__id__'});event.cancelBubble=true;event.stopPropagation();";
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

		$html->replaces(array(
			'__id__'            => $this->getObjectID(),
			'__style__'         => $this->getInlineStyle(true),
			'__toggleClass__'   => $toggleClass,
			'__selected__'      => $this->selected ? " selected" : '',
			'__text__'          => $this->text,
			'__content__'       => $content,
			'__onclick__'       => $onclick,
			'__ondblclick__'    => $ondblclick,
			'__onclickToggle__' => $onclickToggle,
			'__columns__'       => $this->_renderColumns(),
		))->replace('__id__', $this->getObjectID());

		return $html;
	}
	
	private function _renderColumns() {
		$html = '';
		$width = 0;
		foreach($this->treeControl->columns as $column) {
			$width += $column->width/1;
			$data = $this->data;
			$value = \salodev\Utils::Ifnull($data[$column->fieldName], '&nbsp;');
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