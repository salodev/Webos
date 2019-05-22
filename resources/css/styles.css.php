<?php
use Webos\Implementations\Service;
$url = Service::GetUrl();
?>
.no-break { overflow: hidden;  /*text-overflow: ellipsis;*/ white-space: nowrap; }

.app-title{
    font-size:35px;
    color:#999;
    padding:0 10px;
}

.Controls.Icon {
	font-family:'Glyphicons Halflings';
}
.Controls.Icon.refresh:before {
	content: '\E031';
}

input[type=button],button,input[type=text],input[type=password],select,.LabelControl {
	overflow:hidden;
    height: 25px;
    padding: 3px 0;
    margin: 0 5px 0 0;
	color:#444;
}
input[type=text], input[type=password] { padding:3px 8px; }