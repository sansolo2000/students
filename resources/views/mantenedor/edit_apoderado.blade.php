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
	$(document).ready(function() {

		$("#apo_rut").change(function(event){
			$.get("../../alumno_apoderado/"+event.target.value+"", function(response,state){
				if (response.length > 0){
					if (response[0].profesor != null){
						BootstrapDialog.alert({
							title: 'Error',
							message: 'El Rut esta ingresado como Profesor',
							type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
							closable: true, // <-- Default value is false
							draggable: true, // <-- Default value is false
							buttonLabel: 'Volver', // <-- Default value is 'OK',
						});
						$("#apo_rut").val('');			
						$("#apo_nombre").val('');			
						$("#apo_apellido_paterno").val('');			
						$("#apo_apellido_materno").val('');			
						$("#apo_email").val('');
						$("#apo_fono").val('');
						$("#apo_rut").focus();			
					}	
					if (response[0].alumno != null){
						BootstrapDialog.alert({
							title: 'Error',
							message: 'Alumno esta matriculado en el curso '+response[0].curso,
							type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
							closable: true, // <-- Default value is false
							draggable: true, // <-- Default value is false
							buttonLabel: 'Volver', // <-- Default value is 'OK',
						});
						$("#apo_rut").val('');			
						$("#apo_nombre").val('');			
						$("#apo_apellido_paterno").val('');			
						$("#apo_apellido_materno").val('');			
						$("#apo_email").val('');
						$("#apo_fono").val('');
						$("#apo_rut").focus();			
					}
				}
				else{
					$("#apo_nombre").focus();
				}
			});
		});
		$("#apo_rut").change(function(event){
			$.get("../../apoderado/"+event.target.value+"", function(response,state){
				if (response.length > 0){
					$("#apo_nombre").val(response[0]['per_nombre']);			
					$("#apo_apellido_paterno").val(response[0]['per_apellido_paterno']);			
					$("#apo_apellido_materno").val(response[0]['per_apellido_materno']);			
					$("#apo_email").val(response[0]['per_email']);
					$("#apo_fono").val(response[0]['apo_fono']);	
					var email = response[0]['per_email']; 
					if (email.search('@') > 0){
						$('#apo_email').prop('disabled', false);
						$('#apo_email').val(email);
						$('#dat_adicionales').prop('checked', true);
					}
									
				}
			});
		});	

		var apo_rut = '{{ $record["apo_rut"] }}';
		if (apo_rut == ""){
			$('#apo_rut').prop('disabled', false);
			$('#apo_rut').attr("placeholder", "Rut");
			$("#apo_nombre").attr("placeholder", "Nombre");			
			$("#apo_apellido_paterno").attr("placeholder", "Apellido Paterno");			
			$("#apo_apellido_materno").attr("placeholder", "Apellido Materno");			
			$("#apo_email").attr("placeholder", "E-Mail");
			$("#apo_fono").attr("placeholder", "Fono");			
			$('#apo_rut').val("");
			$('#apo_rut').focus();
		}	

		var email = '{{ $record["apo_email"] }}';
		if (email.search('@') > 0){
			$('#apo_email').prop('disabled', false);
			$('#apo_email').val(email);
			$('#dat_adicionales').prop('checked', true);
		}
		$('#dat_adicionales').change(function(event){
			if (!$('#dat_adicionales').is(':checked')){
				$('#apo_email').prop('disabled', true);
			}
			else {
				$('#apo_email').prop('disabled', false);
			}
		});		
		if (!$('#dat_password').is(':checked')){
			$('#apo_password').prop('disabled', true);
			$('#apo_password_re').prop('disabled', true);
		}
		$('#dat_password').change(function(event){
			if (!$('#dat_password').is(':checked')){
				$('#apo_password').prop('disabled', true);
				$('#apo_password_re').prop('disabled', true);
			}
			else {
				$('#apo_password').prop('disabled', false);
				$('#apo_password_re').prop('disabled', false);
			}
		});
	});

	$().ready(function () {
		$('#myform').validate({
			rules: {
				'apo_rut'				:	{required: true, minlength: 10, maxlength: 12},
				'apo_nombre'			:	{required: true, minlength: 3, maxlength: 50},
				'apo_apellido_paterno'	:	{required: true, minlength: 3, maxlength: 50},
				'apo_apellido_materno'	:	{required: true, minlength: 3, maxlength: 50},
				'apo_email'				:	{required: true, email: true, minlength: 5, maxlength: 50},
				'apo_fono'				:	{required: false, number: true},
				'apo_password'			:	{required: true, minlength: 2, maxlength: 15},
				'apo_password_re'		:	{required: true, minlength: 2, maxlength: 15, equalTo : '#apo_password'}
				
			}
		});
		$('#apo_rut').Rut({
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
				}
			});
	});
	
</script>

		<div class="{{$entidad['clase']}}">
			<div class="panel panel-primary" >
				<div class="panel-heading">
		    		<h3 class="panel-title">{{ $title}}</h3>
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
						    		<h3 class="panel-title">Alumno</h3>
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
											<?php
												$mostrar = util::format_rut($record->alu_rut, $record->alu_dv);
												$mostrar = $mostrar['numero'].'-'.$mostrar['dv'];
											?>
											<input class="form-control" id="alu_rut" name="alu_rut" type="text" value="{{ $mostrar }}" disabled="disabled">
										</div>
										<div class="col-sm-5">
										</div>
									</div>
									<div class="form-group col-sm-12">
										<div class="col-sm-1">
											<label for="curso" class="control-label">Nombre:</label>
										</div>
										<div class="col-sm-3">
											<input class="form-control" id="alu_nombre" name="alu_nombre" value="{{ $record['alu_nombre'] }}" type="text" disabled="disabled">
										</div>
										<div class="col-sm-3">
											<input class="form-control" id="alu_apellido_paterno" name="alu_apellido_paterno" value="{{ $record['alu_apellido_paterno'] }}" type="text" disabled="disabled">
										</div>
										<div class="col-sm-3">
											<input class="form-control" id="alu_apellido_materno" name="alu_apellido_materno" value="{{ $record['alu_apellido_materno'] }}" type="text" disabled="disabled">
										</div>
									</div>
								</div>
								<div class="panel-heading">
						    		<h3 class="panel-title">Apoderado</h3>
						  		</div>
						  		<div class="panel-body">
									<div class="form-group col-sm-12">
										<div class="col-sm-1">
											<label for="curso" class="control-label">Rut:</label>
										</div>
										<div class="col-sm-3">
											<?php
												$mostrar = util::format_rut($record->apo_rut, $record->apo_dv);
												$mostrar = $mostrar['numero'].'-'.$mostrar['dv'];
											?>
											<input class="form-control" id="apo_rut" name="apo_rut" type="text" value="{{ $mostrar }}" disabled="disabled">
										</div>
										<div class="col-sm-5">
										</div>
									</div>
									<div class="form-group col-sm-12">
										<div class="col-sm-1">
											<label for="curso" class="control-label">Nombre:</label>
										</div>
										<div class="col-sm-3">
											<input class="form-control" id="apo_nombre" name="apo_nombre" value="{{ $record['apo_nombre'] }}" type="text">
										</div>
										<div class="col-sm-3">
											<input class="form-control" id="apo_apellido_paterno" name="apo_apellido_paterno" value="{{ $record['apo_apellido_paterno'] }}" type="text">
										</div>
										<div class="col-sm-3">
											<input class="form-control" id="apo_apellido_materno" name="apo_apellido_materno" value="{{ $record['apo_apellido_materno'] }}" type="text">
										</div>
									</div>
									<div class="form-group col-sm-12">
										<div class="col-sm-1">
											<label for="curso" class="control-label">Fono:</label>
										</div>
										<div class="col-sm-3">
											<input class="form-control" id="apo_fono" name="apo_fono" value="{{ $record['apo_fono'] }}" type="text">
										</div>
										<div class="col-sm-8">
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
										<div class="col-sm-6">
											<input class="form-control" id="apo_email" name="apo_email" type="text" value="{{ $record['apo_email'] }}" type="text" disabled="disabled" placeholder="E-Mail">
										</div>
										<div class="col-sm-4">
										</div>
									</div>
								</div>
								<div class="panel-footer">
									<label for="curso" class="control-label">Modificar Password:</label>
									<input type="checkbox" id="dat_password" name="dat_password" >
								</div>
						  		<div class="panel-body">
									<div class="form-group col-sm-12">
										<div class="col-sm-2">
											<label for="curso" class="control-label">Password:</label>
										</div>
										<div class="col-sm-3">
											<input class="form-control" id="apo_password" name="apo_password" type="password" disabled="disabled" placeholder="Password">
										</div>
										<div class="col-sm-7">
										</div>
									</div>
									<div class="form-group col-sm-12">
										<div class="col-sm-2">
										</div>
										<div class="col-sm-3">
											<input class="form-control" id="apo_password_re" name="apo_password_re" type="password" disabled="disabled" placeholder="Password">
										</div>
										<div class="col-sm-7">
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