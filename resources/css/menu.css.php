<?php

use Webos\FrontEnd\CSS\StylesManager;
$sm = StylesManager::InstanceWithDefinitions();

// $sm->addRule('.');
$sm->addRule('.MenuBar',                    [ 'overflow'=>'visible', 'border-bottom'=>'solid 1px #ccc', 'background'=>'#f0f0f0', ]);
$sm->addRule('.MenuButton',                 [ 'display'=>'inline', 'float'=>'left', 'height'=>'25px', 'overflow'=>'hidden', 'font-size'=>'small',]);
$sm->addRule('.MenuButton > .text',         [ 'padding'=>'4px 10px', 'height' => '25px',]);
$sm->addRule('.MenuButton:hover .MenuList', [ 'display'=>'block',]);
$sm->addRule('.MenuList', [ 'position'=>'absolute', 'display'=>'block', 'z-index'=>1000, 'background'=>'#fff', 'border'=>'solid 1px #999', 'padding'=>'4px 0', 'box-shadow'=>'0 0 5px #999',]);

$sm->addRule('.MenuItem', [ 'padding'=>'0 3px',]);
$sm->addRule('.MenuItem:hover', [ 
	'background' => $sm->getPalette('gray2') . ' !important', 
	'color'      => '#fff !important', 
	'cursor'     => 'pointer',
]);
$sm->addRule('.MenuItem.selected',  [ 'background'=>'#006e6e !important', 'color'=>'#fff !important',]);
$sm->addRule('.MenuItem > .icon',   [ 'width' => '22px', 'display' => 'block',]);
$sm->addRule('.MenuItem > .text',   [ 'height'=>'25px',]);
$sm->addRule('.MenuItem > .arrow',  [ 'text-align'=> 'right', 'padding'=>'0 10px',]);
$sm->addRule('.MenuItem[disabled]', [ 'color'=> '#999',]);
$sm->addRule('.MenuItem[disabled]:hover', [ 'color'=> '#999 !important', 'background'=>'#fff !important',]);

$sm->addRule('.SeparatorMenuItem hr', [
	'border-bottom' => 'solid 1px #ccc',
    'border-top' => 'none',
    'border-left' => 'none',
    'border-right' => 'none',
    'margin' => '4px 0',
]);

$sm->addRule('.MenuItem:hover', [
	'cursor' => 'pointer',
])->import('hover');

$sm->addRule('.MenuItem.selected')->like('.MenuItem:hover');

$sm->addRule('.MenuButton:hover > .text', [ 'cursor'=>'pointer'])->import('hover');
$sm->addRule('.MenuButton.selected > .text')->import('hover');

echo $sm->getStyles();