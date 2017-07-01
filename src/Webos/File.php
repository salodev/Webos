<?php
namespace Webos;

class File {

	private $fh = null;
	public function __construct($fileName, $fh) {
		$this->fileName = $fileName;
		$this->fh = $fh;
	}

	public function getFileName() {
		return $this->fileName;
	}

	public function getFH() {
		return $this->fh;
	}

	public function getContent() {
		$content = fread($this->fh, filesize($this->fileName)+10);
		return $content;
	}

	public function putContent($content) {
		fwrite($this->fh, $content);
	}
}