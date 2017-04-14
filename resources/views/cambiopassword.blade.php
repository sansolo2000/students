<?php
use App\helpers\util;
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
	$colegios['col_logo'] = 'Sin definir';
	$colegios['Fono'] = 'Sin definir';
}
else {
	$colegio['Fono'] = 'Sin definir';
}


?>

@extends('mantenedor.base',[
					'NamePage' 	=> ':: Proyect Students - '.$colegios['col_nombre'].' ::', 
					'Direccion' => $colegios['col_direccion']. ', '. $colegios['com_nombre'],
					'imagen'	=> $colegios['col_logo']
							])
@section('content')
	{!! Html::script('assets/js/dropdown.js') !!}
	{!! Html::script('assets/js/jquery-validate.bootstrap-tooltip.js') !!}
	
	{!! Html::script('assets/js/dropdown.js') !!}

	<script type="text/javascript">
	
		{!! $validate !!}
	
	</script>


	<div class="container col-md-8 col-md-offset-2">
		<div class="panel panel-default">
			<div class="panel-heading">
	    		<h3 class="panel-title">Cambio de contrase&ntilde;a</h3>
	  		</div>
	  		<div class="panel-body">
			    <form id="form_login" class="form-horizontal" method="POST" action="savepassword">
			      <fieldset>
					{{ csrf_field() }}
			        <div class="form-group">
						<label for="inputTitulo" class="col-md-12 control-label" style="text-align: center;">Debe cambiar la password para continuar</label>
					</div>
			        <div class="form-group">
						<label for="inputPassword" class="col-md-3 control-label">Password:</label>
						<div class="col-md-5">
			         		<input class="form-control required" id="inputPassword" placeholder="Ingrese su nuevo Password" name="inputPassword" type="password">
						</div>
						<div class="col-md-4">
			         		&nbsp;
						</div>
   					</div>
			        <div class="form-group">
						<label for="inputRePassword" class="col-md-3 control-label">Password:</label>
						<div class="col-md-5">
			         		<input class="form-control required" id="inputRePassword" placeholder="Reingrese el Password" name="inputRePassword" type="password">
						</div>
						<div class="col-md-4">
			         		&nbsp;
						</div>
   					</div>
					@if (empty($email))
						<div class="form-group">
							<label for="inputEMail" class="col-md-3 control-label">E-Mail:</label>
							<div class="col-md-5">
								<input class="form-control required" id="inputEMail" placeholder="ingrese su email" name="inputEmail" type="text">
							</div>
							<div class="col-md-4">
				         		&nbsp;
							</div>
						</div>
	   				@endif
			        <div class="form-group">
						<div class="col-md-5">
							<a href="{{ $url }}" class="col-md-8 col-md-offset-2 btn btn-primary">
								<span>Salir</span>
							</a>
						</div>
						<div class="col-md-2">
						</div>
						<div class="col-md-5">
							<button type="submit" class="col-md-8 col-md-offset-2 btn btn-primary">Enviar</button>
						</div>
			        </div>
			      </fieldset>
			    </form>
	  		</div>
		</div>
	</div>
@endsection