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

	<script type="text/javascript">
		@if (isset($errores))
			var errores = '{!! $errores !!}';
			var titulo = '{!! $entidad["Nombre"] !!}';
		@else 
			var errores = '';
		@endif
		if (errores !== ''){
			BootstrapDialog.alert({
		            title: titulo,
		            message: errores,
		            type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
		            closable: true, // <-- Default value is false
		            draggable: true, // <-- Default value is false
		            buttonLabel: 'Volver', // <-- Default value is 'OK',
		            //callback: function(result) {
		                // result will be true if button was click, while it will be false if users close the dialog directly.
		                //alert('Result is: ' + result);
		            //}
		        });
			console.log(3);
		}
	</script>

	<div class="{{ $entidad['clase'] }}">
		<div class="panel panel-default">
			<div class="panel-heading">
	    		<h3 class="panel-title">{{ $entidad['Nombre'] }}</h3>
	  		</div>
	  		<div class="panel-body">
				<table class="table table-striped table-hover ">
					<thead>
						<tr>
						@foreach($tablas as $tabla)
							@if ($tabla['filter']!=3)
								<th>{{ $tabla['nombre'] }}</th>
							@endif
						@endforeach
							<th>Acciones</th>
						</tr>
					</thead>
					<tbody>
						@if($entidad['Filter']  == 1)
							<tr class="warning">
								{{ Form::model(Request::all(), array('url' => $entidad['controller'].'/search', 'class' => 'pull-right')) }}
								@foreach($tablas as $tabla)
									@if ($tabla['filter']!=3)
										<td>
											@if ($tabla['filter']==1)
												<?php 
													if ($tabla['value'] != '') { 
														$mostrar = "value=".$tabla['value'];
													}
													else {
														$mostrar = "placeholder=".$tabla['descripcion'];
													}
													if ($tabla['campo'] == 'per_rut'){
														$campo = $tabla['campo'].'bak';
													}
													else{
														$campo = $tabla['campo'];
													}
												?>
												<input class="form-control" id="{{ $campo }}" name="{{ $campo }}" <?php echo $mostrar; ?> type="text">
											@endif
											@if ($tabla['filter']==0)
												&nbsp;
											@endif
										</td>
									@endif
								@endforeach
									<td align="center">
											{{ Form::button('<span class="glyphicon glyphicon-search"></span>', array('class'=>'btn btn-default', 'type'=>'submit')) }}
									
									</td>
						    </tr>
								{{ Form::close() }}
						@endif

						@if (count($records)>0)
							@foreach($records as $record)
								<tr>
									@foreach($tablas as $tabla)
										@if ($tabla['filter'] != 3)
											@if ($tabla['tipo'] == 'input' || $tabla['tipo'] == 'select')
												<?php 
													if ($tabla['campo'] == 'per_rut' || $tabla['campo'] == 'per_rut_apo' || $tabla['campo'] == 'per_rut_pro' || $tabla['campo'] == 'per_rut_adm'){
														$mostrar = util::format_rut($record[$tabla['campo']], 'X');
														$mostrar = $mostrar['numero'].'-'.$mostrar['dv'];
													}
													else{
														$mostrar = $record[$tabla['campo']];
													}
												?>
												<td><?php echo $mostrar; ?></td>
											@endif
											@if ($tabla['tipo'] == 'check')
												<td><?php if ($record[$tabla["campo"]] == 1){
															echo "S&iacute;";
														}
														if ($record[$tabla["campo"]] == 0){
															echo "No";
														}
														if ($record[$tabla["campo"]] == 3){
															echo "N/A";
														}
														?></td>
											@endif
											@if ($tabla['tipo'] == 'file')
												<td>
													<?php 
														if ($record[$tabla["campo"]] == ''){
															echo "No Existe";
														}
														if ($record[$tabla["campo"]] <> ''){
															echo "Existe";
														}
														?>
												</td>
											@endif
										@endif
									@endforeach
									<td>
										<div class="btn-toolbar" role="toolbar" style="width: 100px;">
											<div class="btn-group">
													<?php 
														$controller = $entidad['controller'].'/'.$record[$entidad['pk']].'/edit'; 
														if ($privilegio->mas_edit == 1){
															$clase = '';
														}
														else{
															$clase = "disabled";
														}
													?>
													<a href="<?php echo $controller ?>" class="btn btn-default <?php echo $clase; ?>">
															<span class="glyphicon glyphicon-edit"></span>
													</a>
														{{ Form::open(array('url' => $entidad['controller'].'/'.$record[$entidad['pk']], 'class' => 'pull-right')) }}
														<?php $url = $entidad['controller']; $id = $record[$entidad['pk']];  ?>
														@if ($privilegio->mas_delete == 1)
															{{ Form::button('<span class="glyphicon glyphicon-remove-circle"></span>', array('class'=>'btn btn-default', 'type'=>'button', 'onclick' => "msg_delete('".$url."', $id); return false;")) }}
														@else												  
															{{ Form::button('<span class="glyphicon glyphicon-remove-circle"></span>', array('class'=>'btn btn-default', 'disabled' => 'disabled', 'type'=>'submit')) }}
														@endif
													
									                    {{ Form::hidden('_method', 'DELETE') }}
									                {{ Form::close() }}
											</div>
										</div>
									</td>
								</tr>
							@endforeach
						@else
							<tr class="danger">
							    <td class="text-center" colspan="{{$entidad['col']}}">
							    	La consulta no contiene datos
								</td>								
							</tr>
						@endif
							<tr class="success">
							    <td class="text-center" colspan="{{$entidad['col'] - 1}}">
							    	&nbsp;
								</td>
							    <td class="text-center">
									<?php 
										$controller = $entidad['controller'].'/create';
										if ($privilegio->mas_add == 1){
											$clase = '';
										}
										else{
											$clase = "disabled";
										}
									?>
									<a href="<?php echo $controller ?>" class="btn btn-default <?php echo $clase; ?>">
											<span class="glyphicon glyphicon-plus-sign"></span>
									</a>
								</td>
						    </tr>
						    @if ($renderactive)
								<tr class="active">
								    <td class="text-center pager" colspan="{{$entidad['col']}}">
								    	 {{ $records->render() }}
								    </td>
							    </tr>
							@endif
    					</tbody>
				</table>
	  		</div>
		</div>
	</div>
@endsection