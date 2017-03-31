<?php
	use App\helpers\util;
	//$ruta_logo = 'http://'.util::obtener_url().'assets/img/logo.png';
	$ruta_logo = util::obtener_url_fija().'assets/img/logo.png';
	$rut_firma = util::obtener_url_fija().'files_uploaded/firmas/'.$alumno['pro_logo'];
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Informe de notas</title>
		{!! Html::style('/assets/css/pdf.css') !!}
	</head>
	<body>
		<header>
			<div class="cabezara">
				<table style="width: 100%;">
					<tr>
						<td style="width: 20%;">
							<div id="logo">
							 	<img src="<?php echo $ruta_logo;?>">
							</div>
						<td>
						<td style="width: 40%;">
							&nbsp;
						<td>
						<td style="width: 40%;">
							<div>
								<div><strong>Liceo Brigida Walker</strong></div>
								<div>San Eugenio 1100, &Ntilde;u&ntilde;oa Regi&oacute;n</div>
								<div>Metropolitana, Chile</div>
								<div>(+56 2) 238 75 42 - (+56 2) 238 75 43</div>
								<div><a href="mailto:liceobrigidawalker@gmail.com">liceobrigidawalker@gmail.com</a></div>
				      		</div>
						<td>
					</tr>
				</table>
			</div>
		</header>
		<main>
			<div class="cuerpo">
				<table class="encabezado" style="width: 100%;">
					<tr>
						<td class="arriba">
							INFORME DE NOTAS PARCIALES
						</td>
					</tr>
					<tr>
						<td class="abajo">
							{{ $anyo_vigente }}
						</td>
					</tr>
					<tr>
						<td>
							&nbsp;
						</td>
					</tr>
				</table>
				<table class="identificacion" style="width: 100%;">
					<tr>
						<td class="titulo" style="width: 20%">
							Curso:
						</td>
						<td style="width: 40%">
							{{ $alumno['curso'] }}
						</td>
						<td class="titulo" style="width: 20%">
							Numero de lista:
						</td>
						<td style="width: 20%">
							{{ $alumno['numero_lista'] }}
						</td>
					</tr>
					<tr>
						<td class="titulo" style="width: 20%">
							Nombre:
						</td>
						<td style="width: 40%">
							{{ $alumno['nombre'] }} {{ $alumno['apellido_paterno'] }} {{ $alumno['apellido_materno'] }}
						</td>
						<td class="titulo" style="width: 20%">
							Run:
						</td>
						<td style="width: 20%">
							{{ $alumno['rut_alumno'] }}
						</td>
					</tr>
				</table>
				<table class="identificacion" style="width: 100%;">
					<tr>
						<td class="titulo" style="width: 20%">
							Profesor Jefe:
						</td>
						<td style="width: 80%">
							{{ $alumno['profesor'] }}
						</td>
					</tr>
					<tr>
						<td class="titulo" style="width: 20%">
							Horario de atenci&oacute;n:
						</td>
						<td style="width: 80%">
							{{ $alumno['pro_horario'] }}
						</td>
					</tr>
				</table>
				<div class="row">
					&nbsp;
				</div>
				{!! $mostrar !!}
			</div>
			<div class="firma">
				<table class="firmante">
					<tr>
						<td style="width: 80%">
							<img src="<?php echo $rut_firma;?>" width="100px" height="100px"> 
						</td>
					</tr>
					<tr>
						<td class="linea_firma" style="width: 20%">
							Profesor Jefe
						</td>
					</tr>
				</table>
			</div>
		</main>
		<footer>
			Fecha: {{ $date }}
		</footer>
	</body>
</html>
