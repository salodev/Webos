<?php
$rutas = [
'roboto/v19/KFOjCnqEu92Fr1Mu51S7ACc3CsTKlA.woff2',
'roboto/v19/KFOjCnqEu92Fr1Mu51S7ACc-CsTKlA.woff2',
'roboto/v19/KFOjCnqEu92Fr1Mu51S7ACc2CsTKlA.woff2',
'roboto/v19/KFOjCnqEu92Fr1Mu51S7ACc5CsTKlA.woff2',
'roboto/v19/KFOjCnqEu92Fr1Mu51S7ACc1CsTKlA.woff2',
'roboto/v19/KFOjCnqEu92Fr1Mu51S7ACc0CsTKlA.woff2',
'roboto/v19/KFOjCnqEu92Fr1Mu51S7ACc6CsQ.woff2',
'roboto/v19/KFOjCnqEu92Fr1Mu51TLBCc3CsTKlA.woff2',
'roboto/v19/KFOjCnqEu92Fr1Mu51TLBCc-CsTKlA.woff2',
'roboto/v19/KFOjCnqEu92Fr1Mu51TLBCc2CsTKlA.woff2',
'roboto/v19/KFOjCnqEu92Fr1Mu51TLBCc5CsTKlA.woff2',
'roboto/v19/KFOjCnqEu92Fr1Mu51TLBCc1CsTKlA.woff2',
'roboto/v19/KFOjCnqEu92Fr1Mu51TLBCc0CsTKlA.woff2',
'roboto/v19/KFOjCnqEu92Fr1Mu51TLBCc6CsQ.woff2',
'roboto/v19/KFOmCnqEu92Fr1Mu72xKOzY.woff2',
'roboto/v19/KFOmCnqEu92Fr1Mu5mxKOzY.woff2',
'roboto/v19/KFOmCnqEu92Fr1Mu7mxKOzY.woff2',
'roboto/v19/KFOmCnqEu92Fr1Mu4WxKOzY.woff2',
'roboto/v19/KFOmCnqEu92Fr1Mu7WxKOzY.woff2',
'roboto/v19/KFOmCnqEu92Fr1Mu7GxKOzY.woff2',
'roboto/v19/KFOmCnqEu92Fr1Mu4mxK.woff2',
'roboto/v19/KFOlCnqEu92Fr1MmEU9fCRc4EsA.woff2',
'roboto/v19/KFOlCnqEu92Fr1MmEU9fABc4EsA.woff2',
'roboto/v19/KFOlCnqEu92Fr1MmEU9fCBc4EsA.woff2',
'roboto/v19/KFOlCnqEu92Fr1MmEU9fBxc4EsA.woff2',
'roboto/v19/KFOlCnqEu92Fr1MmEU9fCxc4EsA.woff2',
'roboto/v19/KFOlCnqEu92Fr1MmEU9fChc4EsA.woff2',
'roboto/v19/KFOlCnqEu92Fr1MmEU9fBBc4.woff2',
'roboto/v19/KFOlCnqEu92Fr1MmWUlfCRc4EsA.woff2',
'roboto/v19/KFOlCnqEu92Fr1MmWUlfABc4EsA.woff2',
'roboto/v19/KFOlCnqEu92Fr1MmWUlfCBc4EsA.woff2',
'roboto/v19/KFOlCnqEu92Fr1MmWUlfBxc4EsA.woff2',
'roboto/v19/KFOlCnqEu92Fr1MmWUlfCxc4EsA.woff2',
'roboto/v19/KFOlCnqEu92Fr1MmWUlfChc4EsA.woff2',
'roboto/v19/KFOlCnqEu92Fr1MmWUlfBBc4.woff2',
'roboto/v19/KFOlCnqEu92Fr1MmYUtfCRc4EsA.woff2',
'roboto/v19/KFOlCnqEu92Fr1MmYUtfABc4EsA.woff2',
'roboto/v19/KFOlCnqEu92Fr1MmYUtfCBc4EsA.woff2',
'roboto/v19/KFOlCnqEu92Fr1MmYUtfBxc4EsA.woff2',
'roboto/v19/KFOlCnqEu92Fr1MmYUtfCxc4EsA.woff2',
'roboto/v19/KFOlCnqEu92Fr1MmYUtfChc4EsA.woff2',
'roboto/v19/KFOlCnqEu92Fr1MmYUtfBBc4.woff2',
];

$host = 'https://fonts.gstatic.com/s/';

foreach($rutas as $fuente) {
	$url = $host . $fuente;
	$destino = '/home/salomon/dev/php/repos/Webos/resources/fonts/' . $fuente;
	file_put_contents($destino, file_get_contents($url), FILE_IGNORE_NEW_LINES);
}