<?php
namespace Webos;

class Log {
	static public function write($text) {
		$text = date('d-m-y h:i:s') . ' - ' . $text . "\r";	
		@file_put_contents(PATH_LOG_FILE, $text, FILE_APPEND);
	}
}