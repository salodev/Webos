<?php
namespace Webos\Visual\Controls;
use \Exception;
class DataTable extends \Webos\Visual\Control {
	public $rowIndex = null;
	public function initialize() {
		$this->rows = array();
		$this->columns = new \Webos\Collection();
		$this->rowIndex = null;
		$this->columInded = null;
	}

	public function getInitialAttributes() {
		return array(
			'top'        => 0,
			'bottom'     => 0,
			'left'       => 0,
			'right'      => 0,
			'scrollTop'  => 0,
			'scrollLeft' => 0,
		);
	}

	public function addColumn($fieldName, $label, $width='100px', $allowOrder=false, $linkable=false, $align = 'left') {
		// $column = new ColumnDataTable();
		$column = new \stdClass();
		$column->label      = $label;
		$column->width      = $width;
		$column->fieldName  = $fieldName;
		$column->allowOrder = $allowOrder;
		$column->linkable   = $linkable;
		$column->align      = $align;
		$this->columns->add($column);
		$this->bodyWidth += str_replace('px','', $width+8)/1;
		return $this;
	}

	public function getActiveRowData($fieldName = null) {
		if ($this->rowIndex !== null) {
			$rowData = $this->getRowData($this->rowIndex/1);
			if ($fieldName) {
				if (!array_key_exists($fieldName, $rowData)) {
					throw new \Exception("The '{$fieldName}' field does not exist.");
				}
				return $rowData[$fieldName];
			}
			return $rowData;
		}
		return null;
	}

	public function getRowData($rowIndex) {
		$i = 0;
		foreach($this->rows as $row) {
			if ($i==$rowIndex) {
				return $row;
			}
			$i++;
		}
		throw new Exception('Requested row does not exists');
	}

	public function rowClick(array $params = array()) {
		if (!isset($params['row'])) {
			throw new Exception('The \'rowClick\' event needs a \'row\' parameter');
		}
		if (!isset($params['fieldName'])) {
			throw new Exception('The \'rowClick\' event needs a \'fieldName\' parameter');
		}
		if ($this->rowIndex !== null && $this->rowIndex == $params['row']) {
			// si clickea en una seleccionada, deselecciona
			$this->rowIndex = null;
		} else {
			// sino, selecciona.
			$this->rowIndex = $params['row'];
		}

		$row       = $params['row'];
		$fieldName = $params['fieldName'];
		$rowData   = $this->getRowData($row);
		$cellValue = $rowData[$fieldName];
		
		$this->triggerEvent('rowClick', array(
			'row'       => $row,
			'fieldName' => $fieldName,
			'rowData'   => $rowData,
			'cellValue' => $cellValue,
		));
	}

	public function rowDoubleClick(array $params = array()) {
		if (!isset($params['row'])) {
			throw new Exception('The \'rowDoubleClick\' event needs a \'row\' parameter');
		}
		$this->triggerEvent('rowDoubleClick', array('row'=>$params['row']));
	}

	public function scroll(array $params = array()) {
		//echo "hola";
		$this->scrollTop  = ifempty($params['top'], 0);
		$this->scrollLeft = ifempty($params['left'], 0);
	}

	public function getAllowedActions() {
		return array(
			'rowClick',
			'rowDoubleClick',
			'scroll'
		);
	}

	public function getAvailableEvents() {
		return array(
			'rowClick',
			'rowDoubleClick',
		);
	}
	
	public function render() {
		$objectID   = $this->getObjectID();

		$scrollTop  = empty($this->scrollTop ) ? 0 : $this->scrollTop ;
		$scrollLeft = empty($this->scrollLeft) ? 0 : $this->scrollLeft;
		$html = '<div id="'.$this->getObjectID().'" class="DataTable" '. $this->getInlineStyle() .'>';
		$rs = $this->rows;
		$html .= '<div class="DataTableHeaders" style="width:'.$this->bodyWidth.'px">';
		if (count($this->columns)) {			
			$html .= '<div class="DataTableRow">';
			foreach($this->columns as $column) {
				$html .= '<div class="DataTableCell" style="width:' . $column->width . '">' . $column->label . '</div>';
			}
			$html .= '</div>';
		} else {
			if (count($rs)) {
				$html .= '<div class="DataTableRow">';
				foreach($rs[0] as $columnName => $value) {
					$html .= '<div class="DataTableCell" style="width:' . $column->width . '">' . $columnName . '</div>';
				}
				$html .= '</div>';
			}
		}
		$html .= '</div>'; // end TataTableHeaders
		$html .= '<div class="DataTableHole">';
		$html .= '<div class="DataTableBody" style="width:'.$this->bodyWidth.'px">';
		foreach($rs as $i => $row) {
			$classSelected = '';
			if ($this->rowIndex!==null && $i == $this->rowIndex) {
				$classSelected = ' selected';
			}
			//$ondblClick = "alert($(this).closest('.DataTable').attr('class'));";
			//$ondblClick = "console.log($(this).closest('.DataTable'));";
			$html .= '<div class="DataTableRow' . $classSelected . '">';
			foreach($this->columns as $column) {
				// $column = (property_exists($column, 'fieldName'))? $column->fieldName : '';
				$onClick    = "__doAction('send', {actionName:'rowClick',objectId:\$(this).closest('.DataTable').attr('id'),row:{$i}, fieldName:'{$column->fieldName}'});";
				$ondblClick = "__doAction('send', {actionName:'rowDoubleClick',objectId:\$(this).closest('.DataTable').attr('id'),row:{$i}, fieldName:'{$column->fieldName}'});";
				$linkable = ($column->linkable) ? ' linkable' : '';
				//echo($row[$column->fieldName] . ' - ');
				$value = empty($row[$column->fieldName]) ? '&nbsp;' : $row[$column->fieldName];
				$html .= '<div class="DataTableCell' . $linkable . '" style="width:'.$column->width.';text-align:'.$column->align.';" onclick="'.$onClick.'" ondblclick="'.$ondblClick.'">' . $value . '</div>';
			}
			$html .= '</div>'; // end DataTableRow
		}
		$html .= '</div>'; // end DataTableBody
		$html .= '</div>'; // end DataTableHole
		$html .= <<<HTML
		<script type="text/javascript">
			$(function() {
				$('#{$objectID} .DataTableHole').attr('disable-scroll-event', 'yes');
				$('#{$objectID} .DataTableHole').scrollTop({$scrollTop});
			 	$('#{$objectID} .DataTableHole').scrollLeft({$scrollLeft});
				setTimeout(function() {
					$('#{$objectID} .DataTableHole').attr('disable-scroll-event', 'NO');
				}, 600); // nunca entendí porqué hace falta esto...
			});
		</script>
HTML;
		$html .= '</div>'; // end DataTable

		return $html;
	}
}