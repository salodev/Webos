<?php

namespace Webos\Visual\BootstrapUI;
use Webos\Visual\Container;
use Webos\StringChar;
use Webos\Frontend\PageWrapper as Base;

class PageWrapper implements Base {
	
	private $_html = null;
	
	public function setContent(string $html): Base {
		$this->_html = $html;
		return $this;
	}
	
	public function getHTML(): string {

		$template = new StringChar(<<<HTML
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

    <title>__TITLE__</title>
  </head>
  <body>
    <div class="content">__CONTENT__</div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <!-- script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
				
	<script type="text/javascript" src="js/engines/actionEngine.js"></script>
	<script type="text/javascript" src="js/engines/eventEngine.js"></script>
	<script type="text/javascript" src="js/engines/directives.js"></script>
	<script type="text/javascript" src="js/Webos.js"></script>
	<script type="text/javascript" src="js/custom.js"></script>
	<script type="text/javascript" src="js/directives.js"></script>				
  </body>
</html>
HTML
);
		$template->replace('__TITLE__', 'title');
		$template->replace('__CONTENT__', $this->_html);
		
		return $template;
	}
}