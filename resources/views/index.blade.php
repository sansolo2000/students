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
	$logo = 'Sin definir';
}
else {
	$logo = 'files_uploaded/logo/'.$colegios['col_logo'];
	$colegio['Fono'] = 'Sin definir';
}
//util::print_a($logo,0);

?>



@extends('layout',[
					'NamePage' 	=> ':: Proyect Students - '.$colegios['col_nombre'].' ::', 
					'Direccion' => $colegios['col_direccion']. ', '. $colegios['com_nombre'],
					'imagen'	=> $colegios['col_logo']
							])


@section('content')
{!! Html::script('assets/js/jquery-validate.bootstrap-tooltip.js') !!}

<script type="text/javascript">
	$(document).ready(function(){
	// Demo 1
	$('#inputRut').Rut({
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
	<script type="text/javascript">
		$().ready(function () {
			$('#form_login').validate({ 
				rules: {
					"inputRut"		:	{required: true},
					"inputPassword"	:	{required: true, minlength: 5, maxlength: 15},
					"selectRol"		:	{required: true, min:1}
	  			},
	  			messages: {
					"selectRol": { min: "Seleccione perfil" }
	  			  }	  			

			});
	
		});

	</script>



@if( ! empty($Error))
        {!! $Error !!}
@endif

      <div class="container">

      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/students/public/">:: Proyect Students ::</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
        </div><!--/.navbar-collapse -->
      </div>
    
<div class="jumbotron">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
  		<div class="panel panel-primary">
		    <div class="panel-heading">
		      <h3 class="panel-title">Ingreso a Students</h3>
		    </div>
		    <div class="panel-body">
			<div class="col-md-4">
				{!! Html::image($logo) !!}
			</div>
			<div class="col-md-8">
			
		    <form id="form_login" class="form-horizontal" method="POST" action="auth/login">
			{{ Form::hidden('formulario', $formulario) }}
		    {!! csrf_field() !!}
		      <fieldset>
		        <div class="form-group">
		          <label for="inputRut" class="col-md-3 control-label">Rut:</label>
		          <div class="col-md-9">
		            <input class="form-control required" id="inputRut" placeholder="Rut" name="inputRut" type="text">
		          </div>
		        </div>
		        <div class="form-group">
		          <label for="text" class="col-md-3 control-label">Password:</label>
		          <div class="col-md-9">
		            <input class="form-control required" id="inputPassword" placeholder="Password" name="inputPassword" type="password">
		          </div>
		        </div>
		        <div class="form-group">
		          <label for="selectRol" class="col-md-3 control-label">Rol:</label>
		          <div class="col-lg-9">
					{{ Form::select('selectRol', $varRol, -1, ['class' => 'form-control required']) }}
		          </div>
		        </div>
		        <div class="form-group">
		          <div class="col-md-12">
		            <button type="submit" class="col-md-8 col-md-offset-2 btn btn-primary">Enviar</button>
		          </div>
		        </div>
		      </fieldset>
		    </form>
			</div>
		    </div>
		</div>
		</div>
  	</div>
</div>
</div>

<div class="container">
<div class="panelpanel-default">
  <div class="panel-body">
	<div class="col-md-10 text-primary">
    	Direci&oacute;n: {{ $colegios['col_direccion']. ', '. $colegios['com_nombre'] }}
    </div>
    <div class="col-md-2">
 		<a href="http://validator.w3.org/check?uri=referer" rel="external" style="line-height: 1.3em; font-style: normal;" title="HTML versión 5">HTML 5</a>
        <span style="line-height: 1.538em; font-style: normal;">&nbsp;|&nbsp;</span>
        <a href="http://jigsaw.w3.org/css-validator/check/referer/?profile=css3" rel="external" style="line-height: 1.3em; font-style: normal;" title="CSS versión 3">CSS 3</a>
        <span style="line-height: 1.538em; font-style: normal;">&nbsp;|</span>
        <a href="http://creativecommons.org/licenses/by/2.0/cl/" rel="external" style="line-height: 1.3em; font-style: normal;" title="Creative Commons">CC</a>
    </div>

  </div>
</div>
</div>



@endsection