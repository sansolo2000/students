<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $NamePage }}</title>
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>

{!! Html::style('assets/css/bootstrap.css') !!}
<!-- Scripts -->
{!! Html::script('assets/js/bootstrap.min.js') !!}
{!! Html::script('assets/js/jquery.Rut.js') !!}
{!! Html::script('assets/js/jquery.validate.js') !!}
{!! Html::script('assets/js/additional-methods.js') !!}
{!! Html::script('assets/js/messages_es.js') !!}


<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.9/css/bootstrap-dialog.min.css" rel="stylesheet" type="text/css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.9/js/bootstrap-dialog.min.js"></script>

<!-- Fonts -->
<link href='//fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>


<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<body>

	<div class="container">

		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="/students/public/main">{{ $NamePage }}</a>
			</div>
			<div id="navbar" class="navbar-collapse collapse">
			</div><!--/.navbar-collapse -->
		</div>

		<div class="container">
			<div class="row">
				<nav class="navbar navbar-default">
					<div class="container-fluid">
				    	<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
							{!! $menu !!}
						</div>
				  	</div>
				</nav>
			</div>
		</div>
	</div>
<div class="container">
	@yield('content')
</div>

	<div class="container fill">
		<div class="panelpanel-default">
			<div class="panel-body">
				<div class="col-md-10 text-primary">
			    	Direci&oacute;n: {!! $Direccion !!}
			    </div>
				<div class="col-md-2">
					<a href="http://validator.w3.org/check?uri=referer" rel="external" style="line-height: 1.3em; font-style: normal;" title="HTML versión 5">HTML 5</a>
					<span style="line-height: 1.538em; font-style: normal;">&nbsp;|&nbsp;</span>
					<a href="http://jigsaw.w3.org/css-validator/check/referer/?profile=css3" rel="external" style="line-height: 1.3em; font-style: normal;" title="CSS versión 3">CSS 3</a>
					<span style="line-height: 1.538em; font-style: normal;">&nbsp;|</span>
					<a href="http://creativecommons.org/licenses/by/2.0/cl/" rel="external" style="line-height: 1.3em; font-style: normal;" title="Creative Commons">CC</a>
				</div>
			</div>
		</div>
	</div>



</body>
</html>