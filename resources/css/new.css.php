<?php

use Webos\FrontEnd\CSS\StylesManager;

$sm = StylesManager::Instance();

$sm->addPalette('white', '#ffffff');
$sm->addPalette('black', '#000000');
$sm->defineColor('blue',  '#3344cc');
$sm->defineColor('red',   '#663333');
$sm->defineColor('green', '#339933');
$sm->defineColor('paper', '#666666', '#ffffff');
$sm->defineColor('gray',  '#cdcdcd', '#ffffff');
$sm->defineColor('darkgray',  '#666666', '#ffffff');

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
	'border-top' => 'none',
	'border-left' => 'none',
	'border-right' => 'none',
	'border-bottom' => 'solid 1px ' . $sm->getPalette('gray'),
	'background' => $sm->getPalette('white'),
])->import('box');

$sm->addRule('.LabelControl', [
	'font-weight' => '600',
	'font-size' => '13px',
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

$sm->addRule('.Button', ['border-radius'=>'2px'])->import('blue')->invert()->import('noborder', 'box', 'click');
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
$sm->addRule('.Window')->import('paper');
$sm->addRule('.Window .form-titlebar .title')->import('darkgray');
$sm->addRule('.Window .form-titlebar .controls *')->import('darkgray')->important('background');



// $sm->addRule('.FilePicker')->like('.Button');

$sm->addRule('.MenuItem:hover', [
	'cursor' => 'pointer',
])->import('blue')->invert()->important('background');

$sm->addRule('.MenuItem.selected')->like('.MenuItem:hover');

$sm->addRule('.MenuButton:hover > .text')->import('blue')->invert()->important('background');
$sm->addRule('.MenuButton.selected > .text')->import('blue')->invert()->important('background');

$sm->addRule('.MultiTab > .Tabs', [
	'border-bottom' => 'solid 1px ' . $sm->getPalette('green'),
]);

$sm->addRule('.MultiTab > .Tabs > .tab', [
	'color'         => $sm->getPalette('black'),
]);
$sm->addRule('.MultiTab > .Tabs > .tab.selected', [
	'color'         => $sm->getPalette('green'),
	'border-bottom' => 'solid 2px ' . $sm->getPalette('green'),
]);
$sm->addRule('.MultiTab > .Tabs > .tab:hover', [
	// 'color' => $sm->getPalette('green'),
	'border-bottom' => 'solid 2px ' . $sm->getPalette('green'),
]);


echo $sm->getStyles(false);
