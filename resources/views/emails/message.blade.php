<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Informe de notas</title>
		{!! Html::style('/assets/css/bootstrap.css') !!}
	</head>
	<body>
		<div class="container">
			<div class="row">
				<div class="col col-md-6 col-md-offset-3"   >
					<div class="panel panel-default">
						<div class="panel-heading"><h3 class="panel-title">Cuenta de Students</h3></div>
						<div class="panel-body">
							<h4>Hemos recibido una solicitud de cambio de contrase&ntilde;a.</h4>
							<h4>Su nueva contrase&ntilde;a es: {{ $password }}</h4>
							<h4>Usa esta nueva contrase&ntilde;a para restablecer la contraseña de la cuenta con el run: {{ $run }}.
							<h4>Gracias,</h4>
							<h4>{{ $colegio }}</h4>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>