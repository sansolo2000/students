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
	$().ready(function () {
		$('#myform').validate({
			rules: {
				'groupOrganiza'			:	{required: true},
				'import_file'			:	{required: true, extension: 'xls|xlsx'}
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
							<input class="form-control" id="cur_nombre" name="cur_nombre" value="{{ $curso->name }}" type="text" disabled="disabled">
				  		</div>
						<div class="col-sm-3">
				  		</div>
			  		</div>
					<div class="col-sm-12">
						<fieldset>
							<?php 
								$controller = 'import_excel/alumno';
							?>
							{{ Form::model($curso, array('route' => array($controller), 'method' => 'POST', 'files' => true, 'class' => 'form-horizontal', 'id' => 'myform', 'name' =>'myform')) }}
							<div class="panel panel-primary" >
								<div class="panel-heading">
						    		<h3 class="panel-title">{{ $title}}</h3>
						  		</div>
								<div class="panel-footer">Organizar</div>
						  		<div class="panel-body">
									<div class="col-sm-3">
									</div>
									<div class="col-sm-6" style="text-align: left;">
										<div class="radio">
											<label>
												<input type="radio" name="groupOrganiza" value="orgAlfabetico" id="orgAlfabetico">
												Alfabeticamente
											</label>
										</div>
										<div class="radio">
											<label>
												<input type="radio" name="groupOrganiza" value="orgRut" id="orgRut">
												Rut
											</label>
										</div>
										<div class="radio">
											<label>
												<input type="radio" name="groupOrganiza" value="orgColumn" id="orgColumn">
												Columna excel
											</label>
										</div>
									</div>
									<div class="col-sm-3">
									</div>
								</div>
								<div class="panel-footer">Archivo Excel</div>
						  		<div class="panel-body">
									<div class="col-sm-6 col-sm-offset-3" style="text-align: center;">
										<input class="form-control" id="cur_codigo" name="cur_codigo" value="{{ $curso->cur_codigo }}" type="hidden">
										{{ Form::file('import_file') }}
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
							</div>
							{!! Form::close() !!}
						</fieldset>
			  		</div>
		  		</div>
			</div>
</div>

@endsection