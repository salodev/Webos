<?php
namespace Webos\Visual\Windows;

use Webos\Visual\Window;
use Webos\Visual\Controls\TextBox;
use Exception as ExceptionClass;
use Throwable;

class Exception extends Window {

	public function initialize(array $params = []) {
		$this->title  = 'Exception trown';
		$this->width  = 800;
		$this->height = 500;
		
		$this->splitHorizontal(60);
		$this->bottomPanel->splitHorizontal(-100);
		$this->panels = [
			$this->topPanel,
			$this->bottomPanel->topPanel,
			$this->bottomPanel->bottomPanel,
		];
				
		// $e = $this->exception = $params['e'];
		$this->title = $params['class'] . ' thrown:';
		$this->panels[0]->createObject(TextBox::class)
			->value($params['message'])
			->top(0)
			->left(0)
			->right(0)
			->bottom(0)
			->multiline(true)
			->disable()->width='100%';
		
		/**
		 * @var \Webos\Visual\Controls\DataTable
		 */
		$this->callStack = $this->panels[1]->createDataTable([
			'top'    => 0
		]);
		// $this->trace = $e->getTrace();
		$this->callStack->addColumn('id', '#', 30, false, false, 'right');
		$this->callStack->addColumn('function', 'Function', 500);
		$this->callStack->addColumn('location', 'Location', 300);
		
		$this->callStack->rows = $params['rsTrace'];
		$this->callStack->onRowClick(function() {
			if ($this->callStack->hasSelectedRow()) {
				$row = $this->callStack->getSelectedRowData();
				$this->panels[2]->setFormData($row, true);
			}
		});
		$this->panels[2]->createTextBox('File', 'file', ['width'=>600]);
		$this->panels[2]->createTextBox('Line', 'line');
		$this->panels[2]->createTextBox('Function', 'function');
		$this->panels[2]->disableForm();
		$this->height=500;
		
		$this->callStack->onContextMenu(function($data) {
			$menu = $data['menu'];
			$menu->createItem('Ampliar...')->onClick(function() {
				$desc = $this->callStack->getSelectedRowData('function');
				// malísimo. Sólo par salir del paso.
				$w = $this->messageWindow("<div style=\"overflow-x:scroll; position:absolute;left:0;right:0;top:0;bottom:30px;\"><pre>{$desc}</pre></div>");
				$w->title = 'Detalle';
				$w->height = 250;
			});
		});
	}
	
	static public function ParseValue($value, $maxDepth = 5, $maxCount = 10, $depth = 0) {
		
		if (is_string($value)) {
			$parsed = "'{$value}'";
		} elseif (is_numeric($value)) {
			$parsed = "{$value}";
		} elseif(is_callable($value)) {
			$parsed = "function() {}";
		} elseif (is_object($value)) {
			$parsed = get_class($value)."(...)";
		} elseif (is_array($value)) {
		
			if ($depth >= $maxDepth) {
				return '[ ... ]	';
			}
			$parsed = "[\n";
			if (count($value)) {
				if (range(0, count($value)-1) == array_keys($value)) {
					foreach($value as $v) {
						$parsed .= str_repeat("\t", $depth + 1) . self::ParseValue($v, $maxDepth, $maxCount, $depth+1) . ",\n";
					}
				} else {
					foreach($value as $k => $v) {
						$parsed .= str_repeat("\t", $depth + 1) . "{$k} => " . self::ParseValue($v, $maxDepth, $maxCount, $depth+1) . ", \n";
					}
				}
			}
			$parsed .= str_repeat("\t", $depth) .']';
		} elseif (is_null($value)) {
			$parsed = 'NULL';
		} elseif (is_bool($value)) {
			$parsed = $value ? 'TRUE' : 'FALSE';
		} else {
			$parsed = '??'. gettype($value).'??';
		}
		
		return $parsed;
	}
	
	/**
	 * To avoid cause Closure serialize error.
	 * 
	 * @param \Exception $e
	 * @return array
	 */
	static public function ParseException(Throwable $e): array {
		$exceptionClass = get_class($e);
		$message = "'{$e->getMessage()}' in file {$e->getFile()} ({$e->getLine()})";
		$rsTrace = [];
		foreach($e->getTrace() as $k => $info) {
			$class = &$info['class'];
			$type = &$info['type'];
			$argumentsString = '';
			$argsList = [];
			if (!empty($info['args'])) {
				foreach($info['args'] as $arg) {
					$argsList[] = self::ParseValue($arg, 10);
				}
				$argumentsString = implode(', ', $argsList);
			}
			$file = &$info['file'];
			$line = &$info['line'];
			$rsTrace[] = [
				'id' => "$k ",
				'function' => $class . $type . $info['function'] . '(' . $argumentsString . ')',
				'location' => '../' . basename($file) . ' (' . $line . ')',
				'file' => &$info['file'],
				'line' => &$info['line'],
				'args' => '', //&$info['args'],
			];
		}
		
		return [
			'class'   => $exceptionClass,
			'message' => $message,
			'rsTrace' => $rsTrace,
		];
	}
}