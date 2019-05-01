<?php
namespace Webos\Implementations;
use Webos\Webos;
use Exception;

class ResourcesLoader {
	
	static private function GetFullPath(string $resourceFileName): string {
		if (empty($resourceFileName)) {
			throw new Exception('empy filename for resource');
		}
		
		if (strpos($resourceFileName, '..')) {
			throw new Exception('Invalid filename.');
		}
		
		$basePath = Webos::GetInstallationPath();
		
		$fullPath = "{$basePath}/resources/{$resourceFileName}";
		
		if (is_file("{$fullPath}.php")) {
			$fullPath = "{$fullPath}.php";
		}
		
		if (!is_file($fullPath)) {
			throw new Exception('File does not exist.');
		}
		
		if (!is_readable($fullPath)) {
			throw new Exception('Access denied.');
		}
		
		return $fullPath;
		
	}
	
	static public function ServeFile(string $type, string $resourceFileName): void {
		try {
			$fullPath = self::GetFullPath($resourceFileName);
		} catch (Exception $e) {
			die($e->getMessage());
		}
		switch($type) {
			case 'js':
				$mimeType = 'text/javascript';
				break;
			case 'css':
				$mimeType = 'text/css';
				break;
			default;
				$finfo = finfo_open(FILEINFO_MIME_TYPE); // devuelve el tipo mime de su extensión
				$mimeType =  finfo_file($finfo, $fullPath);
				finfo_close($finfo);
				break;
		}

		header("Content-type: {$mimeType}");
		header('Cache-Control: public');
		require($fullPath);
		//readfile($fullPath);
		die();
	}
}