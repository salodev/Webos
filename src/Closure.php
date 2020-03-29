<?php

namespace Webos;

use Closure as PHPClosure;
use ReflectionFunction;
use ReflectionObject;

/**
 * Dirty solution for serializing closures.
 * Useful for event driven development. 
 * 
 * It works stroring source code into local property and evaluating at invoke.
 * 
 * Currently is not supporting 'use( ... )' sintax. If present, variables
 * will not be filled, because original scope values are lost.
 * 
 * Neverthless $this scope is available. 
 */
class Closure {
	
	private $_code           = null;
	private $_thisScope      = null;
	private $_topDefinitions = null;
	private $_codeToEval     = null;
	private $_args           = null;

	public function __construct(PHPClosure $closure) {
		$this->_code = $this->_getClosureCode($closure);
		$this->_thisScope = $this->_getThisScope($closure);
		$this->_topDefinitions = $this->_getTopDefinitions($closure);
	}
	
	public function __invoke() {
		$codeToEval = "
			{$this->_topDefinitions}
			\$closure = {$this->_code};
		";
		
		// eval($codeToEval);
		$args = func_get_args();
		if ($this->_thisScope) {
			$codeToEval .= "
				\$newClosure = \$closure->bindTo(\$this->_thisScope);
				\$ret = call_user_func_array(\$newClosure, \$args);
			";
		} else {
			$codeToEval .= "
				\$ret = call_user_func_array(\$closure, \$args);
			";
		}
		return $this->_myEvaluate($codeToEval, $args, $this->_thisScope);
	}
	
	private function _myEvaluate($codeToEval, $args, $thisScope) {
		$this->_codeToEval = $codeToEval; // useful for debugging.
		$this->_args       = $args;       // useful for debugging.
		eval($codeToEval);
		// $ret variable is defined into $codeToEval evaluated code..
		return $ret;
	}
	
	public function _getThisScope(PHPClosure $closure) {
		$r = new ReflectionFunction($closure);
		return $r->getClosureThis();
	}
	
	private function _getClosureCode(PHPClosure $closure) {
		$r = new ReflectionFunction($closure);
		$f = $r->getFileName();
		$s = $r->getStartLine();
		$e = $r->getEndLine();
		if (strpos($r, "eval()'d code")) {
			$fileCode = $this->_getCodeFromLastEval();
			if (!$fileCode) {
				return 'function() { echo \'error getting closure code.\';}';
			}
		} else {
			$fileCode = file_get_contents($f);
		}
		$code = $this->_getDefinitionFromCode($fileCode, $s, $e);
		return $code;
	}
	
	private function _getTopDefinitions(PHPClosure $closure) {
		$r = new ReflectionFunction($closure);
		$f = $r->getFileName();
		if (strpos($r, "eval()'d code")) {
			return $this->_getTopDeclarationsFromLastEval();
		} else {
			return $this->_getTopDeclarationsFromFile($f);
		}
	}
	
	/**
	 * @TODO: THIS IS AN HORROR!
	 * Now I have a Language interpreter, so I can use a language interpreter to do it.
	 */
	private function _getTopDeclarationsFromFile($f) {
		$content = file_get_contents($f);
		$content = str_replace("\n\r", "\n", $content);
		$content = str_replace("\r\n", "\n", $content);
		$content = str_replace("\r",   "\n", $content);
		$lines = explode("\n", $content);
		$extractedLines = [];
		foreach($lines as $line) {
			if (preg_match('/(\<\?)/', $line)) {
				continue;
			}
			if (preg_match('/^(\s)*(class|trait|abstract|function)/', $line)) {
				break; // end of file efinitions
			}
			$extractedLines[] = $line;
		}
		$extractedLines[] = '';
		return implode("\n", $extractedLines);
	}
	
	private function _getTopDeclarationsFromLastEval() {
		$lastTrace = $this->_getLastEvalFromTrace();
		if (!$lastTrace) {
			return '';
		}
		
		$prevClosureThis = &$lastTrace['args'][2];
				
		if (empty($prevClosureThis)){
			return '';
		}
		$r = new ReflectionObject($prevClosureThis);
		$f = $r->getFileName();
		return $this->_getTopDeclarationsFromFile($f);
		
	}
	
	private function _getCodeFromLastEval() {
		$lastTrace = $this->_getLastEvalFromTrace();
		if (!$lastTrace) {
			return '';
		}
		$prevClosureCode = $lastTrace['args'][0];
		$prevClosureThis = &$lastTrace['args'][3];
				
		if (!empty($prevClosureThis)){
			$r = new ReflectionObject($prevClosureThis);
		}
		
		return $prevClosureCode;
		
	}
	
	private function _getLastEvalFromTrace() {
		$stack = debug_backtrace();
		$lastTrace = null;
		// print_r($stack);
		foreach($stack as $trace) {
			// echo $trace['function'] . "\n";
			if ($trace['function']=='_myEvaluate') {
				$lastTrace = $trace;
				break;
			}
		}
		if ($lastTrace==null){
			return false;
		}
		
		if (empty($lastTrace['args'])) {
			return false;
		}
		
		return $lastTrace;
	}
	
	private function _getDefinitionFromCode($sourceCode, $s, $e) {
		$sourceCode = str_replace("\n\r", "\n", $sourceCode);
		$sourceCode = str_replace("\r\n", "\n", $sourceCode);
		$sourceCode = str_replace("\r", "\n", $sourceCode);
		$lines = array_merge([''], explode("\n", $sourceCode));
		// echo "SE EVALUARA EL SIGUIENTE CODIGO {$s} - {$e}:\n{$sourceCode}\nFIN CODIGO\n";
		$extractedLines = [];
		for($i=$s;$i<=$e;$i++) {
			if ($i==$s) {
				// echo "EVALUANDO: *{$lines[$i]}* ($i)\n";
				preg_match('/.*(function.*)/', $lines[$i], $matches);
				if (empty($matches[1])) {
					return 'function() { /* Error evaluating closure */};';
				}
				$extractedLines[] = $matches[1];
			} elseif ($i==$e){
				$extractedLines[$i] = '};';
			} else {
				$extractedLines[] = $lines[$i];
			}
		}
		$extractedCode = implode("\n", $extractedLines);
		return $extractedCode;
	}
}