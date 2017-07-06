<?php
namespace Webos\Visual\Controls;

class TreeNode extends \Webos\Visual\Control {
	
	/**
	 * 
	 * @param mixed $title
	 * @param mixed $value
	 * @return TreeNode
	 */
	public function addNode($text, $data = null) {
		return $this->createObject('\Webos\Visual\Controls\TreeNode', [
			'treeControl' => $this->treeControl,
			'text'        => $text,
			'data'        => $data,
		]);
	}

	public function __get_selected() {
		$test = $this->treeControl->getSelectedNode();
		if ($test===$this) {
			return true;
		}

		return false;
	}

	public function __set_selected(TreeNode $nodeTree) {
		throw new \Exception('Read only property');
	}

	public function getAllowedActions() {
		return array(
			'expand',
			'collapse',
			'toggle',
			'select',
			'click',
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
	
	public function getAvailableEvents() {
		return array();
	}
	
	public function render() {

		$html = new \Webos\String(
			'<li id="__id__" class="TreeNode"__style__ onclick="__onclick__">' .
				'<div class="row __selected__">' . 
					'<span class="toggle __toggleClass__" onclick="__onclickToggle__"></span>' .
					'<div class="title">__text__</div>'.
					'__columns__' .
				'</div>'.
				'<ul>__content__</ul>'.
			'</li>'
		);
		$onclick = "__doAction('send',{actionName:'select',objectId:this.id});event.cancelBubble=true;event.stopPropagation();";
		$ondblclick = "__doAction('send',{actionName:'doubleclick',objectId:this.id});event.cancelBubble=true;event.stopPropagation();";

		$onclickToggle = "__doAction('send',{actionName:'toggle',objectId:'__id__'});event.cancelBubble=true;event.stopPropagation();";
		
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
		foreach($this->treeControl->columns as $column) {
			$data = $this->data;
			$value = &$data[$column->fieldName];
			$styles = $this->getAsStyles([
				'width' => $column->width,
				'text-align' => $column->align,
			]);
			$html .= '<div class="column" style="'.$styles.'">' . $value . '</div>';
		}
		return $html;
	}
}