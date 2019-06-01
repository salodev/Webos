<?php
/**
 * Adds data consuming behavior to any object easy way
 * 
 */
namespace Webos\Visual;

use Webos\Closure;
use Exception;

trait DataConsuming {
	
	protected $_offset = 0;
	protected $_limit  = 100;
	
	protected $_dataSourceFn;
	
	public function reset(): self {
		$this->setOffset(0);
		return $this;
	}
	
	public function setOffset(int $value): self {
		$this->_offset = $value;
		$this->refresh();
		
		return $this;
	}
	
	public function setLimit(int $value): self {
		$this->_limit = $value;
		$this->refresh();
		
		return $this;
	}
	
	/**
	 * Set a callback function that receive parameters, make a query
	 * and return an data resultset array.
	 * 
	 * E.g. 
	 * $myUIObject->setDataFn(function(array $params = []) {
	 *		$rs = MyRepoitory::GetQuotes($params['targetUserID']);
	 *		return $rs;
	 * }, false);
	 * 
	 * $myUIObject->refresh(['targetUserID' => $myUserID]);
	 * @param callable $fn            Callback function to be called for data.
	 * @param bool     $refresh       Wether call function after set.
	 * @param array    $refreshParams Params only for first call, if set to TRUE
	 */
	public function setDataFn(callable $fn, bool $refresh = true, array $refreshParams = []) {
		$this->_dataSourceFn = new Closure($fn);
		if ($refresh) {
			$this->refresh($refreshParams);
		}
	}
	
	public function refresh() {
		$data = $this->_queryData($this->_offset, $this->_limit);
		$this->setData($data);
	}
	
	protected function _queryData(int $offset = 0, int $limit = 0) {
		if (!is_callable($this->_dataSourceFn)) {
			throw new Exception('Not callback function for data');
		}
		$ds = $this->_dataSourceFn;
		return $ds($offset, $limit);
	}
	
	public function setData(array $data = []) {
		$this->rows = $data;
	}
	
	public function addData(array $data = []) {
		foreach($data as $row) {
			$this->rows[] = $row;
		}
	}
}