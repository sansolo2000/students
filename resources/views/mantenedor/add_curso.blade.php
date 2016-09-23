<?php 
use App\models\colegio;

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
	
	$.get("../validar_curso/"+cur_numero+"/"+cur_letra+"/"+niv_nombre+"", function(response,state){
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
		
};


	$(document).ready(function() {
		$.get("../profesores_asignado", function(response,state){
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
					$("#pro_nombre").append("<option value='"+response[i].id+"'>"+response[i].name+"</option>");
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
				'pro_nombre'			:	{required: true, min:1},
			},
				messages: {
				'niv_nombre'			: { min: 'Seleccione nivel' },
				'pro_nombre'			: { min: 'Seleccione profesor' }
			},
		});

	});
	
	
</script>

<div class="{{$entidad['clase']}}">
		<fieldset>
			<?php 
				$controller = $entidad['controller'].'.store';
			?>
			{{ Form::open(['route' => $controller, 'method' => 'post', 'files' => true, 'class' => 'form-horizontal', 'id' => 'myform', 'name' =>'myform']) }}
				<legend>{{ $title}}</legend>
					<div class="form-group col-sm-12">
						<div class="col-sm-2">
							<label for="curso" class="control-label">Curso:</label>
						</div>
						<div class="col-sm-10">
							<input class="form-control" id="col_nombre" name="col_nombre" value="{{ $col_nombre }}" disabled="disabled" type="text">
						</div>
					</div>
					<div class="form-group col-sm-12">
						<div class="col-sm-2">
							<label for="curso" class="control-label">Curso:</label>
						</div>
						<div class="col-sm-3">
							<input class="form-control" id="cur_numero" name="cur_numero" placeholder="N&uacute;mero" type="text">
						</div>
						<div class="col-sm-3">
							<input class="form-control" id="cur_letra" name="cur_letra" placeholder="Letra" type="text">
						</div>
						<div class="col-sm-4">
							{{ Form::select('niv_nombre', $niv_nombre, -1, ['id' => 'niv_nombre', 'class' => 'form-control', 'name' => 'niv_nombre' ]) }}								
						</div>
					</div>
					<div class="form-group col-sm-12">
						<div class="col-sm-2">
							<label for="curso" class="control-label">Profesor Jefe:</label>
						</div>
						<div class="col-sm-10">
							<select class="form-control"  id="pro_nombre" name="pro_nombre">
							<!-- Dropdown List Option -->
							</select>
						</div>
					</div>
					<div class="form-group col-sm-12">
						<div class="col-sm-2">
							<label for="curso" class="control-label">Activo:</label>
						</div>
						<div class="col-sm-10">
							<input type="checkbox" id="cur_activo" name="cur_activo" >
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