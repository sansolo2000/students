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



	@yield('content')

</body>
</html>