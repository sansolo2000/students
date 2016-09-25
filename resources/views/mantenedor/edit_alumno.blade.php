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
	$(function() {
		$("#per_rut_alu").focus();
	});		
	$(document).ready(function() {
		$("#per_rut_alu").change(function(event){
			$.get("../../alumno_matriculado/"+event.target.value+"", function(response,state){
				if (response.length > 0){
					BootstrapDialog.alert({
						title: 'Error',
						message: 'Alumno esta matriculado en el curso '+response[0].curso,
						type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
						closable: true, // <-- Default value is false
						draggable: true, // <-- Default value is false
						buttonLabel: 'Volver', // <-- Default value is 'OK',
					});
					$("#per_rut_alu").val('');			
					$("#per_nombre").val('');			
					$("#per_nombre_segundo").val('');			
					$("#per_apellido_paterno").val('');			
					$("#per_apellido_materno").val('');			
					$("#per_email").val('');
					$("#per_rut_alu").focus();			
				}
				else{
					$("#per_nombre").focus();
				}
			});
		});
		var email = '{{ $record['per_email'] }}';
		if (email.search('@') > 0){
			$('#per_email').prop('disabled', false);
			$('#per_email').val(email);
			$('#dat_adicionales').prop('checked', true);
		}
		$('#dat_adicionales').change(function(event){
			if (!$('#dat_adicionales').is(':checked')){
				$('#per_email').prop('disabled', true);
			}
			else {
				$('#per_email').prop('disabled', false);
			}
		});

		$('#per_rut_alu').Rut({
			  on_error: function(){ 
				  BootstrapDialog.alert({
			            title: 'Error',
			            message: 'El RUN ingresado es incorrecto!!',
			            type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
			            closable: true, // <-- Default value is false
			            draggable: true, // <-- Default value is false
			            buttonLabel: 'Volver', // <-- Default value is 'OK',
			            //callback: function(result) {
			                // result will be true if button was click, while it will be false if users close the dialog directly.
			                //alert('Result is: ' + result);
			            //}
			        });
					console.log('prueba');
				}
			});

	});

	$().ready(function () {
		$('#myform').validate({
			rules: {
				'alu_numero'			:	{required: true, number: true},
				'per_rut'				:	{required: true, minlength: 8, maxlength: 12},
				'per_nombre'			:	{required: true, minlength: 3, maxlength: 50},
				'per_apellido_paterno'	:	{required: true, minlength: 3, maxlength: 50},
				'per_apellido_materno'	:	{required: true, minlength: 3, maxlength: 50},
				'per_email'				:	{required: true, email: true, minlength: 5, maxlength: 50}
			}
		});

	});
	
</script>

		<div class="{{$entidad['clase']}}">
			<div class="panel panel-primary" >
				<div class="panel-heading">
		    		<h3 class="panel-title">Curso</h3>
		  		</div>
		  		<div class="panel-body">
					<div class="col-sm-12">
						<div class="col-sm-3">
				  		</div>
						<div class="col-sm-6">
							<input class="form-control" id="cur_nombre" name="cur_nombre" value="{{ $curso['name'] }}" type="text" disabled="disabled">
				  		</div>
						<div class="col-sm-3">
				  		</div>
			  		</div>
					<div class="col-sm-12">
						&nbsp;
			  		</div>
					<div class="col-sm-12">
						<fieldset>
							<?php 
								$controller = $entidad['controller'].'.update';
							?>
							{{ Form::model($record, array('route' => array($controller, $record[$entidad['pk']]), 'method' => 'PUT', 'files' => true, 'class' => 'form-horizontal', 'id' => 'myform', 'name' =>'myform')) }}
							<div class="panel panel-primary" >
								<div class="panel-heading">
						    		<h3 class="panel-title">{{ $title}}</h3>
						  		</div>
						  		<div class="panel-body">
									<div class="form-group col-sm-12">
										<div class="col-sm-1">
											<label for="curso" class="control-label">Numero:</label>
										</div>
										<div class="col-sm-2">
											<input class="form-control" id="alu_numero" name="alu_numero" type="text" value="{{ $record['alu_numero'] }}" disabled="disabled">
											<input class="form-control" id="cur_codigo" name="cur_codigo" value="{{ $curso['cur_codigo'] }}" type="hidden">
										</div>
										<div class="col-sm-1">
											<label for="curso" class="control-label">Rut:</label>
										</div>
										<div class="col-sm-3">
											<input class="form-control" id="per_rut_alu" name="per_rut_alu" type="text" value="{{ $rut }}" disabled="disabled">
										</div>
										<div class="col-sm-5">
										</div>
									</div>
									<div class="form-group col-sm-12">
										<div class="col-sm-1">
											<label for="curso" class="control-label">Nombre:</label>
										</div>
										<div class="col-sm-3">
											<input class="form-control" id="per_nombre" name="per_nombre" value="{{ $record['per_nombre'] }}" type="text">
										</div>
										<div class="col-sm-2">
											<input class="form-control" id="per_nombre_segundo" name="per_nombre_segundo" value="{{ $record['per_nombre_segundo'] }}" type="text">
										</div>
										<div class="col-sm-3">
											<input class="form-control" id="per_apellido_paterno" name="per_apellido_paterno" value="{{ $record['per_apellido_paterno'] }}" type="text">
										</div>
										<div class="col-sm-3">
											<input class="form-control" id="per_apellido_materno" name="per_apellido_materno" value="{{ $record['per_apellido_materno'] }}" type="text">
										</div>
									</div>
								</div>
								<div class="panel-footer">
									<label for="curso" class="control-label">Datos Adicionales:</label>
									<input type="checkbox" id="dat_adicionales" name="dat_adicionales" >
								</div>
						  		<div class="panel-body">
									<div class="form-group col-sm-12">
										<div class="col-sm-2">
											<label for="curso" class="control-label">E-Mail:</label>
										</div>
										<div class="col-sm-8">
											<input class="form-control" id="per_email" name="per_email" type="text" placeholder="E-Mail" disabled="disabled">
										</div>
										<div class="col-sm-2">
										</div>
									</div>
								</div>
						  		<div class="panel-body">
									<div class="col-sm-6 col-sm-offset-3" style="text-align: center;">
										<?php 
											$controller = $entidad['controller'];
										?>
										<a href="../<?php echo $controller ?>" class="btn btn-default">
												<span>Volver</span>
										</a>
									    {{ Form::button('Guardar', array('class'=>'btn btn-default', 'type'=>'submit')) }}    
										
									</div>
								</div>
							{!! Form::close() !!}
						</fieldset>
			  		</div>
		  		</div>
			</div>
</div>

@endsection