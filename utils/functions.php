<?php
/**
 * Filtra un array de parámetros excluyendo aquellos que no figuran en la
 * lista de nombres admitidos.
 *
 * @param  array $list Lista de nombres admitidos
 * @param  array $params Array asociativo con los nombres de parÃ¡metros y sus valores
 * @return array Array asociativo filtrado por la lista.
 */
function filterParams(array $list, array $params) {
	$return = array();
	foreach($list as $name) {
		if (isset($params[$name])) {
			$return[$name] = $params[$name];
		}
	}

	return $return;
}

/**
 * Rellena un array asociativo con valores iniciales, pero excluyendo aquellos
 * que no se encuentren en la lista de los valores por defecto.
 *
 * @param  array $fill ParÃ¡metros con valores iniciales.
 * @param  array $params Array asociativo con los nombres de parÃ¡metros y sus valores
 * @return array Array asociativo rellenado y filtrado.
 */
function fillParams(array $fill, array $params) {
	$return = array();
	foreach($fill as $k => $v) {
		if (isset($params[$k])) {
			$return[$k] = $params[$k];
		} else {
			$return[$k] = $v;
		}
	}

	return $return;
}

function ifempty(&$value, $default = null) {
	if (empty($value)) return $default;
	return $value;
}

function logText($text) {
	file_put_contents(BACKEND_LOG_FILE, $text, FILE_APPEND);
}

function logLn($text) {
	$time = date('Y-m-d H:i:s');
	logText("$time $text\n");
}

function wrapResponseError($message, $code = 0, $data = null) {
	return array(
		'status'  => 'error',
		'message' => $message,
		'code'    => $code,
		'data'    => $data,
	);
}

function wrapResponseOK($data) {
	return array(
		'status' => 'ok',
		'data'   => $data,
	);
}

function getView($filename, array $parameters = array()) {
	$content = '';
	extract($parameters);
	if (is_file($filename)) {
		ob_start();
		require $filename;
		$content = trim(ob_get_clean());
	}

	return $content;
}

/*function sendMail($to, $subject, $content) {
	$rendered = getView(PATH_VIEWS . 'email/basicTemplate', array('content'=>$content));
	logLn($rendered);
	return Mail::send($to, $subject, $rendered);
}*/

function sendMail($to, $subject, $content, $template = 'default', $replyTo = null, $attachment = null) {
	$rendered = getView(PATH_VIEWS . 'email/' . $template, array('content'=>$content));

	$client = new SMTPClient($replyTo);
	return $client->sendMail($to, $subject, $rendered, null, $attachment);
}

function str2hex($string, $prepend0x = true){
	if (DB_SQL_INJECTION_PEV == TRUE) {
		$hex='';
		if ($prepend0x) {
			$hex = '0x';
		}
		for ($i=0; $i < strlen($string); $i++){
			$hex .= dechex(ord($string[$i]));
		}
		return $hex;
	} else {
		return "'{$string}'";
	}
}


function hex2str($hex){
    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2){
        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;
}

function sanitize($string) {
	$sqlStrings = "SELECT|FROM|WHERE|JOIN|INSERT|INTO|UNION|CHAR|CHR|'|´|--|#|(|)!";
	$sUa = explode('|', $sqlStrings);
	$sLa = explode('|', strtolower($sqlStrings));
	foreach ($sUa as $str) {
		if (stripos($string, $str)!==false) {
			return false;
		}
	}

	return true;
}
function sanitizeForSQL($string) {
	$patternFind = '/(SELECT|INSERT|UPDATE|DELETE|TRUNCATE|DROP|UPSERT|REPLACE|INTO|UNION|FROM|WHERE|JOIN|CHR|CHAR|--|\'|\`|\´|\#|\(|\))+/i';
	$patternRepl = '';
	return preg_replace($patternFind, $patternRepl,$string);
}