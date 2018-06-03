<?php
namespace Webos\Visual\Controls;

use Exception;
use Webos\Visual\Controls\DataTable\Column;
use Webos\Exceptions\Alert;
use Webos\Collection;
use Webos\Visual\Control;

class DataTable extends Control {
	// public $rowIndex = null;
	public function initialize() {
		$this->rows       = array();
		$this->columns    = new Collection();
		$this->rowIndex   = null;
		$this->columInded = null;
	}

	public function getInitialAttributes(): array {
		return [
			'top'        => 0,
			'bottom'     => 0,
			'left'       => 0,
			'right'      => 0,
			'scrollTop'  => 0,
			'scrollLeft' => 0,
		];
	}

	public function addColumn(string $fieldName = '', string $label = '', int $width=100, bool $allowOrder=false, bool $linkable=false, string $align = 'left'): Column {
		// $column = new ColumnDataTable();
		$column = new Column($label, $fieldName);
		$column->width      = $width;
		$column->allowOrder = $allowOrder;
		$column->linkable   = $linkable;
		$column->align      = $align;
		$this->columns->add($column);
		return $column;
	}
	
	public function hasSelectedRow(): bool {
		return $this->rowIndex !== null;
	}
	
	public function checkSelectedRow(string $messageOnError = 'No selected row!'): void {
		if (!$this->hasSelectedRow()) {
			throw new Alert($messageOnError);
		}
	}

	public function getSelectedRowData(string $fieldName = null): array {
		if ($this->rowIndex !== null) {
			$rowData = $this->getRowData($this->rowIndex/1);
			if ($fieldName) {
				if (!array_key_exists($fieldName, $rowData)) {
					throw new Alert("The '{$fieldName}' field does not exist.");
				}
				return $rowData[$fieldName];
			}
			return $rowData;
		}
		return [];
	}

	public function getRowData(int $rowIndex): array {
		$i = 0;
		foreach($this->rows as $row) {
			if ($i==$rowIndex) {
				return $row;
			}
			$i++;
		}
		throw new Exception('Requested row does not exists');
	}

	public function rowClick(array $params = []): void {
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
			$this->rowIndex = $params['row']/1;
		}

		$row       = $params['row'];
		$fieldName = $params['fieldName'];
		$rowData   = $this->getRowData($row);
		$cellValue = &$rowData[$fieldName];
		
		$this->triggerEvent('rowClick', array(
			'row'       => $row,
			'fieldName' => $fieldName,
			'rowData'   => $rowData,
			'cellValue' => $cellValue,
		));
	}

	public function rowDoubleClick(array $params = []): void {
		if (!isset($params['row'])) {
			throw new Exception('The \'rowDoubleClick\' event needs a \'row\' parameter');
		}
		$this->rowIndex = $params['row'];
		$this->triggerEvent('rowDoubleClick', array('row'=>$params['row']));
	}

	public function scroll(array $params = []): void {
		//echo "hola";
		$this->scrollTop  = $params['top' ] ?? 0;
		$this->scrollLeft = $params['left'] ?? 0;
	}
	
	
	
	public function contextMenu(array $params): void {
		if (empty($params['top']) || empty($params['left'])) {
			return;
		}
		if ($this->hasListenerFor('contextMenu')) {
			$menu = $this->getParentWindow()->createContextMenu($params['top'], $params['left']);
			$eventData = ['menu' => $menu];
			if (isset($params['data'])) {
				$rowIndex = $params['data'];
				$rowData = $this->getRowData($rowIndex);
				$this->rowIndex =$rowIndex;
				$eventData['rowIndex'] = $rowIndex;
				$eventData['rowData' ] = $rowData;
			}
			$this->triggerEvent('contextMenu', $eventData);
		}
	}
	
	public function onContextMenu(callable $cb, bool $persistent = true, array $contextData = []): self {
		$this->bind('contextMenu', $cb, $persistent, $contextData);
		return $this;
	}

	public function getAllowedActions(): array {
		return [
			'rowClick',
			'rowDoubleClick',
			'scroll',
			'contextMenu',
		];
	}

	public function getAvailableEvents(): array {
		return [
			'rowClick',
			'rowDoubleClick',
			'contextMenu',
		];
	}
	
	public function onRowClick(callable $eventListener, bool $persistent = true, array $contextData = []): void {
		$this->bind('rowClick', $eventListener, $persistent, $contextData);
	}
	
	public function onRowDoubleClick(callable $eventListener, bool $persistent = true, array $contextData = []): void {
		$this->bind('rowDoubleClick', $eventListener, $persistent, $contextData);
	}
	
	public function render(): string {
		$objectID   = $this->getObjectID();

		$scrollTop  = $this->scrollTop  ?? 0;
		$scrollLeft = $this->scrollLeft ?? 0;
		
		$hasContextMenu = $this->hasListenerFor('contextMenu');
		$inlineStyle    = $this->getInlineStyle();
		$contextMenuDirective = $hasContextMenu ? 'webos contextmenu' : '';
		
		$html = "<div id=\"{$objectID}\" class=\"DataTable\" {$inlineStyle} {$contextMenuDirective}>";
		
		$rs = $this->rows;
		$bodyWidth = 0;
		foreach($this->columns as $column) {
			$bodyWidth += $column->width+8;
		}
		$html .= '<div class="DataTableHeaders" style="width:'.$bodyWidth.'px">';
		if (count($this->columns)) {			
			$html .= '<div class="DataTableRow">';
			foreach($this->columns as $column) {
				$html .= '<div class="DataTableCell" style="width:' . $column->width . 'px">' . $column->label . '</div>';
			}
			$html .= '</div>';
		} else {
			if (count($rs)) {
				$html .= '<div class="DataTableRow">';
				foreach($rs[0] as $columnName => $value) {
					$html .= '<div class="DataTableCell" style="width:' . $column->width . 'px">' . $columnName . '</div>';
				}
				$html .= '</div>';
			}
		}
		$html .= '</div>'; // end TataTableHeaders
		$html .= "<div class=\"DataTableHole\" webos set-scroll-values=\"{$scrollTop},{$scrollLeft}\">";
		$html .= '<div class="DataTableBody" style="width:'.$bodyWidth.'px">';
		
		foreach($rs as $i => $row) {
			$classSelected = '';
			if ($this->rowIndex!==null && $i == $this->rowIndex) {
				$classSelected = ' selected';
			}
			
			$contextMenuDirective = $hasContextMenu ? "webos contextmenu=\"{$i}\"" : '';
			$html .= "<div 
				class=\"DataTableRow {$classSelected}\" 
				webos toggle-class=\"selected\" remove-others
				{$contextMenuDirective}>";
			foreach($this->columns as $column) {
				
				$linkable = ($column->linkable) ? ' linkable' : '';
				
				/**
				 * Esta porción intenta obtener el valor de una estructura
				 * de varios niveles.
				 * Para ello, los niveles se separan con . y aquí el código
				 * va iterando y excavando los niveles hasta obtener el valor.
				 */
				$rowValue = $row;
				$fieldParts = explode('.', $column->fieldName);
				foreach($fieldParts as $fieldPart) {
					$rowValue = $rowValue[$fieldPart];
				}
				// fin de obtención del valor de la columna.
				
				if (empty($rowValue)) {
					$value = '&nbsp;';
				} else {
					$value = $column->renderValue($rowValue);
				}
				$html .= 
					"<div class=\"DataTableCell{$linkable} no-break\" " .
						"style=\"width:{$column->width}px;text-align:{$column->align};\" " .
						'webos click="rowClick" ' .
						'double-click="rowDoubleClick" '.
						"data-row=\"{$i}\" ".
						"data-field-name=\"{$column->fieldName}\"" . 
						"data-ignore-update-object=\"1\"" .
						">" .
						$value . 
					"</div>";
			}
			$html .= '</div>'; // end DataTableRow
		}
		$html .= '</div>'; // end DataTableBody
		$html .= '</div>'; // end DataTableHole
		$html .= '</div>'; // end DataTable

		return $html;
	}
}