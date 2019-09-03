<?php

use Webos\FrontEnd\CSS\StylesManager;
use Webos\Implementations\Service;

$sm = StylesManager::Instance();
$url = Service::GetUrl();

$sm->defineColor('yellow',   '#f5b400');
$sm->defineColor('lighthole',   '#f1f3f4');
// $sm->defineColor('blue',     '#5589e1');
// $sm->defineColor('blue',     '#4a78c5');
$sm->defineColor('blue',     'rgba(74,120,197,0.95)');
$sm->defineColor('red',      '#dc4437');
$sm->defineColor('green',    '#56a845');
$sm->defineColor('paper',    '#555555', '#ffffff');
$sm->defineColor('gray',     '#cdcdcd', '#ffffff');
$sm->defineColor('gray1',    '#eeeeee', '#ffffff');
$sm->defineColor('gray2',    '#dbe2e5', '#ffffff');
$sm->defineColor('gray3',    '#5C5F69', '#ffffff');
$sm->defineColor('gray4',    '#555555', '#ffffff');
$sm->defineColor('gray5',    '#28292d', '#ffffff');
$sm->defineColor('darkgray', '#666666', '#ffffff');
$sm->addPalette('white', '#ffffff');
$sm->addPalette('black', '#000000');

$sm->define('noborder', [
	'border' => 'none',
]);

$sm->define('box', [
	'padding' => '3px 9px',
]);

$sm->define('click', [
	'cursor' => 'pointer',
]);

$sm->define('control', [
	'border' => 'none',
	// 'border-top' => 'none',
	// 'border-left' => 'none',
	// 'border-right' => 'none',
	// 'border-bottom' => 'solid 1px ' . $sm->getPalette('gray'),
	'background' => $sm->getPalette('lighthole'),
])->import('box');

$sm->addRule('*', [
	'box-sizing' => 'border-box !important',
]);
$sm->addRule('body', [
	// 'font-family'      => "'Roboto', sans-serif, Helvetica",
	'font-family'      => 'Helvetica',
	'background-color' => '#f6f6f6',
	'background-image' => "url('{$url}img/bg.png')",
	'font-size'        => '14px',
	'font-style'       => 'normal',
	'margin'           => '0',
]);

$sm->addRule('.LabelControl', [
	'font-weight' => '600',
	'font-size' => '13px',
	//'background' => $sm->getPalette('lighthole'),
]);
$sm->addRule('.Control.Field')->import('control');
$sm->addRule('.Control.Field:focus,.Control.Field:active', [
	'border-bottom' => 'solid 1px ' . $sm->getPalette('darkgray'),
	'box-shadow' => 'none !important',
	'outline' => 'none',
]);
$sm->addRule('.Control[disabled]', [
	'opacity' => '0.7',
	'cursor'  => 'initial',
]);

$sm->define('icon', [
	'font-family' => 'Glyphicons Halflings',
]);
$sm->define('icon-chevron-down', [
	'content' => '"\E114"',
])->import('icon');

$sm->addRule('.Button', [
	// 'font-family'    => "'Roboto'",
	// 'text-transform' => 'uppercase',
	'height'        => '25px',
	'font-weight'	=> '500',
	'line-height'   => '13px',
	'outline'       => 'none',
	'padding'       => '3px 8px',
])->import('blue')->invert()->import('noborder', 'box', 'click');
$sm->addRule('.Button:focus', [
	'filter' => 'brightness(90%)',
]);
$sm->addRule('.Button:active', [
	'filter' => 'brightness(110%)',
]);

$sm->addRule('.LinkButton', [
	'text-decoration' => 'none',
])->like('.Button')->set('padding', '5px 9px');

$sm->addRule('.DropDown .icon', [
	'font-family' => 'Glyphicons Halflings',
    'display' => 'block',
    'float' => 'right',
    'width' => '19px',
    'padding' => '0',
    'margin-left' => '5px',    
    'font-weight' => '100',
    'border-left' => 'solid 1px #ccc',
    'padding-left' => '5px',
]);
$sm->addRule('.DropDown .icon:before', [
	'content'=> '"\E114"'
]);
$sm->addRule('.modal-wrapper', [
	'top'        => 0,
    'left'       => 0,
    'right'      => 0,
    'bottom'     => 0,
    'position'   => 'fixed',
    'background' => 'rgba(0,0,0,.4)',
]);
$sm->addRule('.Window')->import('paper');
$sm->addRule('.Window .form-titlebar .title')->import('gray4');
$sm->addRule('.Window .form-titlebar .controls *')->import('gray4')->important('background');



// $sm->addRule('.FilePicker')->like('.Button');

$sm->addRule('.MenuItem:hover', [
	'cursor' => 'pointer',
])->import('blue')->invert()->important('background');

$sm->addRule('.MenuItem.selected')->like('.MenuItem:hover');

$sm->addRule('.MenuButton:hover > .text')->import('blue')->invert()->important('background');
$sm->addRule('.MenuButton.selected > .text')->import('blue')->invert()->important('background');

$sm->addRule('.MultiTab > .Tabs', [
	'border-bottom' => 'solid 1px ' . $sm->getPalette('blue'),
]);

$sm->addRule('.MultiTab > .Tabs > .tab', [
	'color'         => $sm->getPalette('black'),
]);
$sm->addRule('.MultiTab > .Tabs > .tab.selected', [
	'color'         => $sm->getPalette('blue'),
	'border-bottom' => 'solid 2px ' . $sm->getPalette('blue'),
]);
$sm->addRule('.MultiTab > .Tabs > .tab:hover', [
	// 'color' => $sm->getPalette('green'),
	'border-bottom' => 'solid 2px ' . $sm->getPalette('blue'),
]);

$sm->addRule('.FrameControl', [ 
    'overflow'=> 'hidden',
]);

$sm->addRule('.Control.Link:visited', [
	'color' => 'blue',
]);


echo $sm->getStyles(false);
?>
@font-face {
		font-family:'Glyphicons Halflings';
		src:url('<?= $url; ?>fonts/glyphicons-halflings-regular.eot');
		src:url('<?= $url; ?>fonts/glyphicons-halflings-regular.eot?#iefix') format('embedded-opentype'),
			url('<?= $url; ?>fonts/glyphicons-halflings-regular.woff') format('woff'),
			url('<?= $url; ?>fonts/glyphicons-halflings-regular.ttf') format('truetype'),
			url('<?= $url; ?>fonts/glyphicons-halflings-regular.svg#glyphicons_halflingsregular') format('svg')
}
@font-face {
		font-family:'Glyphicons Regular';
		src:url('<?= $url; ?>fonts/glyphicons-regular.eot');
		src:url('<?= $url; ?>fonts/glyphicons-regular.eot?#iefix') format('embedded-opentype'),
			url('<?= $url; ?>fonts/glyphicons-regular.woff') format('woff'),
			url('<?= $url; ?>fonts/glyphicons-regular.ttf') format('truetype'),
			url('<?= $url; ?>fonts/glyphicons-regular.svg#glyphiconsregular') format('svg');
		font-weight:normal;
		font-style:normal
}


/* cyrillic-ext */
@font-face {
  font-family: 'Roboto';
  font-style: italic;
  font-weight: 500;
  font-display: swap;
  src: local('Roboto Medium Italic'), local('Roboto-MediumItalic'), url(<?= $url; ?>fonts/roboto/v19/KFOjCnqEu92Fr1Mu51S7ACc3CsTKlA.woff2) format('woff2');
  unicode-range: U+0460-052F, U+1C80-1C88, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;
}
/* cyrillic */
@font-face {
  font-family: 'Roboto';
  font-style: italic;
  font-weight: 500;
  font-display: swap;
  src: local('Roboto Medium Italic'), local('Roboto-MediumItalic'), url(<?= $url; ?>fonts/roboto/v19/KFOjCnqEu92Fr1Mu51S7ACc-CsTKlA.woff2) format('woff2');
  unicode-range: U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;
}
/* greek-ext */
@font-face {
  font-family: 'Roboto';
  font-style: italic;
  font-weight: 500;
  font-display: swap;
  src: local('Roboto Medium Italic'), local('Roboto-MediumItalic'), url(<?= $url; ?>fonts/roboto/v19/KFOjCnqEu92Fr1Mu51S7ACc2CsTKlA.woff2) format('woff2');
  unicode-range: U+1F00-1FFF;
}
/* greek */
@font-face {
  font-family: 'Roboto';
  font-style: italic;
  font-weight: 500;
  font-display: swap;
  src: local('Roboto Medium Italic'), local('Roboto-MediumItalic'), url(<?= $url; ?>fonts/roboto/v19/KFOjCnqEu92Fr1Mu51S7ACc5CsTKlA.woff2) format('woff2');
  unicode-range: U+0370-03FF;
}
/* vietnamese */
@font-face {
  font-family: 'Roboto';
  font-style: italic;
  font-weight: 500;
  font-display: swap;
  src: local('Roboto Medium Italic'), local('Roboto-MediumItalic'), url(<?= $url; ?>fonts/roboto/v19/KFOjCnqEu92Fr1Mu51S7ACc1CsTKlA.woff2) format('woff2');
  unicode-range: U+0102-0103, U+0110-0111, U+1EA0-1EF9, U+20AB;
}
/* latin-ext */
@font-face {
  font-family: 'Roboto';
  font-style: italic;
  font-weight: 500;
  font-display: swap;
  src: local('Roboto Medium Italic'), local('Roboto-MediumItalic'), url(<?= $url; ?>fonts/roboto/v19/KFOjCnqEu92Fr1Mu51S7ACc0CsTKlA.woff2) format('woff2');
  unicode-range: U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;
}
/* latin */
@font-face {
  font-family: 'Roboto';
  font-style: italic;
  font-weight: 500;
  font-display: swap;
  src: local('Roboto Medium Italic'), local('Roboto-MediumItalic'), url(<?= $url; ?>fonts/roboto/v19/KFOjCnqEu92Fr1Mu51S7ACc6CsQ.woff2) format('woff2');
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
/* cyrillic-ext */
@font-face {
  font-family: 'Roboto';
  font-style: italic;
  font-weight: 900;
  font-display: swap;
  src: local('Roboto Black Italic'), local('Roboto-BlackItalic'), url(<?= $url; ?>fonts/roboto/v19/KFOjCnqEu92Fr1Mu51TLBCc3CsTKlA.woff2) format('woff2');
  unicode-range: U+0460-052F, U+1C80-1C88, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;
}
/* cyrillic */
@font-face {
  font-family: 'Roboto';
  font-style: italic;
  font-weight: 900;
  font-display: swap;
  src: local('Roboto Black Italic'), local('Roboto-BlackItalic'), url(<?= $url; ?>fonts/roboto/v19/KFOjCnqEu92Fr1Mu51TLBCc-CsTKlA.woff2) format('woff2');
  unicode-range: U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;
}
/* greek-ext */
@font-face {
  font-family: 'Roboto';
  font-style: italic;
  font-weight: 900;
  font-display: swap;
  src: local('Roboto Black Italic'), local('Roboto-BlackItalic'), url(<?= $url; ?>fonts/roboto/v19/KFOjCnqEu92Fr1Mu51TLBCc2CsTKlA.woff2) format('woff2');
  unicode-range: U+1F00-1FFF;
}
/* greek */
@font-face {
  font-family: 'Roboto';
  font-style: italic;
  font-weight: 900;
  font-display: swap;
  src: local('Roboto Black Italic'), local('Roboto-BlackItalic'), url(<?= $url; ?>fonts/roboto/v19/KFOjCnqEu92Fr1Mu51TLBCc5CsTKlA.woff2) format('woff2');
  unicode-range: U+0370-03FF;
}
/* vietnamese */
@font-face {
  font-family: 'Roboto';
  font-style: italic;
  font-weight: 900;
  font-display: swap;
  src: local('Roboto Black Italic'), local('Roboto-BlackItalic'), url(<?= $url; ?>fonts/roboto/v19/KFOjCnqEu92Fr1Mu51TLBCc1CsTKlA.woff2) format('woff2');
  unicode-range: U+0102-0103, U+0110-0111, U+1EA0-1EF9, U+20AB;
}
/* latin-ext */
@font-face {
  font-family: 'Roboto';
  font-style: italic;
  font-weight: 900;
  font-display: swap;
  src: local('Roboto Black Italic'), local('Roboto-BlackItalic'), url(<?= $url; ?>fonts/roboto/v19/KFOjCnqEu92Fr1Mu51TLBCc0CsTKlA.woff2) format('woff2');
  unicode-range: U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;
}
/* latin */
@font-face {
  font-family: 'Roboto';
  font-style: italic;
  font-weight: 900;
  font-display: swap;
  src: local('Roboto Black Italic'), local('Roboto-BlackItalic'), url(<?= $url; ?>fonts/roboto/v19/KFOjCnqEu92Fr1Mu51TLBCc6CsQ.woff2) format('woff2');
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
/* cyrillic-ext */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: local('Roboto'), local('Roboto-Regular'), url(<?= $url; ?>fonts/roboto/v19/KFOmCnqEu92Fr1Mu72xKOzY.woff2) format('woff2');
  unicode-range: U+0460-052F, U+1C80-1C88, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;
}
/* cyrillic */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: local('Roboto'), local('Roboto-Regular'), url(<?= $url; ?>fonts/roboto/v19/KFOmCnqEu92Fr1Mu5mxKOzY.woff2) format('woff2');
  unicode-range: U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;
}
/* greek-ext */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: local('Roboto'), local('Roboto-Regular'), url(<?= $url; ?>fonts/roboto/v19/KFOmCnqEu92Fr1Mu7mxKOzY.woff2) format('woff2');
  unicode-range: U+1F00-1FFF;
}
/* greek */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: local('Roboto'), local('Roboto-Regular'), url(<?= $url; ?>fonts/roboto/v19/KFOmCnqEu92Fr1Mu4WxKOzY.woff2) format('woff2');
  unicode-range: U+0370-03FF;
}
/* vietnamese */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: local('Roboto'), local('Roboto-Regular'), url(<?= $url; ?>fonts/roboto/v19/KFOmCnqEu92Fr1Mu7WxKOzY.woff2) format('woff2');
  unicode-range: U+0102-0103, U+0110-0111, U+1EA0-1EF9, U+20AB;
}
/* latin-ext */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: local('Roboto'), local('Roboto-Regular'), url(<?= $url; ?>fonts/roboto/v19/KFOmCnqEu92Fr1Mu7GxKOzY.woff2) format('woff2');
  unicode-range: U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;
}
/* latin */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: local('Roboto'), local('Roboto-Regular'), url(<?= $url; ?>fonts/roboto/v19/KFOmCnqEu92Fr1Mu4mxK.woff2) format('woff2');
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
/* cyrillic-ext */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: local('Roboto Medium'), local('Roboto-Medium'), url(<?= $url; ?>fonts/roboto/v19/KFOlCnqEu92Fr1MmEU9fCRc4EsA.woff2) format('woff2');
  unicode-range: U+0460-052F, U+1C80-1C88, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;
}
/* cyrillic */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: local('Roboto Medium'), local('Roboto-Medium'), url(<?= $url; ?>fonts/roboto/v19/KFOlCnqEu92Fr1MmEU9fABc4EsA.woff2) format('woff2');
  unicode-range: U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;
}
/* greek-ext */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: local('Roboto Medium'), local('Roboto-Medium'), url(<?= $url; ?>fonts/roboto/v19/KFOlCnqEu92Fr1MmEU9fCBc4EsA.woff2) format('woff2');
  unicode-range: U+1F00-1FFF;
}
/* greek */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: local('Roboto Medium'), local('Roboto-Medium'), url(<?= $url; ?>fonts/roboto/v19/KFOlCnqEu92Fr1MmEU9fBxc4EsA.woff2) format('woff2');
  unicode-range: U+0370-03FF;
}
/* vietnamese */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: local('Roboto Medium'), local('Roboto-Medium'), url(<?= $url; ?>fonts/roboto/v19/KFOlCnqEu92Fr1MmEU9fCxc4EsA.woff2) format('woff2');
  unicode-range: U+0102-0103, U+0110-0111, U+1EA0-1EF9, U+20AB;
}
/* latin-ext */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: local('Roboto Medium'), local('Roboto-Medium'), url(<?= $url; ?>fonts/roboto/v19/KFOlCnqEu92Fr1MmEU9fChc4EsA.woff2) format('woff2');
  unicode-range: U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;
}
/* latin */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: local('Roboto Medium'), local('Roboto-Medium'), url(<?= $url; ?>fonts/roboto/v19/KFOlCnqEu92Fr1MmEU9fBBc4.woff2) format('woff2');
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
/* cyrillic-ext */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 700;
  font-display: swap;
  src: local('Roboto Bold'), local('Roboto-Bold'), url(<?= $url; ?>fonts/roboto/v19/KFOlCnqEu92Fr1MmWUlfCRc4EsA.woff2) format('woff2');
  unicode-range: U+0460-052F, U+1C80-1C88, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;
}
/* cyrillic */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 700;
  font-display: swap;
  src: local('Roboto Bold'), local('Roboto-Bold'), url(<?= $url; ?>fonts/roboto/v19/KFOlCnqEu92Fr1MmWUlfABc4EsA.woff2) format('woff2');
  unicode-range: U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;
}
/* greek-ext */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 700;
  font-display: swap;
  src: local('Roboto Bold'), local('Roboto-Bold'), url(<?= $url; ?>fonts/roboto/v19/KFOlCnqEu92Fr1MmWUlfCBc4EsA.woff2) format('woff2');
  unicode-range: U+1F00-1FFF;
}
/* greek */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 700;
  font-display: swap;
  src: local('Roboto Bold'), local('Roboto-Bold'), url(<?= $url; ?>fonts/roboto/v19/KFOlCnqEu92Fr1MmWUlfBxc4EsA.woff2) format('woff2');
  unicode-range: U+0370-03FF;
}
/* vietnamese */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 700;
  font-display: swap;
  src: local('Roboto Bold'), local('Roboto-Bold'), url(<?= $url; ?>fonts/roboto/v19/KFOlCnqEu92Fr1MmWUlfCxc4EsA.woff2) format('woff2');
  unicode-range: U+0102-0103, U+0110-0111, U+1EA0-1EF9, U+20AB;
}
/* latin-ext */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 700;
  font-display: swap;
  src: local('Roboto Bold'), local('Roboto-Bold'), url(<?= $url; ?>fonts/roboto/v19/KFOlCnqEu92Fr1MmWUlfChc4EsA.woff2) format('woff2');
  unicode-range: U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;
}
/* latin */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 700;
  font-display: swap;
  src: local('Roboto Bold'), local('Roboto-Bold'), url(<?= $url; ?>fonts/roboto/v19/KFOlCnqEu92Fr1MmWUlfBBc4.woff2) format('woff2');
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
/* cyrillic-ext */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 900;
  font-display: swap;
  src: local('Roboto Black'), local('Roboto-Black'), url(<?= $url; ?>fonts/roboto/v19/KFOlCnqEu92Fr1MmYUtfCRc4EsA.woff2) format('woff2');
  unicode-range: U+0460-052F, U+1C80-1C88, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;
}
/* cyrillic */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 900;
  font-display: swap;
  src: local('Roboto Black'), local('Roboto-Black'), url(<?= $url; ?>fonts/roboto/v19/KFOlCnqEu92Fr1MmYUtfABc4EsA.woff2) format('woff2');
  unicode-range: U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;
}
/* greek-ext */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 900;
  font-display: swap;
  src: local('Roboto Black'), local('Roboto-Black'), url(<?= $url; ?>fonts/roboto/v19/KFOlCnqEu92Fr1MmYUtfCBc4EsA.woff2) format('woff2');
  unicode-range: U+1F00-1FFF;
}
/* greek */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 900;
  font-display: swap;
  src: local('Roboto Black'), local('Roboto-Black'), url(<?= $url; ?>fonts/roboto/v19/KFOlCnqEu92Fr1MmYUtfBxc4EsA.woff2) format('woff2');
  unicode-range: U+0370-03FF;
}
/* vietnamese */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 900;
  font-display: swap;
  src: local('Roboto Black'), local('Roboto-Black'), url(<?= $url; ?>fonts/roboto/v19/KFOlCnqEu92Fr1MmYUtfCxc4EsA.woff2) format('woff2');
  unicode-range: U+0102-0103, U+0110-0111, U+1EA0-1EF9, U+20AB;
}
/* latin-ext */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 900;
  font-display: swap;
  src: local('Roboto Black'), local('Roboto-Black'), url(<?= $url; ?>fonts/roboto/v19/KFOlCnqEu92Fr1MmYUtfChc4EsA.woff2) format('woff2');
  unicode-range: U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;
}
/* latin */
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 900;
  font-display: swap;
  src: local('Roboto Black'), local('Roboto-Black'), url(<?= $url; ?>fonts/roboto/v19/KFOlCnqEu92Fr1MmYUtfBBc4.woff2) format('woff2');
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}