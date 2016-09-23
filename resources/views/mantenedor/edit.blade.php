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

{!! Html::script('assets/js/jquery-validate.bootstrap-tooltip.js') !!}
{!! Html::script('assets/js/dropdown.js') !!}

<script type="text/javascript">

{{!! $validate !!}}

</script>

<div class="{{$entidad['clase']}}">
		<fieldset>

			<?php 
				$controller = $entidad['controller'].'.update';
			?>

			{{ Form::model($record, array('route' => array($controller, $record[$entidad['pk']]), 'method' => 'PUT', 'files' => true, 'class' => 'form-horizontal', 'id' => 'myform', 'name' =>'myform')) }}
				<legend>{{ $title}}</legend>
				@foreach($tablas as $tabla)
					<?php
						$disabled = '';
						if (!$tabla['enable']){
							$disabled = 'disabled="disabled"';
						}
					
					?>
					<div class="form-group">
						<label for="{{ $tabla['campo'] }}" class="{{$entidad['label']}} control-label">{{ $tabla['nombre'] }}</label>
						<div class="{{$tabla['clase']}}">
							@if ($tabla['tipo'] == 'input')
								<?php 
									if ($tabla['campo'] == 'per_rut'){
										$mostrar = util::format_rut($record[$tabla['campo']], 'X');
										$mostrar = $mostrar['numero'].'-'.$mostrar['dv'];
									}
									else{
										$mostrar = $record[$tabla['campo']];
									}
								?>
								
								<input class="form-control" id="{{ $tabla['campo'] }}" name="{{ $tabla['campo'] }}" value="{{ $mostrar }}" type="text" <?php echo ' '.$disabled; ?>>
							@endif
							@if ($tabla['tipo'] == 'password')
								<input class="form-control" id="{{ $tabla['campo'] }}" name="{{ $tabla['campo'] }}" placeholder="{{ $tabla['descripcion'] }}" type="password">
							@endif
							@if ($tabla['tipo'] == 'check')
								<?php
									if ($record[$tabla['campo']] == 1){
										$check = 'checked';
									}
									else {
										$check ='';
									}
								?>
								<input type="checkbox" id="{{ $tabla['campo'] }}" name="{{ $tabla['campo'] }}" <?php echo $check.' '.$disabled; ?>>
							@endif
							@if ($tabla['tipo'] == 'select')
								<?php
									$disabled = '';
									if (!$tabla['enable']){
										$disabled = 'disabled';
									}
								
								?>
								{{ Form::select($tabla['campo'], $tabla['select'], $record[$tabla['value']], ['id' => $tabla['campo'], 'class' => 'form-control', 'name' => $tabla['campo'], $disabled ]) }}
							@endif
							@if ($tabla['tipo'] == 'file')
								{{ Form::file($tabla['campo']) }}
							@endif
							
						</div>
					</div>
				@endforeach
				<div class="form-group">
					<div class="col-lg-6 col-lg-offset-3">
						<?php 
							$controller = $entidad['controller'];
						?>
						<a href="../../<?php echo $controller ?>" class="btn btn-default">
								<span>Volver</span>
						</a>
					    {{ Form::button('Guardar', array('class'=>'btn btn-default', 'type'=>'submit')) }}    
						
					</div>
				</div>
			{!! Form::close() !!}
		</fieldset>
</div>

@endsection