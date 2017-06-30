<?php
namespace Webos\Visual\Controls;

class TreeNode extends \Webos\Visual\Control {
	
	/**
	 * 
	 * @param mixed $title
	 * @param mixed $value
	 * @return TreeNode
	 */
	public function addNode($text, $value = null) {
		return $this->createObject('\Webos\Visual\Controls\TreeNode', [
			'treeControl' => $this->treeControl,
			'text'  => $text,
			'value' => $value,
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
	
	public function toggle() {
		$expanded = $this->expanded;
		if ($expanded) {
			$this->expanded = false;
		} else {
			$this->expanded = true;
		}
		$this->treeControl->triggerEvent('nodeToggled',['node'=>$this]);
		$this->select();
	}
	
	public function click() {
		$this->treeControl->setSelectedNode($this);
	}
	
	public function select() {
		$this->treeControl->setSelectedNode($this);
	}
	
	public function getAvailableEvents() {
		return array();
	}
	
	public function render() {

		$html = new \Webos\String(
			'<li id="__id__" class="NodeTree__selected__"__style__ onclick="__onclick__">' .
				'<div style="min-width:600px;">' . 
					'<span class="toggle __toggleClass__" onclick="__onclickToggle__"></span>' .
					'__text__'.
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
			foreach($this->getChildObjects() as $child) {
				$content .= $child->render();
			}
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
		))->replace('__id__', $this->getObjectID());

		return $html;
	}
}