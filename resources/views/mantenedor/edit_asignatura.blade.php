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
		var pro_codigo = {{ $asignatura->per_rut }};
		console.log(pro_codigo);
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
					if (pro_codigo == response[i].id){
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
			<div class="panel panel-primary" >
				<div class="panel-heading">
		    		<h3 class="panel-title">Curso</h3>
		  		</div>
		  		<div class="panel-body">
					<div class="col-sm-12">
						<input class="form-control" id="cur_nombre" name="cur_nombre" value="{{ $curso->name }}" type="text" disabled="disabled">
			  		</div>
					<fieldset>
						<?php 
							$controller = $entidad['controller'].'.update';
						?>
						{{ Form::model($asignatura, array('route' => array($controller, $asignatura[$entidad['pk']]), 'method' => 'PUT', 'files' => true, 'class' => 'form-horizontal', 'id' => 'myform', 'name' =>'myform')) }}
							<legend>{{ $title}}</legend>
								<div class="form-group col-sm-12">
									<div class="col-sm-4">
										<label for="curso" class="control-label">Asignatura:</label>
									</div>
									<div class="col-sm-8">
										<input class="form-control" id="asg_nombre" name="asg_nombre" type="text" value="{{ $asignatura->asg_nombre }}">
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
								<div class="form-group col-sm-12">
									<div class="col-sm-4">
										<label for="curso" class="control-label">Orden:</label>
									</div>
									<div class="col-sm-5">
										<input class="form-control" id="asg_orden" name="asg_orden" value="{{ $asignatura->asg_orden }}" type="text">
									</div>
									<div class="col-sm-3">
									</div>
								</div>
								<div class="form-group col-sm-12">
									<div class="col-sm-4">
										<label for="curso" class="control-label">Activo:</label>
									</div>
									<div class="col-sm-8">
										<?php 
											if ($asignatura->asg_activo == 1){
												$mostrar = 'checked';		
											}
											else{
												$mostrar='';
											}
										?>
										<input type="checkbox" id="asg_activo" name="asg_activo" <?php echo $mostrar; ?>>
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

@endsection