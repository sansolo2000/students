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
		$().ready(function () {
			$('#form_login').validate({ 
				rules: {
					'inputEMail'				:	{required: true, email: true,  minlength: 2, maxlength: 50},
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
			      <h3 class="panel-title">Recordar password</h3>
					<input type="hidden" name="_token" value="{!! csrf_token() !!}">
			    </div>
			    <div class="panel-body">
					<div class="col-md-12">
					    <form id="form_login" class="form-horizontal" method="POST" action="enviarcorreo">
					      <fieldset>
							{{ csrf_field() }}
					        <div class="form-group">
					          <label for="inputEMail" class="col-md-3 control-label">E-Mail:</label>
					          <div class="col-md-9">
					            <input class="form-control required" id="inputEMail" placeholder="E-Mail" name="inputEMail" type="text">
					          </div>
					        </div>
					        <div class="form-group">
					          <div class="col-md-5">
					          	
					            <a href="{{ $url }}" class="col-md-8 col-md-offset-2 btn btn-primary">
												<span>Volver</span>
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
 		<a href="http://validator.w3.org/check?uri=referer" rel="external" style="line-height: 1.3em; font-style: normal;" title="HTML versi�n 5">HTML 5</a>
        <span style="line-height: 1.538em; font-style: normal;">&nbsp;|&nbsp;</span>
        <a href="http://jigsaw.w3.org/css-validator/check/referer/?profile=css3" rel="external" style="line-height: 1.3em; font-style: normal;" title="CSS versi�n 3">CSS 3</a>
        <span style="line-height: 1.538em; font-style: normal;">&nbsp;|</span>
        <a href="http://creativecommons.org/licenses/by/2.0/cl/" rel="external" style="line-height: 1.3em; font-style: normal;" title="Creative Commons">CC</a>
    </div>

  </div>
</div>
</div>



@endsection