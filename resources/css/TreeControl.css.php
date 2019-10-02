<?php

use Webos\FrontEnd\CSS\StylesManager;

$sm = StylesManager::InstanceWithDefinitions();
?>
.Tree {
	/*border: solid 1px #ccc;*/
    margin: 0;
	padding: 0;
    position: absolute !important;
    background: #fff;
	overflow-x:auto;
	top:0;left:0;right:0;bottom:0;
}

.TreeNode {
	position:relative !important;
	list-style:none;
	cursor:default;
}
.TreeNode > .row {
	overflow:auto;
}
<?php
$sm->addRule('.TreeNode > .row:hover', [
	'cursor' => 'pointer',
])->import('hover');
$sm->addRule('.TreeNode > .row.selected')->import('hover');
echo $sm->getStyles();
?>
.TreeNode > .row .title {
	float:left;
	padding:3px 6px;
}
.TreeNode > .row .columns {
    overflow: auto;
    float: right;
}
.TreeNode > .row .column {
	float:left;
	padding: 0 5px;
}
.TreeNode > .row > .toggle {
    cursor: pointer;
    background: #fff;
    display: block;
    float: left;
    height: 13px;
    width: 13px;
    border: solid 1px #000;
    text-align: center;
    padding: 0 !important;
    line-height: 11px;
    margin: 5px;
    color: #000;
    border-radius: 2px;
    font-size: 7px;
	font-family:'Glyphicons Halflings'
}

.TreeNode > .row > .toggle.none {
	background:none;
	border:none;
	width: 7px;
}

.TreeNode > .row > .toggle.expand:before {
	content:"\002b";
}
.TreeNode > .row > .toggle.collapse:before {
	content:"\2212";
}

.TreeNode ul {
	padding: 0 !important;
	margin: 0 0 0 18px;
}