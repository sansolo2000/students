<?php 
use App\models\colegio;
use App\helpers\util;


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

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js"></script>
{!! Html::script('assets/js/jquery-validate.bootstrap-tooltip.js') !!}
{!! Html::script('assets/js/dropdown.js') !!}

<script type="text/javascript">
	function validar_curso(){
		var cur_numero = $('#cur_numero').val();
		var cur_letra = $('#cur_letra').val();
		var niv_nombre = $('#niv_nombre').val();
		var hid_cur_numero = $('#hid_cur_numero').val();
		var hid_cur_letra = $('#hid_cur_letra').val();
		var hid_niv_nombre = $('#hid_niv_nombre').val();
		if ((hid_cur_numero == cur_numero) && (cur_letra == hid_cur_letra) && (niv_nombre == hid_niv_nombre)){
			$('#myform').submit();
		}
		else {	
			$.get("../../validar_curso/"+cur_numero+"/"+cur_letra+"/"+niv_nombre+"", function(response,state){
				if (response.length == 0){
					$('#myform').submit();
					//console.log(response);
				}
				else {
					BootstrapDialog.alert({
						title: 'Error',
						message: 'Curso ya se encuentra registrado',
						type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
						closable: true, // <-- Default value is false
						draggable: true, // <-- Default value is false
						buttonLabel: 'Volver', // <-- Default value is 'OK',
					});
				}
			});
		}			
	};


	$(document).ready(function() {
		$.get("../../profesores_asignado", function(response,state){
			if (response.length == 0){
				BootstrapDialog.alert({
					title: 'Error',
					message: 'No existen profesores para asignar',
					type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
					closable: true, // <-- Default value is false
					draggable: true, // <-- Default value is false
					buttonLabel: 'Volver', // <-- Default value is 'OK',
				});
			}
			else{
//				console.log(response);
				var profesores = []; 
				for (i=0; i<response.length; i++){
					if (response[i].id == {!! $record['per_rut'] !!}){
						$("#pro_nombre").append("<option value='"+response[i].id+"' selected>"+response[i].name+"</option>");
					}
					else{
						$("#pro_nombre").append("<option value='"+response[i].id+"'>"+response[i].name+"</option>");
					}
				}
				console.log(profesores);
				$("#pro_nombre").select2({
//					  data: profesores
				});
			}
		});
	});

	$().ready(function () {
		$('#myform').validate({
			rules: {
				'cur_numero'			:	{required: true, number: true},
				'cur_letra'				:	{required: true, minlength: 1, maxlength: 1},
				'niv_nombre'			:	{required: true, min:1},
				'any_numero'			:	{required: true, min:1},
				'pro_nombre'			:	{required: true, min:1},
			},
				messages: {
				'niv_nombre'			: { min: 'Seleccione nivel' },
				'any_numero'			: { min: 'Seleccione a\u00f1o' },
				'pro_nombre'			: { min: 'Seleccione profesor' }
			},
		});

	});
	
</script>

<div class="{{$entidad['clase']}}">
		<fieldset>

			<?php 
				$controller = $entidad['controller'].'.update';
			?>

			{{ Form::model($record, array('route' => array($controller, $record[$entidad['pk']]), 'method' => 'PUT', 'files' => true, 'class' => 'form-horizontal', 'id' => 'myform', 'name' =>'myform')) }}
				<legend>{{ $title}}</legend>
					<div class="form-group col-sm-12">
						<div class="col-sm-3">
							<label for="curso" class="control-label">Curso:</label>
						</div>
						<div class="col-sm-9">
							<input class="form-control" id="col_nombre" name="col_nombre" value="{{ $col_nombre }}" disabled="disabled" type="text">
						</div>
					</div>
					<div class="form-group col-sm-12">
						<div class="col-sm-3">
							<label for="curso" class="control-label">Curso:</label>
						</div>
						<div class="col-sm-3">
							<input class="form-control" id="cur_numero" name="cur_numero" value="{{ $record['cur_numero'] }}" placeholder="N&uacute;mero" type="text">
							<input id="hid_cur_numero" name="hid_cur_numero" value="{{ $record['cur_numero'] }}" type="hidden">
						</div>
						<div class="col-sm-3">
							<input class="form-control" id="cur_letra" name="cur_letra" value="{{ $record['cur_letra'] }}" placeholder="Letra" type="text">
							<input id="hid_cur_letra" name="hid_cur_letra" value="{{ $record['cur_letra'] }}" type="hidden">
						</div>
						<div class="col-sm-3">
							{{ Form::select('niv_nombre', $niv_nombre, $record['niv_codigo'], ['id' => 'niv_nombre', 'class' => 'form-control', 'name' => 'niv_nombre' ]) }}								
							<input id="hid_niv_nombre" name="hid_niv_nombre" value="{{ $record['niv_codigo'] }}" type="hidden">
						</div>
					</div>
					<div class="form-group col-sm-12">
						<div class="col-sm-3">
							<label for="curso" class="control-label">Profesor:</label>
						</div>
						<div class="col-sm-9">
							<select class="form-control"  id="pro_nombre" name="pro_nombre">
							<!-- Dropdown List Option -->
							</select>
						</div>
					</div>
					<div class="form-group col-sm-12">
						<div class="col-sm-3">
							<label for="curso" class="control-label">A&ntilde;o:</label>
						</div>
						<div class="col-sm-9">
							{{ Form::select('any_numero', $any_numero, $record['any_codigo'], ['id' => 'any_numero', 'class' => 'form-control', 'name' => 'any_numero' ]) }}								
						</div>
					</div>
					<div class="form-group col-sm-12">
						<div class="col-sm-2">
							<label for="curso" class="control-label">Activo:</label>
						</div>
						<div class="col-sm-10">
								<?php
									if ($record['cur_activo'] == 1){
										$check = 'checked';
									}
									else {
										$check ='';
									}
								?>
							<input type="checkbox" id="cur_activo" name="cur_activo" <?php echo $check; ?>>
						</div>
					</div>
								
					<div class="col-sm-6 col-sm-offset-3">
						<?php 
							$controller = $entidad['controller'];
						?>
						<a href="../<?php echo $controller ?>" class="btn btn-default">
								<span>Volver</span>
						</a>
					    {{ Form::button('Guardar', array('class'=>'btn btn-default', 'type'=>'button', 'onclick' => "validar_curso(); return false;")) }}    
						
					</div>
			{!! Form::close() !!}
		</fieldset>
</div>

@endsection