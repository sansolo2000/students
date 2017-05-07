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
					$("#pro_nombre").append("<option value='"+response[i].id+"'>"+response[i].name+"</option>");
				}
				console.log(profesores);
				$("#pro_nombre").select2({
//					  data: profesores
				});
			}
		});
		$.get("../../asignatura_asignado/"+{{ $curso->cur_numero }}+"/"+{{ $curso->cur_codigo }}, function(response,state){
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
					$("#asg_nombre").append("<option value='"+response[i].id+"'>"+response[i].name+"</option>");
				}
				console.log(profesores);
				$("#asg_nombre").select2({
//					  data: profesores
				});
			}
		});
	});

	
	$().ready(function () {
		$('#myform').validate({
			rules: {
				'asg_nombre'			:	{required: true, min:1},
				'pro_nombre'			:	{required: true, min:1},
			},
				messages: {
				'asg_nombre'			: { min: 'Seleccione asignatura' },
				'pro_nombre'			: { min: 'Seleccione profesor' }
			},
		});

	});
	
</script>

		<div class="{{$entidad['clase']}}">
			<div class="panel panel-primary" >
				<div class="panel-heading">
		    		<h3 class="panel-title">{{ $title }}</h3>
		  		</div>
		  		<div class="panel-body">
					<div class="col-sm-12">
						<input class="form-control" id="cur_nombre" name="cur_nombre" value="{{ $curso->name }}" type="text" disabled="disabled">
			  		</div>
					<div class="col-sm-12">
						&nbsp:
			  		</div>
					<div class="form-group col-sm-12">
						<fieldset>
							<?php 
								$controller = $entidad['controller'].'.store';
							?>
							{{ Form::open(['route' => $controller, 'method' => 'post', 'files' => true, 'class' => 'form-horizontal', 'id' => 'myform', 'name' =>'myform']) }}
									<div class="form-group col-sm-12">
										<div class="col-sm-4">
											<label for="curso" class="control-label">Asignatura:</label>
										</div>
										<div class="col-sm-8">
											<select class="form-control"  id="asg_nombre" name="asg_nombre">
											<!-- Dropdown List Option -->
											</select>
											<input class="form-control" id="cur_codigo" name="cur_codigo" value="{{ $curso->cur_codigo }}" type="hidden">
										</div>
									</div>
									<div class="form-group col-sm-12">
										<div class="col-sm-4">
											<label for="curso" class="control-label">Profesor:</label>
										</div>
										<div class="col-sm-8">
											<select class="form-control"  id="pro_nombre" name="pro_nombre">
											<!-- Dropdown List Option -->
											</select>
										</div>
									</div>
									<div class="col-sm-6 col-sm-offset-3" style="text-align: center;">
										<?php 
											$controller = $entidad['controller'];
										?>
										<a href="../<?php echo $controller ?>" class="btn btn-default">
												<span>Volver</span>
										</a>
									    {{ Form::button('Guardar', array('class'=>'btn btn-default', 'type'=>'submit')) }}    
										
									</div>
							{!! Form::close() !!}
						</fieldset>
			  		</div>
		  		</div>
			</div>
		</div>

@endsection