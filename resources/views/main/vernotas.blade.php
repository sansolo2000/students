<?php 
use App\models\colegio;
use App\models\modulo_asignado;
use App\helpers\util;
use App\models\permiso_especial;
use App\models\curso;

$colegios = Colegio::select()
			->join('comunas', 'colegios.com_codigo', '=', 'comunas.com_codigo')
			->join('regiones', 'regiones.reg_codigo', '=', 'comunas.reg_codigo')
			->where('colegios.col_activo', '=', 1)
			->first();
if (empty($colegios)){
	$colegios['col_nombre'] = 'Sin definir';
	$colegios['col_direccion'] = 'Sin definir';
	$colegios['com_nombre'] = 'Sin definir';
	$colegios['Fono'] = 'Sin definir';
}
else {
	$colegios['Fono'] = 'Sin definir';
}
?>
@extends('mantenedor.base',[
					'NamePage' => ':: Proyect Students - '.$colegios['col_nombre'].' ::', 
					'Direccion' => $colegios['col_direccion']. ', '. $colegios['com_nombre']
							])


@section('content')

	<script type="text/javascript">
		
	</script>


<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet" />
{!! Html::style('assets/css/personal.css') !!}

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js"></script>
{!! Html::script('assets/js/jquery-validate.bootstrap-tooltip.js') !!}
{!! Html::script('assets/js/dropdown.js') !!}


	<div class="{{ $entidad['clase'] }}">
		<div class="col-sm-6 col-sm-offset-3">
			<div class="panel panel-primary" >
				<div class="panel-heading">
		    		<h3 class="panel-title">{{ $entidad['Nombre'] }}</h3>
		  		</div>
		  		<div class="panel-body">
					<table class="table table-striped table-hover ">
						<thead>
							<tr>
								<th>Curso</th>
								<th>Asignatura</th>
								<th>Acci&oacute;n</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>1</td>
								<td>Column content</td>
								<td>Column content</td>
							</tr>
						</tbody>
					</table> 
		  		</div>
			</div>
		</div>
	</div>
@endsection