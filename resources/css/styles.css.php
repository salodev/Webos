<?php
use Webos\Implementations\Service;
$url = Service::GetUrl();
?>
@font-face{
		font-family:'Glyphicons Halflings';
		src:url('<?php echo $url; ?>fonts/glyphicons-halflings-regular.eot');
		src:url('<?php echo $url; ?>fonts/glyphicons-halflings-regular.eot?#iefix') format('embedded-opentype'),
			url('<?php echo $url; ?>fonts/glyphicons-halflings-regular.woff') format('woff'),
			url('<?php echo $url; ?>fonts/glyphicons-halflings-regular.ttf') format('truetype'),
			url('<?php echo $url; ?>fonts/glyphicons-halflings-regular.svg#glyphicons_halflingsregular') format('svg')
}
@font-face{
		font-family:'Glyphicons Regular';
		src:url('<?php echo $url; ?>fonts/glyphicons-regular.eot');
		src:url('<?php echo $url; ?>fonts/glyphicons-regular.eot?#iefix') format('embedded-opentype'),
			url('<?php echo $url; ?>fonts/glyphicons-regular.woff') format('woff'),
			url('<?php echo $url; ?>fonts/glyphicons-regular.ttf') format('truetype'),
			url('<?php echo $url; ?>fonts/glyphicons-regular.svg#glyphiconsregular') format('svg');
		font-weight:normal;
		font-style:normal
}
* {
	box-sizing:border-box !important;
}
body{
    background-color:#f6f6f6;
    background-image:url('<?php echo $url; ?>img/bg.png');
    font-family:Helvetica;
    font-size:14px !important;
    margin:0;
}
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

.DropDown .icon:before {
	content:"\E114"
}

.Button {
	padding: 3px 8px;
}